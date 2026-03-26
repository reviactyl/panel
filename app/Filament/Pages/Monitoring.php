<?php

namespace App\Filament\Pages;

use App\Models\Node;
use Filament\Pages\Page;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use App\Repositories\Wings\DaemonMonitoringRepository;

class Monitoring extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-chart-bar';
    protected static ?int $navigationSort = 3;

    public ?int $selectedNodeId = null;

    public function mount(): void
    {
    }

    #[On('nodeChanged')]
    public function updateSelectedNode(?int $nodeId = null): void
    {
        if ($nodeId) {
            $this->selectedNodeId = $nodeId;
        }
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/monitoring.navigation.label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/monitoring.navigation.group');
    }

    public function getTitle(): string
    {
        return trans('admin/monitoring.page.title');
    }

    public function getHeading(): string
    {
        return trans('admin/monitoring.page.heading');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewDetails')
                ->label(trans('admin/monitoring.details.button'))
                ->icon('heroicon-o-chart-bar-square')
                ->color('gray')
                ->slideOver()
                ->modalHeading(trans('admin/monitoring.details.heading'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(trans('admin/monitoring.details.close'))
                ->disabled(fn (): bool => !$this->selectedNodeId)
                ->form(function (): array {
                    if (!$this->selectedNodeId) {
                        return [
                            Placeholder::make('no_data')
                                ->hiddenLabel()
                                ->content(trans('admin/monitoring.details.no_data')),
                        ];
                    }
                    $node = Node::find($this->selectedNodeId);
                    $data = $node ? $this->getMonitoringData($node) : null;

                    return $this->buildDetailsForm($data);
                }),

            Action::make('refresh')
                ->label(trans('admin/monitoring.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->dispatch('refreshMonitoring')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\NodeSelectorWidget::class,
            \App\Filament\Widgets\MonitoringWidget::class,
            \App\Filament\Widgets\ServersWidget::class,
        ];
    }

    protected function getMonitoringData(Node $node): ?array
    {
        try {
            $repository = app(DaemonMonitoringRepository::class);
            $repository->setNode($node);

            return $repository->getSystemMonitoring();
        } catch (\Exception) {
            return null;
        }
    }

    protected function buildDetailsForm(?array $data): array
    {
        if (!$data) {
            return [
                Placeholder::make('no_data')
                    ->hiddenLabel()
                    ->content(trans('admin/monitoring.details.no_data')),
            ];
        }

        return [
            Section::make(trans('admin/monitoring.details.cpu_section'))
                ->icon('heroicon-o-cpu-chip')
                ->columns(2)
                ->schema([
                    Placeholder::make('cpu_total')
                        ->label(trans('admin/monitoring.details.cpu_total'))
                        ->content(number_format($data['cpu']['usage_percent'], 2) . '%'),

                    Placeholder::make('cpu_cores')
                        ->label(trans('admin/monitoring.details.cpu_cores'))
                        ->content((string) $data['cpu']['cores']),

                    Placeholder::make('cpu_per_core')
                        ->label(trans('admin/monitoring.details.per_core'))
                        ->columnSpanFull()
                        ->content(function () use ($data): HtmlString {
                            $cores = $data['cpu']['per_core'] ?? [];
                            if (empty($cores)) {
                                return new HtmlString('<span class="text-gray-400">—</span>');
                            }
                            $items = array_map(function (int $i, float $pct): string {
                                $color = $pct >= 80 ? 'text-danger-500' : ($pct >= 50 ? 'text-warning-500' : 'text-success-500');

                                return "<div class=\"text-xs\"><span class=\"text-gray-400\">Core {$i}</span> <span class=\"{$color} font-mono\">" . number_format($pct, 1) . '%</span></div>';
                            }, array_keys($cores), $cores);

                            return new HtmlString('<div class="grid grid-cols-4 gap-x-4 gap-y-1">' . implode('', $items) . '</div>');
                        }),
                ]),

            Section::make(trans('admin/monitoring.details.memory_section'))
                ->icon('heroicon-o-circle-stack')
                ->columns(2)
                ->schema([
                    Placeholder::make('mem_total')
                        ->label(trans('admin/monitoring.details.total_memory'))
                        ->content($this->formatBytes($data['memory']['total_bytes'])),

                    Placeholder::make('mem_used')
                        ->label(trans('admin/monitoring.details.used_memory'))
                        ->content($this->formatBytes($data['memory']['used_bytes'])),

                    Placeholder::make('mem_free')
                        ->label(trans('admin/monitoring.details.free_memory'))
                        ->content($this->formatBytes($data['memory']['free_bytes'])),

                    Placeholder::make('mem_available')
                        ->label(trans('admin/monitoring.details.available_memory'))
                        ->content($this->formatBytes($data['memory']['available_bytes'])),
                ]),

            Section::make(trans('admin/monitoring.details.swap_section'))
                ->icon('heroicon-o-arrows-right-left')
                ->columns(2)
                ->schema(function () use ($data): array {
                    $swapTotal = (int) ($data['memory']['swap_total_bytes'] ?? 0);
                    if ($swapTotal === 0) {
                        return [
                            Placeholder::make('swap_none')
                                ->hiddenLabel()
                                ->content(trans('admin/monitoring.details.swap_none')),
                        ];
                    }

                    return [
                        Placeholder::make('swap_total')
                            ->label(trans('admin/monitoring.details.swap_total'))
                            ->content($this->formatBytes($swapTotal)),

                        Placeholder::make('swap_used')
                            ->label(trans('admin/monitoring.details.swap_used'))
                            ->content($this->formatBytes((int) ($data['memory']['swap_used_bytes'] ?? 0))),

                        Placeholder::make('swap_free')
                            ->label(trans('admin/monitoring.details.swap_free'))
                            ->content($this->formatBytes((int) ($data['memory']['swap_free_bytes'] ?? 0))),

                        Placeholder::make('swap_pct')
                            ->label(trans('admin/monitoring.details.swap_usage'))
                            ->content(number_format((float) ($data['memory']['swap_usage_percent'] ?? 0), 2) . '%'),
                    ];
                }),

            Section::make(trans('admin/monitoring.details.network_section'))
                ->icon('heroicon-o-signal')
                ->columns(2)
                ->schema([
                    Placeholder::make('net_sent')
                        ->label(trans('admin/monitoring.details.bytes_sent'))
                        ->content($this->formatBytes($data['network']['bytes_sent'])),

                    Placeholder::make('net_recv')
                        ->label(trans('admin/monitoring.details.bytes_recv'))
                        ->content($this->formatBytes($data['network']['bytes_recv'])),

                    Placeholder::make('net_pkts_sent')
                        ->label(trans('admin/monitoring.details.packets_sent'))
                        ->content(number_format($data['network']['packets_sent'])),

                    Placeholder::make('net_pkts_recv')
                        ->label(trans('admin/monitoring.details.packets_received'))
                        ->content(number_format($data['network']['packets_recv'])),
                ]),

            Section::make(trans('admin/monitoring.details.runtime_section'))
                ->icon('heroicon-o-cog-6-tooth')
                ->columns(2)
                ->schema([
                    Placeholder::make('rt_go')
                        ->label(trans('admin/monitoring.details.go_version'))
                        ->content($data['runtime']['go_version']),

                    Placeholder::make('rt_arch')
                        ->label(trans('admin/monitoring.details.arch'))
                        ->content($data['runtime']['arch'] ?? '—'),

                    Placeholder::make('rt_goroutines')
                        ->label(trans('admin/monitoring.details.goroutines'))
                        ->content((string) $data['runtime']['goroutines']),

                    Placeholder::make('rt_uptime')
                        ->label(trans('admin/monitoring.details.uptime'))
                        ->content($this->formatUptime($data['runtime']['uptime_seconds'])),
                ]),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        }
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
