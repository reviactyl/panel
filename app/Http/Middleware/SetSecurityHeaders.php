<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SetSecurityHeaders
{
    /**
     * Ideally we move away from X-Frame-Options/X-XSS-Protection and implement a
     * proper standard CSP, but I can guarantee that will break for a lot of folks
     * using custom plugins and who knows what image embeds.
     *
     * We'll circle back to that at a later date when it can be more fully controlled
     * by the admin to support those cases without too much trouble.
     *
     * We have set it to SAMEORIGIN for now to allow the designify editor to load iframe.
     * This is not ideal but it is better than breaking the editor for everyone. We will need to revisit this in the future to find a better solution that does not compromise security.
     */
    private static array $headers = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'no-referrer-when-downgrade',
        // script-src requires 'unsafe-inline' because the base template injects server-side data
        // via inline <script> blocks using {!! json_encode() !!}. Until those are replaced with
        // a nonce-based approach, 'unsafe-inline' is the only viable option.
        // connect-src uses broad wss:/https: because Wings daemon addresses are user-configured
        // and cannot be enumerated at request time.
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: https:; connect-src 'self' wss: https:; frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self';",
    ];

    /**
     * Enforces some basic security headers on all responses returned by the software.
     * If a header has already been set in another location within the code it will be
     * skipped over here.
     *
     * @param  (\Closure(mixed): Response)  $next
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $response = $next($request);

        foreach (self::$headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
