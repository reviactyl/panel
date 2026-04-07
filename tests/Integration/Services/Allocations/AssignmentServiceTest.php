<?php

namespace Tests\Integration\Services\Allocations;

use App\Models\Allocation;
use App\Services\Allocations\AssignmentService;
use Tests\Integration\IntegrationTestCase;

class AssignmentServiceTest extends IntegrationTestCase
{
    public function test_ipv6_literal_allocation_is_created(): void
    {
        $server = $this->createServerModel();
        $allocationIp = '2001:db8::1';

        $this->app->make(AssignmentService::class)->handle($server->node, [
            'allocation_ip' => $allocationIp,
            'allocation_ports' => [25565],
            'allocation_alias' => null,
        ]);

        $this->assertDatabaseHas((new Allocation())->getTable(), [
            'node_id' => $server->node_id,
            'ip' => $allocationIp,
            'port' => 25565,
            'ip_alias' => null,
            'server_id' => null,
        ]);
    }

    public function test_ipv4_allocation_is_created(): void
    {
        $server = $this->createServerModel();
        $allocationIp = '192.168.1.1';

        $this->app->make(AssignmentService::class)->handle($server->node, [
            'allocation_ip' => $allocationIp,
            'allocation_ports' => [25565],
            'allocation_alias' => null,
        ]);

        $this->assertDatabaseHas((new Allocation())->getTable(), [
            'node_id' => $server->node_id,
            'ip' => $allocationIp,
            'port' => 25565,
            'ip_alias' => null,
            'server_id' => null,
        ]);
    }
}
