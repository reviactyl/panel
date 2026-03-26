<?php

namespace App\Filament\Widgets;

use App\Models\Node;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Repositories\Wings\DaemonMonitoringRepository;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MonitoringWidget extends BaseWidget
{
    protected static bool $isDiscoverable = false;

    public ?int $selectedNodeId = null;

    /** @var float[] Rolling history for chart sparklines (max 10 points each) */
    public array $cpuHistory    = [];
    public array $memoryHistory = [];
    public array $diskHistory   = [];

    private const HISTORY_MAX = 10;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '1s';

    public function mount(): void
    {
    }

    #[On('nodeChanged')]
    public function updateNodeId(?int $nodeId = null): void
    {
        if ($nodeId && $nodeId !== $this->selectedNodeId) {
            $this->selectedNodeId = $nodeId;
            $this->cpuHistory     = [];
            $this->memoryHistory  = [];
            $this->diskHistory    = [];
        }
    }

    #[On('refreshMonitoring')]
    public function refreshData(): void
    {
        // force re-render
    }

    protected function getStats(): array
    {
        if (!$this->selectedNodeId) {
            return [];
        }

        try {
            $node = Node::findOrFail($this->selectedNodeId);
        } catch (ModelNotFoundException) {
            $this->selectedNodeId = null;

            return $this->getErrorStats(trans('admin/monitoring.stats.error_node_gone'));
        }

        $data = $this->getMonitoringData($node);

        if ($data === null) {
            return $this->getErrorStats(trans('admin/monitoring.stats.error_fetch'));
        }

        return [
            Stat::make(trans('admin/monitoring.stats.cpu_usage'), number_format($data['cpu']['usage_percent'], 2) . '%')
                ->description(trans('admin/monitoring.stats.cpu_cores', ['count' => $data['cpu']['cores']]))
                ->descriptionIcon('heroicon-o-cpu-chip')
                ->chart($this->pushHistory('cpuHistory', $data['cpu']['usage_percent']))
                ->color($this->getColorForPercentage($data['cpu']['usage_percent']))
                ->icon('heroicon-o-cpu-chip'),

            Stat::make(trans('admin/monitoring.stats.memory_usage'), number_format($data['memory']['usage_percent'], 2) . '%')
                ->description(
                    $this->formatBytes($data['memory']['used_bytes']) . ' / ' .
                    $this->formatBytes($data['memory']['total_bytes'])
                )
                ->descriptionIcon('heroicon-o-circle-stack')
                ->chart($this->pushHistory('memoryHistory', $data['memory']['usage_percent']))
                ->color($this->getColorForPercentage($data['memory']['usage_percent']))
                ->icon('heroicon-o-circle-stack'),

            Stat::make(trans('admin/monitoring.stats.disk_usage'), number_format($data['disk']['usage_percent'], 2) . '%')
                ->description(
                    $this->formatBytes($data['disk']['used_bytes']) . ' / ' .
                    $this->formatBytes($data['disk']['total_bytes'])
                )
                ->descriptionIcon('heroicon-o-server-stack')
                ->chart($this->pushHistory('diskHistory', $data['disk']['usage_percent']))
                ->color($this->getColorForPercentage($data['disk']['usage_percent']))
                ->icon('heroicon-o-server-stack'),

            Stat::make(trans('admin/monitoring.stats.network_traffic'), $this->formatBytes($data['network']['bytes_sent'] + $data['network']['bytes_recv']))
                ->description(
                    '↑ ' . $this->formatBytes($data['network']['bytes_sent']) . ' | ' .
                    '↓ ' . $this->formatBytes($data['network']['bytes_recv'])
                )
                ->descriptionIcon('heroicon-o-signal')
                ->color('info')
                ->icon('heroicon-o-signal'),

            Stat::make(trans('admin/monitoring.stats.uptime'), $this->formatUptime($data['runtime']['uptime_seconds']))
                ->description(trans('admin/monitoring.stats.goroutines', ['count' => $data['runtime']['goroutines']]) . ' | ' . $data['runtime']['go_version'])
                ->descriptionIcon('heroicon-o-clock')
                ->color('success')
                ->icon('heroicon-o-clock'),

            Stat::make(trans('admin/monitoring.stats.last_updated'), date('H:i:s', $data['timestamp']))
                ->description(date('Y-m-d', $data['timestamp']))
                ->descriptionIcon('heroicon-o-calendar')
                ->color('gray')
                ->icon('heroicon-o-calendar'),
        ];
    }

    protected function getMonitoringData(Node $node): ?array
    {
        try {
            $repository = app(DaemonMonitoringRepository::class);
            $repository->setNode($node);

            return $repository->getSystemMonitoring();
        } catch (\Throwable $e) {
            Log::warning('MonitoringWidget: failed to fetch data for node ' . $node->id, [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function getErrorStats(string $message): array
    {
        return [
            Stat::make(trans('admin/monitoring.stats.error'), trans('admin/monitoring.stats.error_desc'))
                ->description($message)
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }

    protected function getColorForPercentage(float $percentage): string
    {
        return match (true) {
            $percentage >= 80 => 'danger',
            $percentage >= 50 => 'warning',
            default           => 'success',
        };
    }

    protected function formatBytes(int|float $bytes): string
    {
        $bytes = max((float) $bytes, 0.0);
        if ($bytes === 0.0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow   = (int) min(floor(log($bytes) / log(1024)), count($units) - 1);

        return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }

    protected function formatUptime(int $seconds): string
    {
        $days    = intdiv($seconds, 86400);
        $hours   = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($days > 0) {
            return "{$days}d {$hours}h {$minutes}m";
        }
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Append $value to a rolling history Livewire property and return the array.
     *
     * @return float[]
     */
    protected function pushHistory(string $property, float $value): array
    {
        $this->{$property}[] = round($value, 2);

        if (count($this->{$property}) > self::HISTORY_MAX) {
            $this->{$property} = array_slice($this->{$property}, -self::HISTORY_MAX);
        }

        return $this->{$property};
    }
}
