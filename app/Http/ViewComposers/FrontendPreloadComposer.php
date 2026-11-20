<?php

namespace App\Http\ViewComposers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\View;

class FrontendPreloadComposer
{
    private const SCRIPTS = 'resources/scripts/';

    private const SERVER_SCREENS = [
        '' => 'components/server/console/ServerConsoleContainer.tsx',
        'files' => 'components/server/files/FileManagerContainer.tsx',
        'files/edit' => 'components/server/files/FileEditContainer.tsx',
        'files/new' => 'components/server/files/FileEditContainer.tsx',
        'startup' => 'components/server/startup/StartupContainer.tsx',
        'network' => 'components/server/network/NetworkContainer.tsx',
        'metrics' => 'components/server/metrics/HistoricalGraphsContainer.tsx',
        'databases' => 'components/server/databases/DatabasesContainer.tsx',
        'schedules' => 'components/server/schedules/ScheduleContainer.tsx',
        'backups' => 'components/server/backups/BackupContainer.tsx',
        'users' => 'components/server/users/UsersContainer.tsx',
        'settings' => 'components/server/settings/SettingsContainer.tsx',
        'activity' => 'components/server/ServerActivityLogContainer.tsx',
    ];

    private const ACCOUNT_SCREENS = [
        '' => 'components/dashboard/AccountOverviewContainer.tsx',
        'api' => 'components/dashboard/AccountApiContainer.tsx',
        'passkeys' => 'components/dashboard/PasskeysContainer.tsx',
        'ssh' => 'components/dashboard/ssh/AccountSSHContainer.tsx',
        'activity' => 'components/dashboard/activity/ActivityLogContainer.tsx',
    ];

    /** @var array<string, array> */
    private static array $manifests = [];

    public function __construct(private Request $request) {}

    public function compose(View $view): void
    {
        $view->with('frontendPreloads', $this->preloads());
    }

    public function preloads(): array
    {
        $empty = ['scripts' => [], 'styles' => []];
        if (Vite::isRunningHot() || ! ($manifest = $this->manifest())) {
            return $empty;
        }

        $scripts = [];
        $styles = [];
        $visited = [];
        $walk = function (string $key) use (&$walk, &$scripts, &$styles, &$visited, $manifest): void {
            if (isset($visited[$key]) || ! isset($manifest[$key]['file'])) {
                return;
            }
            $visited[$key] = true;
            $scripts[] = $manifest[$key]['file'];
            foreach ($manifest[$key]['css'] ?? [] as $css) {
                $styles[] = $css;
            }
            foreach ($manifest[$key]['imports'] ?? [] as $import) {
                $walk($import);
            }
        };

        $walk(self::SCRIPTS.'index.tsx');
        $initial = ['scripts' => $scripts, 'styles' => $styles];
        $scripts = $styles = [];

        foreach ($this->chunksForPath($this->request->path(), $this->request->user() !== null) as $chunk) {
            $walk(self::SCRIPTS.$chunk);
        }

        return [
            'scripts' => array_values(array_diff(array_unique($scripts), $initial['scripts'])),
            'styles' => array_values(array_diff(array_unique($styles), $initial['styles'])),
        ];
    }

    /**
     * @return string[]
     */
    public function chunksForPath(string $path, bool $authenticated): array
    {
        $path = trim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);

        if (($segments[0] ?? null) === 'auth') {
            return ['routers/AuthenticationRouter.tsx'];
        }

        if (! $authenticated || in_array($segments[0] ?? null, ['status', 'admin', 'docs'], true)) {
            return [];
        }

        if (($segments[0] ?? null) === 'server' && isset($segments[1])) {
            $screen = $segments[2] ?? '';
            if ($screen === 'files' && in_array($segments[3] ?? null, ['edit', 'new'], true)) {
                $screen .= '/'.$segments[3];
            } elseif ($screen === 'schedules' && isset($segments[3])) {
                return ['routers/ServerRouter.tsx', 'components/server/schedules/ScheduleEditContainer.tsx'];
            }

            return array_values(array_filter([
                'routers/ServerRouter.tsx',
                self::SERVER_SCREENS[$screen] ?? null,
            ]));
        }

        if (($segments[0] ?? null) === 'account') {
            return array_values(array_filter([
                'routers/DashboardRouter.tsx',
                self::ACCOUNT_SCREENS[$segments[1] ?? ''] ?? null,
            ]));
        }

        return ['routers/DashboardRouter.tsx'];
    }

    private function manifest(): ?array
    {
        $path = public_path('build/manifest.json');
        if (! array_key_exists($path, self::$manifests)) {
            $decoded = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
            self::$manifests[$path] = is_array($decoded) ? $decoded : [];
        }

        return self::$manifests[$path] ?: null;
    }
}
