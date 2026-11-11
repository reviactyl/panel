<?php

namespace App\Filament\Widgets;

use App\Filament\AdminAccess;
use App\Models\Server;
use App\Models\User;
use App\Services\Acl\RolePermission;
use App\Services\Helpers\DashboardStatisticsService;
use App\Services\Helpers\SoftwareVersionService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class DashboardStatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        return [
            ...$this->userStats(),
            ...$this->serverStats(),
            ...$this->softwareVersionWidget(),
        ];
    }

    private function userStats(): array
    {
        if (! AdminAccess::allows(RolePermission::RESOURCE_USERS)) {
            return [];
        }

        $counts = app(DashboardStatisticsService::class)->counts(User::class);

        return [
            Stat::make(trans('admin/index.dashboard.total_users'), $counts['total'])
                ->description(trans('admin/index.dashboard.new_users', ['count' => $counts['today']]))
                ->descriptionIcon('heroicon-o-users')
                ->chart($counts['daily'])
                ->color('success')
                ->icon('heroicon-o-users'),
        ];
    }

    private function serverStats(): array
    {
        if (! AdminAccess::allows(RolePermission::RESOURCE_SERVERS)) {
            return [];
        }

        $counts = app(DashboardStatisticsService::class)->counts(Server::class);

        return [
            Stat::make(trans('admin/index.dashboard.total_servers'), $counts['total'])
                ->description(trans('admin/index.dashboard.new_servers', ['count' => $counts['today']]))
                ->descriptionIcon('heroicon-o-server-stack')
                ->chart($counts['daily'])
                ->color('info')
                ->icon('heroicon-o-server-stack'),
        ];
    }

    private function softwareVersionWidget(): array
    {
        $softwareVersionService = app(SoftwareVersionService::class);

        $isLatest = $softwareVersionService->isLatestPanel();
        $version = config('app.version');

        if ($version === 'canary') {
            $headPath = base_path('.git/HEAD');

            if (is_file($headPath)) {
                $head = trim((string) file_get_contents($headPath));

                if (Str::startsWith($head, 'ref: ')) {
                    $referencePath = base_path('.git/'.trim(Str::after($head, 'ref: ')));

                    if (is_file($referencePath)) {
                        $hash = trim((string) file_get_contents($referencePath));

                        if ($hash !== '') {
                            $version .= ' ('.substr($hash, 0, 8).')';
                        }
                    }
                }
            }
        }

        return [
            Stat::make('Reviactyl Panel', $version)
                ->description(config('app.version') === 'canary' ? trans('admin/index.dashboard.developmental_build') : ($isLatest ? trans('admin/index.dashboard.up_to_date') : trans('admin/index.dashboard.outdated_build')))
                ->descriptionIcon(config('app.version') === 'canary' ? 'heroicon-o-code-bracket' : ($isLatest ? 'heroicon-o-check-badge' : 'heroicon-o-exclamation-triangle'))
                ->color(config('app.version') === 'canary' ? 'info' : ($isLatest ? 'success' : 'warning'))
                ->icon('heroicon-o-bolt'),
        ];
    }
}
