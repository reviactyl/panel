<?php

namespace Tests\Integration\Api\Client\Server;

use App\Models\ServerStatsHistory;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class ResourceHistoryControllerTest extends ClientApiIntegrationTestCase
{
    #[DataProvider('historyRanges')]
    public function test_history_returns_ordered_server_data_in_the_requested_intervals(int $days, int $intervalHours): void
    {
        $this->travelTo(Carbon::parse('2026-11-04 12:00:00'));
        [$user, $server] = $this->generateTestAccount();
        $otherServer = $this->createServerModel();
        $start = Carbon::now()->subDays($days)->addDay()->startOfDay();
        $times = [
            $start->copy()->addMinutes(30),
            $start->copy()->addHours($intervalHours)->subSecond(),
            $start->copy()->addHours($intervalHours),
        ];

        foreach ([2, 1, 0] as $index) {
            ServerStatsHistory::query()->create([
                'server_id' => $server->id,
                'created_at' => $times[$index],
                'cpu_usage' => [10.125, 30.125, 80][$index],
                'memory_bytes' => [100, 201, 800][$index],
                'disk_bytes' => [200, 401, 1600][$index],
                'network_rx_bytes' => [300, 601, 2400][$index],
                'network_tx_bytes' => [400, 801, 3200][$index],
            ]);
        }

        ServerStatsHistory::query()->create([
            'server_id' => $server->id,
            'created_at' => Carbon::now()->subDays($days)->subSecond(),
            'cpu_usage' => 999,
        ]);
        ServerStatsHistory::query()->create([
            'server_id' => $otherServer->id,
            'created_at' => $times[0],
            'cpu_usage' => 999,
        ]);

        $data = $days === 1 ? [
            ['timestamp' => $times[0]->toIso8601String(), 'cpu_absolute' => 10.125, 'memory_bytes' => 100, 'disk_bytes' => 200, 'network_rx_bytes' => 300, 'network_tx_bytes' => 400],
            ['timestamp' => $times[1]->toIso8601String(), 'cpu_absolute' => 30.125, 'memory_bytes' => 201, 'disk_bytes' => 401, 'network_rx_bytes' => 601, 'network_tx_bytes' => 801],
            ['timestamp' => $times[2]->toIso8601String(), 'cpu_absolute' => 80, 'memory_bytes' => 800, 'disk_bytes' => 1600, 'network_rx_bytes' => 2400, 'network_tx_bytes' => 3200],
        ] : [
            ['timestamp' => $times[0]->toIso8601String(), 'cpu_absolute' => 20.13, 'memory_bytes' => 150, 'disk_bytes' => 300, 'network_rx_bytes' => 450, 'network_tx_bytes' => 600],
            ['timestamp' => $times[2]->toIso8601String(), 'cpu_absolute' => 80, 'memory_bytes' => 800, 'disk_bytes' => 1600, 'network_rx_bytes' => 2400, 'network_tx_bytes' => 3200],
        ];

        $this->actingAs($user)
            ->getJson("/api/client/servers/{$server->uuid}/resources/history?days={$days}")
            ->assertOk()
            ->assertExactJson(['data' => $data]);
    }

    #[TestWith([1])]
    #[TestWith([3])]
    #[TestWith([7])]
    public function test_history_without_samples_returns_an_empty_list(int $days): void
    {
        [$user, $server] = $this->generateTestAccount();

        $this->actingAs($user)
            ->getJson("/api/client/servers/{$server->uuid}/resources/history?days={$days}")
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public static function historyRanges(): array
    {
        return [
            'one day' => [1, 1],
            'three days' => [3, 3],
            'seven days' => [7, 6],
        ];
    }
}
