<?php

namespace Tests\Unit\Services\Helpers;

use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Helpers\ServerResourceUsageService;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Mockery;
use Tests\TestCase;

class ServerResourceUsageServiceTest extends TestCase
{
    public function test_cache_is_shared_between_readers_isolated_by_server_and_expires(): void
    {
        $cache = new Repository(new ArrayStore());
        $server = new Server();
        $server->uuid = 'first';
        $other = new Server();
        $other->uuid = 'other';
        $repository = Mockery::mock(DaemonServerRepository::class);
        $repository->shouldReceive('setServer')->with($server)->twice()->andReturnSelf();
        $repository->shouldReceive('setServer')->with($other)->once()->andReturnSelf();
        $repository->shouldReceive('getDetails')->times(3)->andReturn(['state' => 'running'], ['state' => 'stopped'], ['state' => 'offline']);
        $first = new ServerResourceUsageService($cache, $repository);
        $second = new ServerResourceUsageService($cache, $repository);

        $this->assertSame(['state' => 'running'], $first->get($server));
        $this->assertSame(['state' => 'running'], $second->get($server));
        $this->assertSame(['state' => 'stopped'], $second->get($other));
        $this->travel(21)->seconds();
        $this->assertSame(['state' => 'offline'], $second->get($server));
    }

    public function test_failed_fetch_releases_the_lock_and_does_not_cache_the_failure(): void
    {
        $cache = new Repository(new ArrayStore());
        $server = new Server();
        $server->uuid = 'first';
        $repository = Mockery::mock(DaemonServerRepository::class);
        $repository->shouldReceive('setServer')->with($server)->twice()->andReturnSelf();
        $repository->shouldReceive('getDetails')->once()->andThrow(new \RuntimeException('offline'));
        $repository->shouldReceive('getDetails')->once()->andReturn([]);
        $usage = new ServerResourceUsageService($cache, $repository);
        try {
            $usage->get($server);
            $this->fail('Expected Agent failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('offline', $exception->getMessage());
        }
        $this->assertSame([], $usage->get($server));
        $this->assertSame([], $usage->get($server));
    }
}
