<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use App\Services\Helpers\GeoIPService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use App\Filament\Widgets\BaseWidget;

class UserActivityWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 4;

    private GeoIPService $geoIPService;

    public function mount(GeoIPService $geoIPService): void
    {
        $this->geoIPService = $geoIPService;
    }

    public function form(Schema $schema): Schema
    {
        $topCountries = $this->getTopCountries(3);

        $components = [];
        if (empty($topCountries)) {
            $components[] = TextEntry::make('no_data')
                ->hiddenLabel()
                ->state('No activity data available');
        } else {
            foreach ($topCountries as $index => $data) {
                $flag = $this->getFlagEmoji($data['code']);
                $rank = $index + 1;
                $components[] = TextEntry::make("country_{$rank}")
                    ->label($rank === 1 ? "Most Active Country" : "#{$rank} Country")
                    ->state("{$flag} {$data['country']}")
                    ->hint("{$data['count']} logins");
            }
        }

        return $schema->components([
            Section::make(trans('admin/index.metrics-header'))
                ->icon('heroicon-o-globe-americas')
                ->iconColor('primary')
                ->schema($components),
        ]);
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
