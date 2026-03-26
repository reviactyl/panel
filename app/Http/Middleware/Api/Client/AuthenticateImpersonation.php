<?php

namespace App\Http\Middleware\Api\Client;

use App\Models\User;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use App\Services\Acl\Api\AdminAcl;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthenticateImpersonation
{
    public const IMPERSONATING_USER_ATTRIBUTE = 'impersonating_user';

    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var User|null $caller */
        $caller = $request->user();

        if (!$caller) {
            return $next($request);
        }

        $token = $caller->currentAccessToken();

        if (!($token instanceof ApiKey) || $token->key_type !== ApiKey::TYPE_APPLICATION) {
            return $next($request);
        }

        // Application API keys MUST specify which user they are acting as.
        $executionUserId = $request->header('X-Execution-User');
        if (empty($executionUserId)) {
            throw new BadRequestHttpException('Application API keys must include the X-Execution-User header to access Client API endpoints.');
        }

        // Only root admins are permitted to impersonate users.
        if (!$caller->root_admin) {
            throw new AccessDeniedHttpException('You do not have permission to perform user impersonation.');
        }

        // The application API key must have impersonation permission enabled.
        if (!AdminAcl::check($token, AdminAcl::RESOURCE_IMPERSONATION, AdminAcl::READ)) {
            throw new AccessDeniedHttpException('This API key does not have permission to perform user impersonation.');
        }

        // Read-only impersonation keys may not perform write operations.
        if (!AdminAcl::check($token, AdminAcl::RESOURCE_IMPERSONATION, AdminAcl::WRITE) && !$request->isMethodSafe()) {
            throw new AccessDeniedHttpException('This API key only has read-only impersonation permission and cannot perform write operations.');
        }

        // Resolve the target user by integer ID or UUID.
        $targetUser = User::query()
            ->where(is_numeric($executionUserId) ? 'id' : 'uuid', $executionUserId)
            ->first();

        if (!$targetUser) {
            throw new NotFoundHttpException('The user specified in X-Execution-User could not be found.');
        }

        // Preserve the original admin for auditing.
        $request->attributes->set(self::IMPERSONATING_USER_ATTRIBUTE, $caller);

        $request->setUserResolver(fn () => $targetUser);

        return $next($request);
    }
}
