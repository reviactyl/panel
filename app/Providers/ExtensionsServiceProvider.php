<?php

namespace App\Providers;

use App\Http\Middleware\ConfigureExtensionFilesystem;
use App\Models\Extension;
use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\RequireTwoFactorAuthentication;
use App\Services\Extensions\ExtensionFilesystemService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\ServiceProvider;

class ExtensionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!$this->canBootExtensionsTable()) {
            return;
        }

        $this->registerPublicFilesystemRoute();

        $extensions = Extension::query()->enabled()->get();
        $fs = app(ExtensionFilesystemService::class);

        foreach ($extensions as $extension) {
            $manifest = $extension->manifest ?? [];
            $fs->registerDisks($extension->identifier, $extension->install_path);
            $this->registerRoutes($extension->identifier, $extension->install_path, $manifest);
            $this->registerCommands($extension->identifier, $manifest);
            $this->runBootHooks($extension->identifier, $extension->install_path, $manifest);
        }
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function registerRoutes(string $identifier, string $installPath, array $manifest): void
    {
        $routes = Arr::get($manifest, 'backend.routes', []);
        if (!is_array($routes)) {
            return;
        }

        $this->mountRouteScope(
            $identifier,
            $installPath,
            Arr::get($routes, 'client'),
            ['api', RequireTwoFactorAuthentication::class, 'client-api', 'throttle:api.client'],
            "/api/client/extensions/{$identifier}"
        );

        $this->mountRouteScope(
            $identifier,
            $installPath,
            Arr::get($routes, 'admin'),
            ['web', 'auth.session', RequireTwoFactorAuthentication::class, AdminAuthenticate::class],
            "/admin/extensions/{$identifier}"
        );

        $this->mountRouteScope(
            $identifier,
            $installPath,
            Arr::get($routes, 'api'),
            ['api'],
            "/api/extensions/{$identifier}"
        );

        $this->mountRouteScope(
            $identifier,
            $installPath,
            Arr::get($routes, 'web'),
            ['web'],
            "/extensions/{$identifier}"
        );
    }

    /**
     * @param mixed $scopeConfig
     * @param array<int, string> $baseMiddleware
     */
    private function mountRouteScope(
        string $identifier,
        string $installPath,
        mixed $scopeConfig,
        array $baseMiddleware,
        string $prefix
    ): void {
        if (!is_array($scopeConfig)) {
            return;
        }

        $file = Arr::get($scopeConfig, 'file');
        if (!is_string($file) || trim($file) === '') {
            return;
        }

        $routeFilePath = realpath($installPath . DIRECTORY_SEPARATOR . ltrim($file, '/'));
        $installPathReal = realpath($installPath);

        if ($routeFilePath === false || $installPathReal === false || strpos($routeFilePath, $installPathReal) !== 0) {
            Log::warning('Skipping extension route mount due to invalid route path.', [
                'extension' => $identifier,
                'route_file' => $file,
            ]);

            return;
        }

        if (!is_file($routeFilePath)) {
            return;
        }

        $manifestMiddleware = Arr::wrap(Arr::get($scopeConfig, 'middleware', []));
        $middleware = [
            ...$baseMiddleware,
            ...array_filter($manifestMiddleware, fn ($value) => is_string($value)),
            ConfigureExtensionFilesystem::class . ':' . $identifier,
        ];

        Route::middleware($middleware)
            ->prefix($prefix)
            ->group($routeFilePath);
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function registerCommands(string $identifier, array $manifest): void
    {
        $commandClasses = Arr::wrap(Arr::get($manifest, 'backend.commands', []));
        $resolved = [];

        foreach ($commandClasses as $class) {
            if (is_string($class) && class_exists($class)) {
                $resolved[] = $class;
            }
        }

        if (count($resolved) === 0) {
            return;
        }

        Artisan::starting(function ($artisan) use ($resolved): void {
            $artisan->resolveCommands($resolved);
        });

        Log::info('Registered extension artisan commands.', [
            'extension' => $identifier,
            'count' => count($resolved),
        ]);
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function runBootHooks(string $identifier, string $installPath, array $manifest): void
    {
        $hooks = Arr::wrap(Arr::get($manifest, 'backend.boot_hooks', []));
        $installPathReal = realpath($installPath);
        if ($installPathReal === false) {
            return;
        }

        foreach ($hooks as $hook) {
            if (!is_string($hook) || trim($hook) === '') {
                continue;
            }

            $path = realpath($installPath . DIRECTORY_SEPARATOR . ltrim($hook, '/'));
            if ($path === false || strpos($path, $installPathReal) !== 0 || !is_file($path)) {
                Log::warning('Skipping extension boot hook due to invalid path.', [
                    'extension' => $identifier,
                    'hook' => $hook,
                ]);

                continue;
            }

            app(ExtensionFilesystemService::class)->configurePlaceholderDisks($identifier, $installPath);

            require_once $path;
        }
    }

    private function registerPublicFilesystemRoute(): void
    {
        Route::middleware(['web'])->get('/fs/{identifier}/{path?}', function (string $identifier, ?string $path = null) {
            $extension = Extension::query()->enabled()->where('identifier', $identifier)->first();
            if ($extension === null) {
                throw new NotFoundHttpException();
            }

            $requested = trim((string) ($path ?? ''), '/');
            if ($requested === '' || str_contains($requested, '..')) {
                throw new NotFoundHttpException();
            }

            $fs = app(ExtensionFilesystemService::class);
            $root = $fs->publicRootPath($identifier);
            $absolute = realpath($root . DIRECTORY_SEPARATOR . $requested);

            if ($absolute === false || strpos($absolute, $root) !== 0 || !is_file($absolute)) {
                throw new NotFoundHttpException();
            }

            return response()->file($absolute);
        })->where('path', '.*');
    }

    private function canBootExtensionsTable(): bool
    {
        try {
            return Schema::hasTable('extensions');
        } catch (\Throwable) {
            return false;
        }
    }
}
