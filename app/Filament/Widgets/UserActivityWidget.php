<?php

namespace App\Filament\Widgets;

use App\Jobs\Administration\RefreshUserActivityLocationsJob;
use BackedEnum;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Js;

class UserActivityWidget extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 4;

    protected string $color = 'primary';

    protected ?string $maxHeight = '260px';

    protected bool $isCollapsible = true;

    protected ?string $pollingInterval = null;

    public function getHeading(): string|Htmlable|null
    {
        return trans('admin/navigation.administration.user_activity_metrics');
    }

    public function getDescription(): string|Htmlable|null
    {
        return trans('admin/navigation.administration.user_activity_description');
    }

    public function getEmptyStateHeading(): string|Htmlable
    {
        return trans('admin/navigation.administration.no_data');
    }

    public function getEmptyStateIcon(): string|BackedEnum|Htmlable
    {
        return 'heroicon-o-globe-alt';
    }

    protected function getData(): array
    {
        $countries = $this->getCountryDistribution();

        if (empty($countries)) {
            return [];
        }

        $topCountries = array_slice($countries, 0, 3);
        $otherCount = array_sum(array_column(array_slice($countries, 3), 'count'));

        if ($otherCount > 0) {
            $topCountries[] = [
                'country' => trans('admin/navigation.administration.other'),
                'code' => 'OTHER',
                'count' => $otherCount,
            ];
        }

        $total = array_sum(array_column($countries, 'count'));
        $percentages = array_map(
            fn (array $country): int => (int) round(($country['count'] / $total) * 100),
            $topCountries
        );
        $largestPercentageIndex = array_search(max($percentages), $percentages, true);
        $percentages[$largestPercentageIndex] += 100 - array_sum($percentages);

        return [
            'datasets' => [
                [
                    'label' => trans('admin/navigation.administration.active_users'),
                    'data' => $percentages,
                    'borderRadius' => 6,
                    'borderSkipped' => false,
                ],
            ],
            'labels' => array_map(
                fn (array $country): string => $country['code'] === 'UNKNOWN'
                    ? trans('admin/navigation.administration.unknown')
                    : $country['country'],
                $topCountries
            ),
        ];
    }

    protected function getOptions(): RawJs
    {
        $axisLabel = Js::from(trans('admin/navigation.administration.active_users').' (%)')->toHtml();

        return RawJs::make(<<<JS
            {
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => context.dataset.label + ': ' + context.parsed.y + '%',
                            labelColor: (context) => ({
                                backgroundColor: context.chart.options.borderColor,
                                borderColor: context.chart.options.borderColor,
                                borderWidth: 0,
                            }),
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 10,
                            callback: (value) => value + '%',
                        },
                        title: {
                            display: true,
                            text: {$axisLabel},
                        },
                    },
                },
            }
        JS);
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * Determine active user locations from each user's latest recorded activity in the last 30 days.
     *
     * @return array<int, array{country: string, code: string, count: int}>
     */
    private function getCountryDistribution(): array
    {
        return Cache::get(RefreshUserActivityLocationsJob::CACHE_KEY, []);
    }
}
