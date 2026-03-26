<?php

namespace App\Http\Controllers\Api\Client\Servers;

use Carbon\Carbon;
use App\Models\Server;
use App\Models\ServerStatsHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\Client\ClientApiController;
use App\Http\Requests\Api\Client\Servers\GetServerRequest;

class ResourceHistoryController extends ClientApiController
{
    /**
     * Return the historical resource usage for a server.
     * Data is aggregated based on time range:
     * - 1 day: hourly data (no aggregation)
     * - 3 days: 3-hour intervals
     * - 7 days: 6-hour intervals.
     */
    public function __invoke(GetServerRequest $request, Server $server): array
    {
        $days = (int) $request->query('days', 1);

        // Determine aggregation interval based on days
        $intervalHours = match ($days) {
            1 => 1,      // 24 hours: hourly (~24 points)
            3 => 3,      // 3 days: every 3 hours (~24 points)
            7 => 6,      // 7 days: every 6 hours (~28 points)
            default => 1,
        };

        $startDate = Carbon::now()->subDays($days);

        if ($intervalHours === 1) {
            // For 24 hours, return raw data
            $stats = ServerStatsHistory::query()
                ->where('server_id', $server->id)
                ->where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'asc')
                ->get(['created_at', 'cpu_usage', 'memory_bytes', 'disk_bytes', 'network_rx_bytes', 'network_tx_bytes']);

            return [
                'data' => $stats->map(fn ($stat) => [
                    'timestamp' => $stat->created_at->toIso8601String(),
                    'cpu_absolute' => $stat->cpu_usage,
                    'memory_bytes' => $stat->memory_bytes,
                    'disk_bytes' => $stat->disk_bytes,
                    'network_rx_bytes' => $stat->network_rx_bytes,
                    'network_tx_bytes' => $stat->network_tx_bytes,
                ]),
            ];
        }

        // For longer periods, aggregate data
        $stats = ServerStatsHistory::query()
            ->select([
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as time_bucket"),
                DB::raw('AVG(cpu_usage) as avg_cpu'),
                DB::raw('AVG(memory_bytes) as avg_memory'),
                DB::raw('AVG(disk_bytes) as avg_disk'),
                DB::raw('AVG(network_rx_bytes) as avg_network_rx'),
                DB::raw('AVG(network_tx_bytes) as avg_network_tx'),
                DB::raw('MIN(created_at) as bucket_time'),
            ])
            ->where('server_id', $server->id)
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('FLOOR(UNIX_TIMESTAMP(created_at) / (' . ($intervalHours * 3600) . '))'))
            ->orderBy('bucket_time', 'asc')
            ->get();

        return [
            'data' => $stats->map(fn ($stat) => [
                'timestamp' => Carbon::parse($stat->bucket_time)->toIso8601String(),
                'cpu_absolute' => round($stat->avg_cpu, 2),
                'memory_bytes' => (int) $stat->avg_memory,
                'disk_bytes' => (int) $stat->avg_disk,
                'network_rx_bytes' => (int) $stat->avg_network_rx,
                'network_tx_bytes' => (int) $stat->avg_network_tx,
            ]),
        ];
    }
}
