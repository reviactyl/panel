<?php

namespace App\Http;

use App\Http\Middleware\Activity\TrackAPIKey;
use App\Http\Middleware\Api\Application\AuthenticateApplicationUser;
use App\Http\Middleware\Api\AuthenticateIPAccess;
use App\Http\Middleware\Api\Client\AuthenticateImpersonation;
use App\Http\Middleware\Api\Client\RequireClientApiKey;
use App\Http\Middleware\Api\Client\SubstituteClientBindings;
use App\Http\Middleware\Api\Daemon\DaemonAuthenticate;
use App\Http\Middleware\Api\IsValidJson;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\EnsureInstallationState;
use App\Http\Middleware\EnsureStatefulRequests;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\MaintenanceMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RequireTwoFactorAuthentication;
use App\Http\Middleware\SetSecurityHeaders;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\UpdateLastSeen;
use App\Http\Middleware\VerifyCaptcha;
use App\Http\Middleware\PreventRequestForgery;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     */
    protected $middleware = [
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        SetSecurityHeaders::class,
    ];

    protected $middlewarePriority = [
        SubstituteClientBindings::class,
    ];

    /**
     * The application's route middleware groups.
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            PreventRequestForgery::class,
            SubstituteBindings::class,
            LanguageMiddleware::class,
            UpdateLastSeen::class,
        ],
        'api' => [
            EnsureStatefulRequests::class,
            'auth:sanctum',
            IsValidJson::class,
            TrackAPIKey::class,
            RequireTwoFactorAuthentication::class,
            AuthenticateIPAccess::class,
        ],
        'application-api' => [
            SubstituteBindings::class,
            AuthenticateApplicationUser::class,
        ],
        'client-api' => [
            SubstituteClientBindings::class,
            AuthenticateImpersonation::class,
            RequireClientApiKey::class,
        ],
        'daemon' => [
            SubstituteBindings::class,
            DaemonAuthenticate::class,
        ],
    ];

    /**
     * The application's route middleware.
     */
    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.session' => AuthenticateSession::class,
        'guest' => RedirectIfAuthenticated::class,
        'csrf' => PreventRequestForgery::class,
        'throttle' => ThrottleRequests::class,
        'can' => Authorize::class,
        'bindings' => SubstituteBindings::class,
        'captcha' => VerifyCaptcha::class,
        'node.maintenance' => MaintenanceMiddleware::class,
        'installation' => EnsureInstallationState::class,
    ];
}
