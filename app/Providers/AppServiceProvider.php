<?php

namespace App\Providers;

use App\Models;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Sanctum::usePersonalAccessTokenModel(Models\ApiKey::class);

        View::share('appVersion', $this->versionData()['version'] ?? 'undefined');
        View::share('appIsGit', $this->versionData()['is_git'] ?? false);

        Paginator::useBootstrap();

        // If the APP_URL value is set with https:// make sure we force it here. Theoretically
        // this should just work with the proxy logic, but there are a lot of cases where it
        // doesn't, and it triggers a lot of support requests, so lets just head it off here.
        //
        // @see https://github.com/pterodactyl/panel/issues/3623
        if (Str::startsWith(config('app.url') ?? '', 'https://')) {
            URL::forceScheme('https');
        }

        Relation::enforceMorphMap([
            'allocation' => Models\Allocation::class,
            'api_key' => Models\ApiKey::class,
            'backup' => Models\Backup::class,
            'database' => Models\Database::class,
            'database_host' => Models\DatabaseHost::class,
            'egg' => Models\Egg::class,
            'egg_variable' => Models\EggVariable::class,
            'mount' => Models\Mount::class,
            'schedule' => Models\Schedule::class,
            'server' => Models\Server::class,
            'node' => Models\Node::class,
            'ssh_key' => Models\UserSSHKey::class,
            'task' => Models\Task::class,
            'user' => Models\User::class,
        ]);
    }

    /**
     * Register application service providers.
     */
    public function register(): void
    {
        $this->app->register(SettingsServiceProvider::class);
    }

    /**
     * Return version information for the footer.
     */
    protected function versionData(): array
    {
        return Cache::remember('git-version', 5, function () {
            $headPath = base_path('.git/HEAD');

            if (is_file($headPath)) {
                $head = trim((string) file_get_contents($headPath));

                if (Str::startsWith($head, 'ref: ')) {
                    $referencePath = base_path('.git/'.trim(Str::after($head, 'ref: ')));

                    if (is_file($referencePath)) {
                        $version = trim((string) file_get_contents($referencePath));

                        if ($version !== '') {
                            return [
                                'version' => substr($version, 0, 8),
                                'is_git' => true,
                            ];
                        }
                    }
                }
            }

            return [
                'version' => config('app.version'),
                'is_git' => false,
            ];
        });
    }
}
