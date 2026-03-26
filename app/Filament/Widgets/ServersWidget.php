<?php

namespace App\Filament\Widgets;

use App\Models\Node;
use App\Models\Server;
use Livewire\Attributes\On;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use App\Repositories\Wings\DaemonServerStatusRepository;

class ServersWidget extends BaseWidget
{
    protected static bool $isDiscoverable = false;

    public ?int $selectedNodeId = null;

    protected static ?int $sort = 2;

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
        }
    }

    #[On('refreshMonitoring')]
    public function refreshData(): void
    {
        // force re-render
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(trans('admin/monitoring.servers.heading'))
                ->icon('heroicon-o-server-stack')
                ->schema([
                    Placeholder::make('servers_table')
                        ->hiddenLabel()
                        ->content($this->buildTable()),
                ]),
        ]);
    }

    private function buildTable(): HtmlString
    {
        if (!$this->selectedNodeId) {
            return $this->emptyState(trans('admin/monitoring.servers.no_node'), '#94a3b8');
        }

        $wingsRows = $this->fetchWingsData();

        if ($wingsRows === null) {
            return $this->emptyState(trans('admin/monitoring.servers.error_fetch'), '#f87171');
        }

        if (empty($wingsRows)) {
            return $this->emptyState(trans('admin/monitoring.servers.no_servers'), '#94a3b8');
        }

        // Index Wings data by UUID for O(1) lookup.
        $wingsByUuid = [];
        foreach ($wingsRows as $row) {
            $uuid = $row['configuration']['uuid'] ?? null;
            if ($uuid) {
                $wingsByUuid[$uuid] = $row;
            }
        }

        // Fetch panel server names for those UUIDs.
        $names = Server::query()
            ->where('node_id', $this->selectedNodeId)
            ->whereIn('uuid', array_keys($wingsByUuid))
            ->pluck('name', 'uuid')
            ->all();

        $rows = '';
        foreach ($wingsByUuid as $uuid => $row) {
            $rows .= $this->buildRow($uuid, $row, $names[$uuid] ?? null);
        }

        $hName    = e(trans('admin/monitoring.servers.col.name'));
        $hState   = e(trans('admin/monitoring.servers.col.state'));
        $hCpu     = e(trans('admin/monitoring.servers.col.cpu'));
        $hMemory  = e(trans('admin/monitoring.servers.col.memory'));
        $hDisk    = e(trans('admin/monitoring.servers.col.disk'));
        $hNetwork = e(trans('admin/monitoring.servers.col.network'));
        $hUptime  = e(trans('admin/monitoring.servers.col.uptime'));

        $thStyle = 'padding:10px 16px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#64748b';

        return new HtmlString(<<<HTML
        <div style="overflow-x:auto;border-radius:8px">
            <table style="width:100%;border-collapse:collapse;text-align:left">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.08)">
                        <th style="{$thStyle}">{$hName}</th>
                        <th style="{$thStyle}">{$hState}</th>
                        <th style="{$thStyle};min-width:120px">{$hCpu}</th>
                        <th style="{$thStyle};min-width:180px">{$hMemory}</th>
                        <th style="{$thStyle}">{$hDisk}</th>
                        <th style="{$thStyle}">{$hNetwork}</th>
                        <th style="{$thStyle}">{$hUptime}</th>
                    </tr>
                </thead>
                <tbody>{$rows}</tbody>
            </table>
        </div>
        HTML);
    }

    private function buildRow(string $uuid, array $row, ?string $serverName): string
    {
        $state    = $row['state'] ?? 'unknown';
        $util     = $row['utilization'] ?? [];

        $cpuRaw   = (float) ($util['cpu_absolute'] ?? 0);
        $memUsed  = (int) ($util['memory_bytes'] ?? 0);
        $memLimit = (int) ($util['memory_limit_bytes'] ?? 0);
        $disk     = (int) ($util['disk_bytes'] ?? 0);
        $netRx    = (int) ($util['network']['rx_bytes'] ?? 0);
        $netTx    = (int) ($util['network']['tx_bytes'] ?? 0);
        $uptimeMs = (int) ($util['uptime'] ?? 0);

        $name       = e($serverName ?? substr($uuid, 0, 8));
        $cpuPct     = min($cpuRaw, 100);
        $memPct     = $memLimit > 0 ? min(round($memUsed / $memLimit * 100, 1), 100) : 0;

        $cpuLabel   = number_format($cpuRaw, 1) . '%';
        $memLabel   = $this->formatBytes($memUsed) . ' / ' . $this->formatBytes($memLimit);
        $diskLabel  = $this->formatBytes($disk);
        $netRxLabel = $this->formatBytes($netRx);
        $netTxLabel = $this->formatBytes($netTx);
        $uptime     = $uptimeMs > 0 ? $this->formatUptime((int) ($uptimeMs / 1000)) : '—';

        $cpuColor = $cpuPct >= 80 ? '#ef4444' : ($cpuPct >= 60 ? '#eab308' : '#22c55e');
        $memColor = $memPct >= 80 ? '#ef4444' : ($memPct >= 60 ? '#eab308' : '#3b82f6');

        [$badgeHtml, $avatarBorder] = $this->stateBadge($state);

        $initial  = e(strtoupper(mb_substr($serverName ?? $uuid, 0, 1)));
        $tdStyle  = 'padding:12px 16px;vertical-align:middle';

        return <<<HTML
        <tr style="border-bottom:1px solid rgba(255,255,255,0.05)">
            <td style="{$tdStyle}">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="flex-shrink:0;width:32px;height:32px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid {$avatarBorder};display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#f1f5f9">{$initial}</div>
                    <span style="font-size:13px;font-weight:500;color:#f1f5f9;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px" title="{$name}">{$name}</span>
                </div>
            </td>
            <td style="{$tdStyle}">{$badgeHtml}</td>
            <td style="{$tdStyle};min-width:120px">
                <div style="font-size:11px;color:#94a3b8;font-family:monospace;margin-bottom:5px">{$cpuLabel}</div>
                <div style="height:5px;width:100%;background:rgba(255,255,255,0.08);border-radius:9999px;overflow:hidden">
                    <div style="height:100%;width:{$cpuPct}%;background:{$cpuColor};border-radius:9999px"></div>
                </div>
            </td>
            <td style="{$tdStyle};min-width:180px">
                <div style="font-size:11px;color:#94a3b8;font-family:monospace;margin-bottom:5px">{$memLabel}</div>
                <div style="height:5px;width:100%;background:rgba(255,255,255,0.08);border-radius:9999px;overflow:hidden">
                    <div style="height:100%;width:{$memPct}%;background:{$memColor};border-radius:9999px"></div>
                </div>
            </td>
            <td style="{$tdStyle};font-size:12px;color:#94a3b8;font-family:monospace">{$diskLabel}</td>
            <td style="{$tdStyle}">
                <div style="font-family:monospace;font-size:11px;line-height:1.7">
                    <div style="color:#4ade80">↓ {$netRxLabel}</div>
                    <div style="color:#60a5fa">↑ {$netTxLabel}</div>
                </div>
            </td>
            <td style="{$tdStyle};font-size:12px;color:#94a3b8;font-family:monospace">{$uptime}</td>
        </tr>
        HTML;
    }

    private function stateBadge(string $state): array
    {
        [$bg, $textColor, $dotColor, $border] = match ($state) {
            'running'  => ['rgba(34,197,94,0.12)',  '#4ade80', '#22c55e', 'rgba(34,197,94,0.5)'],
            'starting' => ['rgba(234,179,8,0.12)',  '#facc15', '#eab308', 'rgba(234,179,8,0.5)'],
            'stopping' => ['rgba(249,115,22,0.12)', '#fb923c', '#f97316', 'rgba(249,115,22,0.5)'],
            'offline'  => ['rgba(148,163,184,0.08)', '#94a3b8', '#64748b', 'rgba(148,163,184,0.3)'],
            'crashed'  => ['rgba(239,68,68,0.12)',  '#f87171', '#ef4444', 'rgba(239,68,68,0.5)'],
            default    => ['rgba(148,163,184,0.08)', '#94a3b8', '#64748b', 'rgba(148,163,184,0.3)'],
        };

        $label = e(trans('admin/monitoring.servers.states.' . $state));

        $badge = <<<HTML
        <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:9999px;font-size:11px;font-weight:600;background:{$bg};color:{$textColor};border:1px solid {$border}">
            <span style="width:6px;height:6px;border-radius:50%;background:{$dotColor};flex-shrink:0"></span>
            {$label}
        </span>
        HTML;

        return [$badge, $border];
    }

    private function emptyState(string $message, string $color): HtmlString
    {
        $msg = e($message);

        return new HtmlString(<<<HTML
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 0;gap:12px">
            <svg style="width:40px;height:40px" fill="none" viewBox="0 0 24 24" stroke="#4b5563" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
            </svg>
            <p style="font-size:13px;color:{$color}">{$msg}</p>
        </div>
        HTML);
    }

    private function fetchWingsData(): ?array
    {
        try {
            $node = Node::find($this->selectedNodeId);
            if (!$node) {
                return [];
            }
            $repository = app(DaemonServerStatusRepository::class);
            $repository->setNode($node);

            return $repository->getAllServerStatus();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatBytes(int|float $bytes): string
    {
        $bytes = max((float) $bytes, 0.0);
        if ($bytes === 0.0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow   = (int) min(floor(log($bytes) / log(1024)), count($units) - 1);

        return round($bytes / (1024 ** $pow), 2) . ' ' . $units[$pow];
    }

    private function formatUptime(int $seconds): string
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
}
