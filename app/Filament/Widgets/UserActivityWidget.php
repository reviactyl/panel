<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use App\Services\Helpers\GeoIPService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class UserActivityWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 2;

    protected static ?int $sort = 4;

    private GeoIPService $geoIPService;

    public function mount(GeoIPService $geoIPService): void
    {
        $this->geoIPService = $geoIPService;
    }

    public function form(Schema $schema): Schema
    {
        $topCountriesRaw = $this->getTopCountries(3);
        
        if (empty($topCountriesRaw)) {
            return $schema->components([
                Section::make(trans('admin/navigation.administration.user_activity_metrics'))
                    ->icon('heroicon-o-globe-alt')
                    ->iconColor('primary')
                    ->schema([
                        TextEntry::make('no_data')
                            ->hiddenLabel()
                            ->state(trans('admin/navigation.administration.no_data')),
                    ]),
            ]);
        }

        $maxCount = $topCountriesRaw[0]['count'];
        $entries = [];

        foreach ($topCountriesRaw as $index => $data) {
            $code = strtolower($data['code']);
            $percentage = $maxCount > 0 ? round(($data['count'] / $maxCount) * 100, 1) : 0;
            $isTop = $index === 0;
            
            $flagHtml = in_array($code, ['un', 'local'])
                ? '<span class="text-base">🌐</span>'
                : '<img src="https://flagcdn.com/' . $code . '.svg" alt="' . $data['country'] . '" class="w-5 h-auto rounded shadow-sm" />';
            
            $gradient = $isTop 
                ? 'linear-gradient(to right, rgb(34, 197, 94), rgb(22, 163, 74))' 
                : 'linear-gradient(to right, rgb(59, 130, 246), rgb(99, 102, 241))'; // This still makes the top country prominent enough

            $html = '
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex items-center gap-2.5 min-w-[140px]">
                        ' . $flagHtml . '
                        <span class="text-sm font-medium">
                            ' . e($data['country']) . ' (' . $percentage . '%)
                        </span>
                    </div>
                    <div class="flex-1">
                        <div class="relative rounded-lg overflow-hidden" style="height: 12px;">
                            <div class="absolute top-0 left-0 rounded-lg transition-all duration-500 ease-out" 
                                 style="width: ' . $percentage . '%; height: 12px; background: ' . $gradient . '; border-radius: 0.5rem;">
                            </div>
                        </div>
                    </div>
                </div>
            ';

            $entries[] = TextEntry::make('country_' . $index)
                ->hiddenLabel()
                ->state(new HtmlString($html));
        }

        return $schema->components([
            Section::make(trans('admin/navigation.administration.user_activity_metrics'))
                ->icon('heroicon-o-globe-alt')
                ->iconColor('primary')
                ->schema($entries),
        ]);
    }

    /**
     * Determine the top N active countries based on recent authentication logs.
     * 
     * @return array<int, array{country: string, code: string, count: int}>
     */
    private function getTopCountries(int $limit = 3): array
    {
        return Cache::remember('metric:top_active_countries_v3', 3600, function () use ($limit) {
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
}
