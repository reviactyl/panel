<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ClearLegacySessionCookies
{
    /**
     * Legacy session cookie names that may still exist in browsers.
     */
    private const LEGACY_COOKIE_NAMES = [
        'pterodactyl_session',
        'laravel_session',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $currentSessionCookie = (string) config('session.cookie');

        foreach (self::LEGACY_COOKIE_NAMES as $cookieName) {
            if ($cookieName === $currentSessionCookie || ! $request->cookies->has($cookieName)) {
                continue;
            }

            foreach ($this->candidateDomains($request->getHost()) as $domain) {
                $response->headers->setCookie(Cookie::forget($cookieName, '/', $domain));
            }
        }

        return $response;
    }

    /**
     * Generate candidate domains to expire host-only and parent-domain cookies.
     *
     * @return array<int, string|null>
     */
    private function candidateDomains(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [null];
        }

        $domains = [null];
        $parts = explode('.', $host);

        if (count($parts) >= 2) {
            $domains[] = '.'.implode('.', array_slice($parts, -2));
        }

        if (count($parts) >= 3) {
            $domains[] = '.'.implode('.', array_slice($parts, -3));
        }

        return array_values(array_unique($domains));
    }
}
