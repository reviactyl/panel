<?php

namespace App\Services\Helpers;

use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardStatisticsService
{
    /**
     * @param  class-string<User|Server>  $model
     * @return array{total: int, today: int, daily: int[]}
     */
    public function counts(string $model): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->addDay()->startOfDay();

        return Cache::remember('dashboard:counts:'.$model.':'.$start->toIso8601String(), 15, function () use ($model, $start, $end): array {
            $counts = $model::query()
                ->where('created_at', '>=', $start)
                ->where('created_at', '<', $end)
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->groupByRaw('DATE(created_at)')
                ->pluck('total', 'day');

            $daily = collect(range(0, 6))
                ->map(fn (int $day): int => (int) $counts->get($start->copy()->addDays($day)->toDateString(), 0))
                ->all();

            return ['total' => $model::query()->count(), 'today' => $daily[6], 'daily' => $daily];
        });
    }
}
