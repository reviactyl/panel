<?php

use App\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Console\Kernel as ConsoleKernel;
use App\Http\Middleware\AdminAuthenticate;
use App\Exceptions\Handler as ExceptionHandler;
use App\Http\Middleware\RequireTwoFactorAuthentication;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

$app = Application::configure(basePath: $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__));

if (!empty($_ENV['APP_STORAGE_PATH'])) {
    $app->useStoragePath($_ENV['APP_STORAGE_PATH']);
}

return $app
    ->withProviders()
    ->withRouting(function () {

        Route::middleware('web')->group(function () {
            Route::middleware(['auth.session', RequireTwoFactorAuthentication::class])
                ->group(base_path('routes/base.php'));

            Route::middleware(['auth.session', RequireTwoFactorAuthentication::class, AdminAuthenticate::class])
                ->prefix('/panel')
                ->group(base_path('routes/admin.php'));

            Route::middleware(['auth.session', RequireTwoFactorAuthentication::class, AdminAuthenticate::class])
                ->prefix('/designify')
                ->group(base_path('routes/designify.php'));

            Route::middleware('guest')
                ->prefix('/auth')
                ->group(base_path('routes/auth.php'));
        });

        Route::middleware(['api', RequireTwoFactorAuthentication::class])
            ->group(function () {

                Route::middleware(['application-api', 'throttle:api.application'])
                    ->prefix('/api/application')
                    ->scopeBindings()
                    ->group(base_path('routes/api-application.php'));

                Route::middleware(['client-api', 'throttle:api.client'])
                    ->prefix('/api/client')
                    ->scopeBindings()
                    ->group(base_path('routes/api-client.php'));
            });

        Route::middleware(['throttle:api.client'])
            ->prefix('/api/public')
            ->scopeBindings()
            ->group(base_path('routes/api-public.php'));

        Route::middleware('daemon')
            ->prefix('/api/remote')
            ->scopeBindings()
            ->group(base_path('routes/api-remote.php'));
    })
    ->withExceptions()
    ->withSingletons([
        HttpKernelContract::class => HttpKernel::class,
        ConsoleKernelContract::class => ConsoleKernel::class,
        ExceptionHandlerContract::class => ExceptionHandler::class,
    ])
    ->create();
