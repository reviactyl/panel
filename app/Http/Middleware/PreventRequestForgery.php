<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as BaseVerifier;

class PreventRequestForgery extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification. These are
     * never hit by the front-end, and require specific token validation
     * to work.
     */
    protected $except = ['remote/*', 'daemon/*'];
}
