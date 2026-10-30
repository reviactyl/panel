<?php

namespace App\Support;

use Illuminate\Http\Request;

class LoginDestination
{
    public static function pull(Request $request): string
    {
        $destination = $request->session()->pull('url.intended');

        if (! is_string($destination) || ! str_starts_with($destination, '/')) {
            return '/';
        }

        $path = rawurldecode(explode('?', $destination, 2)[0]);
        if (str_starts_with($path, '//') || preg_match('/[\\\\\x00-\x20\x7f]/', $destination.$path) || preg_match('#^/auth(?:/|$)#i', $path)) {
            return '/';
        }

        return $destination;
    }
}
