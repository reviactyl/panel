<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Cache;
use App\Services\Helpers\GeoIPService;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

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
        $topCountriesRaw = $this->getTopCountries(3);

        if (empty($topCountriesRaw)) {
            return $schema->components([
                Section::make(trans('admin/navigation.administration.user_activity_metrics'))
                    ->icon('heroicon-o-globe-alt')
                    ->iconColor('primary')
                    ->collapsible()
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
                ? '<svg class="fi-icon" style="display: inline;color:var(--primary-200);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"> <path stroke-linecap="round" stroke-linejoin="round" d="m20.893 13.393-1.135-1.135a2.252 2.252 0 0 1-.421-.585l-1.08-2.16a.414.414 0 0 0-.663-.107.827.827 0 0 1-.812.21l-1.273-.363a.89.89 0 0 0-.738 1.595l.587.39c.59.395.674 1.23.172 1.732l-.2.2c-.212.212-.33.498-.33.796v.41c0 .409-.11.809-.32 1.158l-1.315 2.191a2.11 2.11 0 0 1-1.81 1.025 1.055 1.055 0 0 1-1.055-1.055v-1.172c0-.92-.56-1.747-1.414-2.089l-.655-.261a2.25 2.25 0 0 1-1.383-2.46l.007-.042a2.25 2.25 0 0 1 .29-.787l.09-.15a2.25 2.25 0 0 1 2.37-1.048l1.178.236a1.125 1.125 0 0 0 1.302-.795l.208-.73a1.125 1.125 0 0 0-.578-1.315l-.665-.332-.091.091a2.25 2.25 0 0 1-1.591.659h-.18c-.249 0-.487.1-.662.274a.931.931 0 0 1-1.458-1.137l1.411-2.353a2.25 2.25 0 0 0 .286-.76m11.928 9.869A9 9 0 0 0 8.965 3.525m11.928 9.868A9 9 0 1 1 8.965 3.525" /> </svg>'
                : '<img src="https://flagcdn.com/' . $code . '.svg" alt="' . $data['country'] . '" class="fi-icon" style="display: inline; border-radius: 0.25rem;" />';

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
                ->collapsible()
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
                    ++$countryData[$key]['count'];
                }
            }

            if (empty($countryData)) {
                return [];
            }

            usort($countryData, fn ($a, $b) => $b['count'] <=> $a['count']);

            return array_slice($countryData, 0, $limit);
        });
    }
}
