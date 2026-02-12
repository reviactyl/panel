<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use App\Services\Helpers\GeoIPService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class UserActivityWidget extends Widget
{
    protected string $view = 'filament.widgets.user-activity-metrics';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 4;

    private GeoIPService $geoIPService;

    public function mount(GeoIPService $geoIPService): void
    {
        $this->geoIPService = $geoIPService;
    }

    protected function getViewData(): array
    {
        $topCountriesRaw = $this->getTopCountries(3);
        $topCountries = [];

        if (!empty($topCountriesRaw)) {
            $maxCount = $topCountriesRaw[0]['count'];
            foreach ($topCountriesRaw as $data) {
                $topCountries[] = [
                    'country' => $data['country'],
                    'code' => $data['code'],
                    'count' => $data['count'],
                    'flag' => $this->getFlagEmoji($data['code']),
                    'percentage' => $maxCount > 0 ? ($data['count'] / $maxCount) * 100 : 0,
                ];
            }
        }

        return [
            'topCountries' => $topCountries,
        ];
    }

    /**
     * Determine the top N active countries based on recent authentication logs.
     * 
     * @return array<int, array{country: string, code: string, count: int}>
     */
    private function getTopCountries(int $limit = 3): array
    {
        return Cache::remember('metric:top_active_countries_v2', 3600, function () use ($limit) {
            $recentLogs = ActivityLog::query()
                ->where('event', 'auth:success')
                ->orderBy('id', 'desc')
                ->limit(200)
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
