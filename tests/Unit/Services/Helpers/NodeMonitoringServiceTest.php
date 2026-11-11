<?php

namespace Tests\Unit\Services\Helpers;

use App\Models\Node;
use App\Repositories\Agent\DaemonMonitoringRepository;
use App\Services\Helpers\NodeMonitoringService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class NodeMonitoringServiceTest extends TestCase
{
    public function test_viewers_share_a_sample_but_nodes_and_configuration_changes_do_not(): void
    {
        Cache::flush();
        $node = new Node();
        $node->id = 42;
        $node->updated_at = now();
        $other = clone $node;
        $other->id = 43;
        $repository = Mockery::mock(DaemonMonitoringRepository::class);
        $repository->shouldReceive('setNode')->with($node)->twice()->andReturnSelf();
        $repository->shouldReceive('setNode')->with($other)->once()->andReturnSelf();
        $repository->shouldReceive('getSystemMonitoring')->with(3)->times(3)->andReturn(['timestamp' => 1], ['timestamp' => 2], ['timestamp' => 3]);
        $service = new NodeMonitoringService($repository);

        $this->assertSame(['timestamp' => 1], $service->get($node));
        $this->assertSame(['timestamp' => 1], $service->get($node));
        $this->assertSame(['timestamp' => 2], $service->get($other));
        $node->updated_at = now()->addSecond();
        $this->assertSame(['timestamp' => 3], $service->get($node));
    }

    public function test_expired_samples_refresh_and_failures_are_retried(): void
    {
        Cache::flush();
        $node = new Node();
        $node->id = 42;
        $repository = Mockery::mock(DaemonMonitoringRepository::class);
        $repository->shouldReceive('setNode')->with($node)->times(3)->andReturnSelf();
        $repository->shouldReceive('getSystemMonitoring')->with(3)->once()->andThrow(new \RuntimeException('offline'));
        $repository->shouldReceive('getSystemMonitoring')->with(3)->twice()->andReturn(['timestamp' => 1], ['timestamp' => 2]);
        $service = new NodeMonitoringService($repository);
        try {
            $service->get($node);
            $this->fail('Expected offline failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('offline', $error->getMessage());
        }
        $this->assertSame(['timestamp' => 1], $service->get($node));
        $this->travel(6)->seconds();
        $this->assertSame(['timestamp' => 2], $service->get($node));
    }
}
