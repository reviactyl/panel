<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class EditorMiddleware
{
    /**
     * EditorMiddleware constructor.
     */
    public function __construct(private Application $app)
    {
    }

    /**
     * Handle an incoming request and set the user's preferred file editor.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        if ($user = $request->user()) {
            $user->editor = $user->editor ?? config('app.fileEditor', 'cm');
        }

        return $next($request);
    }
}
