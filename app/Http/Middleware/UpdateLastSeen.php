<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Handle an incoming request and update the user's last_seen timestamp.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Only update if last_seen is null or was updated more than 5 minutes ago (to prevent excessive database writes)
            if (
                is_null($user->last_seen) ||
                $user->last_seen->lt(now()->subMinutes(5))
            ) {
                $user->update(['last_seen' => now()]);
            }
        }

        return $next($request);
    }
}
