<?php

namespace App\Http\Middleware\Api\Client\Server;

use App\Exceptions\Http\Server\ServerStateConflictException;
use App\Models\Server;
use App\Models\User;
use App\Services\Subusers\SubuserPreviewContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticateServerAccess
{
    /**
     * Routes that this middleware should not apply to if the user is an admin.
     */
    protected array $except = [
        'api:client:server.ws',
        'api:client:server.settings.category',
    ];

    /**
     * AuthenticateServerAccess constructor.
     */
    public function __construct() {}

    /**
     * Authenticates access to a server route and enforces the server's current state.
     *
     * @return mixed The response produced by the next middleware.
     *
     * @throws NotFoundHttpException If the server is invalid, the preview targets another server, or the user lacks access.
     * @throws ServerStateConflictException If the server state does not permit the requested route.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        /** @var User $user */
        $user = $request->user();
        $server = $request->route()->parameter('server');
        $preview = $request->attributes->get(SubuserPreviewContext::class);

        if (! $server instanceof Server) {
            throw new NotFoundHttpException(trans('exceptions.api.resource_not_found'));
        }

        // At the very least, ensure that the user trying to make this request is the
        // server owner, a subuser, or a root admin. We'll leave it up to the controllers
        // to authenticate more detailed permissions if needed.
        if ($preview instanceof SubuserPreviewContext && ! $preview->isServer($server)) {
            throw new NotFoundHttpException(trans('exceptions.api.resource_not_found'));
        }

        if (! ($preview instanceof SubuserPreviewContext) && $user->id !== $server->owner_id && ! $user->root_admin) {
            // Check for subuser status.
            if (! $server->subusers->contains('user_id', $user->id)) {
                throw new NotFoundHttpException(trans('exceptions.api.resource_not_found'));
            }
        }

        try {
            $server->validateCurrentState();
        } catch (ServerStateConflictException $exception) {
            // Still allow users to get information about their server if it is installing or
            // being transferred.
            if (! $request->routeIs('api:client:server.view')) {
                if (($server->isSuspended() || $server->node->isUnderMaintenance()) && ! $request->routeIs('api:client:server.resources')) {
                    throw $exception;
                }
                if (! $user->root_admin || ! $request->routeIs($this->except)) {
                    throw $exception;
                }
            }
        }

        $request->attributes->set('server', $server);

        return $next($request);
    }
}
