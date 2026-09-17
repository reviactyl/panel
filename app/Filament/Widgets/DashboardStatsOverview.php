<?php

namespace App\Filament\Widgets;

use App\Models\Server;
use App\Models\User;
use App\Services\Helpers\SoftwareVersionService;
use Carbon\CarbonInterface;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class DashboardStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $start = now()->subDays(6)->startOfDay();

        return [
            Stat::make(trans('admin/index.dashboard.total_users'), User::query()->count())
                ->description(trans('admin/index.dashboard.new_users', ['count' => User::query()->whereDate('created_at', today())->count()]))
                ->descriptionIcon('heroicon-o-users')
                ->chart($this->getDailyCreatedCounts(User::class, $start))
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make(trans('admin/index.dashboard.total_servers'), Server::query()->count())
                ->description(trans('admin/index.dashboard.new_servers', ['count' => Server::query()->whereDate('created_at', today())->count()]))
                ->descriptionIcon('heroicon-o-server-stack')
                ->chart($this->getDailyCreatedCounts(Server::class, $start))
                ->color('info')
                ->icon('heroicon-o-server-stack'),

            ...$this->softwareVersionWidget(),
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

    /**
     * @param  class-string<User|Server>  $model
     * @return int[]
     */
    private function getDailyCreatedCounts(string $model, CarbonInterface $start): array
    {
        $counts = $model::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (User|Server $record): string => $record->created_at?->toDateString() ?? '')
            ->map->count();

        return collect(range(0, 6))
            ->map(fn (int $day): int => $counts->get($start->copy()->addDays($day)->toDateString(), 0))
            ->all();
    }
}
