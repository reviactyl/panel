<?php

namespace App\Http\Middleware\Api\Client;

use App\Models\SubuserPreviewSession;
use App\Services\Subusers\SubuserPreviewContext;
use App\Services\Subusers\SubuserPreviewSimulator;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ResolveSubuserPreview
{
    public const HEADER = 'X-Subuser-Preview';

    public function __construct(private readonly SubuserPreviewSimulator $simulator) {}

    public function handle(Request $request, \Closure $next): mixed
    {
        $token = $request->header(self::HEADER);
        if (! is_string($token) || $token === '') {
            return $next($request);
        }

        $session = SubuserPreviewSession::query()
            ->with(['subuser.user', 'server'])
            ->where('owner_id', $request->user()->id)
            ->first();

        if (! $session || ! $session->tokenMatches($token)) {
            throw new AccessDeniedHttpException('This preview session is no longer available.');
        }

        if ($session->expires_at->isPast()) {
            $session->delete();

            throw new ConflictHttpException('This preview session has expired.');
        }

        $session->renew();
        $context = new SubuserPreviewContext($session);

        $request->attributes->set(SubuserPreviewContext::class, $context);
        app()->instance(SubuserPreviewContext::class, $context);

        if ($request->is('api/client/account*')) {
            throw new AccessDeniedHttpException('Account information is unavailable during subuser preview.');
        }

        if ($request->is('api/client/servers/*/users/*/preview')) {
            throw new ConflictHttpException('You cannot start another preview while preview mode is active.');
        }

        return $this->simulator->handle($request, $next, $context);
    }
}
