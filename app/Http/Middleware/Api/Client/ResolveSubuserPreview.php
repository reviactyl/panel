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

    public const PARAMETER = 'subuser_preview';

    public function __construct(private readonly SubuserPreviewSimulator $simulator) {}

    /**
     * Resolves a subuser preview session for the request and delegates it with preview context.
     *
     * Requests without a preview token continue unchanged. Invalid, expired, or restricted preview
     * requests raise an HTTP exception.
     *
     * @return mixed The response from the next handler or preview simulator.
     *
     * @throws AccessDeniedHttpException If the session is unavailable or the request targets a restricted route.
     * @throws ConflictHttpException If the session has expired or the request attempts to start another preview.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $token = $request->header(self::HEADER) ?? ($request->isMethodSafe() ? $request->query(self::PARAMETER) : null);
        if (! is_string($token) || $token === '') {
            return $next($request);
        }

        $session = SubuserPreviewSession::query()
            ->with(['subuser.user', 'server'])
            ->where('owner_id', $request->user()->id)
            ->first();

        if (! $session || ! $session->tokenMatches($token)) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.session_unavailable'));
        }

        if ($session->expires_at->isPast()) {
            $session->delete();

            throw new ConflictHttpException(trans('exceptions.subuser_preview.session_expired'));
        }

        if ($request->is('api/client/account*')) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.account_unavailable'));
        }

        if ($request->is('api/client/servers/*/users/*/preview')) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.start_blocked'));
        }

        if ($session->expires_at->lessThanOrEqualTo(now()->addMinutes(25))) {
            $session->renew();
        }

        $context = new SubuserPreviewContext($session);
        $request->attributes->set(SubuserPreviewContext::class, $context);

        return $this->simulator->handle($request, $next, $context);
    }
}
