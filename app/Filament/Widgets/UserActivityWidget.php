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
        $topCountries = $this->getTopCountries(3);

        if (empty($topCountries)) {
            return [
                Stat::make(trans('admin/index.most-active-country'), 'No data available')
                    ->description(trans('admin/index.activity-description'))
                    ->descriptionIcon('heroicon-m-globe-americas')
                    ->color('gray'),
            ];
        }

        $stats = [];
        $rank = 1;
        foreach ($topCountries as $data) {
            $flag = $this->getFlagEmoji($data['code']);
            $label = $rank === 1 ? trans('admin/index.most-active-country') : "#{$rank} " . trans('admin/index.most-active-country');
            
            $stats[] = Stat::make($label, "{$flag} {$data['country']}")
                ->description("{$data['count']} logins recently")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($rank === 1 ? 'success' : 'primary');
            
            $rank++;
        }

        return $stats;
    }

    /**
     * Determine the top N active countries based on recent authentication logs.
     * 
     * @return array<int, array{country: string, code: string, count: int}>
     */
    private function getTopCountries(int $limit = 3): array
    {
        return Cache::remember('metric:top_active_countries', 3600, function () use ($limit) {
            $recentLogs = ActivityLog::query()
                ->where('event', 'auth:success')
                ->orderBy('id', 'desc')
                ->limit(200) // Increase sample size for better ranking
                ->pluck('ip');

            if ($recentLogs->isEmpty()) {
                return [];
            }

            $countryData = [];
            foreach ($recentLogs as $ip) {
                $info = $this->geoIPService->getCountryInfo($ip);
                if ($info && $info['country'] !== 'Unknown') {
                    $key = $info['code'];
                    if (!isset($countryData[$key])) {
                        $countryData[$key] = [
                            'country' => $info['country'],
                            'code' => $info['code'],
                            'count' => 0,
                        ];
                    }
                    $countryData[$key]['count']++;
                }
            }

            if (empty($countryData)) {
                return [];
            }

            usort($countryData, fn($a, $b) => $b['count'] <=> $a['count']);

            return array_slice($countryData, 0, $limit);
        });
    }

    /**
     * Convert ISO country code to emoji flag.
     */
    private function getFlagEmoji(string $countryCode): string
    {
        if ($countryCode === 'UN' || strlen($countryCode) !== 2) {
            return '🌐';
        }

        if ($countryCode === 'Localhost') {
            return '🏠';
        }

        $codePoints = array_map(function ($char) {
            return 127397 + ord(strtoupper($char));
        }, str_split($countryCode));

        return mb_convert_encoding('&#' . implode(';&#', $codePoints) . ';', 'UTF-8', 'HTML-ENTITIES');
    }
}
