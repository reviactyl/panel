<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use App\Services\Helpers\GeoIPService;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;

class UserActivityWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    private GeoIPService $geoIPService;

    public function mount(GeoIPService $geoIPService): void
    {
        $this->geoIPService = $geoIPService;
    }

    protected function getStats(): array
    {
        $mostActiveCountry = $this->getMostActiveCountry();

        return [
            Stat::make(trans('admin/index.most-active-country'), $mostActiveCountry)
                ->description(trans('admin/index.activity-description'))
                ->descriptionIcon('heroicon-m-globe-americas')
                ->color('primary'),
        ];
    }

    /**
     * Determine the most active country based on recent authentication logs.
     */
    private function getMostActiveCountry(): string
    {
        return Cache::remember('metric:most_active_country', 3600, function () {
            $recentLogs = ActivityLog::query()
                ->where('event', 'auth:success')
                ->orderBy('id', 'desc')
                ->limit(100)
                ->pluck('ip');

            if ($recentLogs->isEmpty()) {
                return 'No data available';
            }

            $countries = [];
            foreach ($recentLogs as $ip) {
                $country = $this->geoIPService->getCountry($ip);
                if ($country && $country !== 'Unknown') {
                    $countries[] = $country;
                }
            }

            if (empty($countries)) {
                return 'Unknown';
            }

            $counts = array_count_values($countries);
            arsort($counts);

            return array_key_first($counts) ?: 'Unknown';
        });
    }
}
