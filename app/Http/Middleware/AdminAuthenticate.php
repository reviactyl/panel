<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        if (! $request->user()) {
            throw new AuthenticationException();
        }

        if (! $request->user()->isAdmin()) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
