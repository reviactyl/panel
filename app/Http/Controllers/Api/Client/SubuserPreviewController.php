<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Middleware\Api\Client\ResolveSubuserPreview;
use App\Models\Server;
use App\Models\SubuserPreviewSession;
use App\Models\User;
use App\Services\Subusers\SubuserPreviewContext;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SubuserPreviewController extends ClientApiController
{
    public function status(Request $request): JsonResponse
    {
        $session = $this->activeSession($request->user());
        if (! $session) {
            return response()->json(['active' => false]);
        }

        $token = $request->header(ResolveSubuserPreview::HEADER);
        $ownedByTab = is_string($token) && $session->tokenMatches($token);

        return response()->json([
            'active' => true,
            'owned_by_tab' => $ownedByTab,
            'session' => $this->serialize($session),
        ]);
    }

    public function store(Request $request, Server $server, User $user): JsonResponse
    {
        if ($request->attributes->has(SubuserPreviewContext::class)) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.start_blocked'));
        }

        if ($request->user()->id !== $server->owner_id) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.owner_only'));
        }

        $subuser = $server->subusers()->where('user_id', $user->id)->firstOrFail();
        $existing = $this->activeSession($request->user());
        $replace = $request->boolean('replace');

        if ($existing && ! $replace) {
            return response()->json([
                'active' => true,
                'owned_by_tab' => false,
                'session' => $this->serialize($existing),
            ], 409);
        }

        $token = Str::random(64);
        $now = Carbon::now();

        try {
            $session = DB::transaction(function () use ($existing, $request, $server, $subuser, $token, $now) {
                $existing?->delete();

                return SubuserPreviewSession::query()->create([
                    'uuid' => Str::uuid()->toString(),
                    'owner_id' => $request->user()->id,
                    'server_id' => $server->id,
                    'subuser_id' => $subuser->id,
                    'token_hash' => hash('sha256', $token),
                    'state' => [
                        'power_status' => null,
                        'files' => [],
                    ],
                    'last_seen_at' => $now,
                    'expires_at' => $now->clone()->addMinutes(30),
                ])->load(['subuser.user', 'server']);
            });
        } catch (UniqueConstraintViolationException) {
            return response()->json([
                'active' => true,
                'owned_by_tab' => false,
                'session' => $this->serialize($this->activeSession($request->user(), true)),
            ], 409);
        }

        return response()->json([
            'active' => true,
            'owned_by_tab' => true,
            'token' => $token,
            'session' => $this->serialize($session),
        ], 201);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $context = $request->attributes->get(SubuserPreviewContext::class);
        if (! $context instanceof SubuserPreviewContext) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.session_unavailable'));
        }

        return response()->json([
            'active' => true,
            'session' => $this->serialize($context->session()),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $context = $request->attributes->get(SubuserPreviewContext::class);
        if (! $context instanceof SubuserPreviewContext) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.session_unavailable'));
        }

        $context->session()->delete();

        return response()->json([], 204);
    }

    private function activeSession(User $owner, bool $required = false): ?SubuserPreviewSession
    {
        $session = SubuserPreviewSession::query()
            ->with(['subuser.user', 'server'])
            ->where('owner_id', $owner->id)
            ->first();

        if ($session?->expires_at->isPast()) {
            $session->delete();
            $session = null;
        }

        if ($required && ! $session) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.concurrent_start'));
        }

        return $session;
    }

    private function serialize(SubuserPreviewSession $session): array
    {
        return [
            'uuid' => $session->uuid,
            'server_uuid' => $session->server->uuid,
            'server_identifier' => $session->server->uuidShort,
            'server_name' => $session->server->name,
            'subuser_email' => $session->subuser->user->email,
            'subuser_uuid' => $session->subuser->user->uuid,
            'permission_count' => count(array_filter(
                $session->subuser->permissions ?? [],
                fn (string $permission) => $permission !== 'websocket.connect'
            )),
            'max_file_size' => (int) config('panel.files.max_edit_size'),
            'power_status' => $session->state['power_status'] ?? 'running',
            'expires_at' => $session->expires_at->toAtomString(),
        ];
    }
}
