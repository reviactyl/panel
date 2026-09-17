<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeDuplicateCookies
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->headers->get('cookie', '');

        if ($header !== '') {
            $cookies = [];

            foreach (explode(';', $header) as $pair) {
                $pair = trim($pair);

                if ($pair === '' || ! str_contains($pair, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $pair, 2);
                $name = trim($name);

                if ($name === '') {
                    continue;
                }

                $cookies[$name] = rawurldecode(trim($value));
            }

            if ($cookies !== []) {
                $request->cookies->replace($cookies);
            }
        }

        return $next($request);
    }
}
