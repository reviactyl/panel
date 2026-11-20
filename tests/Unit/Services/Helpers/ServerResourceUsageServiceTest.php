<?php

namespace Tests\Unit\Services\Helpers;

use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Helpers\ServerResourceUsageService;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\Promise;
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

    private function server(string $uuid): Server
    {
        $server = new Server();
        $server->uuid = $uuid;

        return $server;
    }

    public function test_batch_issues_every_miss_before_waiting_through_one_shared_handler(): void
    {
        $cache = new Repository(new ArrayStore());
        $cache->put('resources:cached', ['state' => 'cached'], 20);
        $events = [];
        $handlers = [];
        $current = null;
        $repository = Mockery::mock(DaemonServerRepository::class);
        $repository->allows('setServer')->andReturnUsing(function (Server $server) use (&$current, $repository) {
            $current = $server->uuid;

            return $repository;
        });
        $repository->allows('getDetailsAsync')->andReturnUsing(function (callable $handler) use (&$current, &$events, &$handlers) {
            $uuid = $current;
            $events[] = "request {$uuid}";
            $handlers[] = $handler;
            $promise = new Promise(function () use (&$promise, &$events, $uuid) {
                $events[] = "wait {$uuid}";
                $promise->resolve(['state' => $uuid]);
            });

            return $promise;
        });
        $usage = new ServerResourceUsageService($cache, $repository);

        $result = $usage->getMany([$this->server('cached'), $this->server('a'), $this->server('b')]);

        $this->assertSame(['cached' => ['state' => 'cached'], 'a' => ['state' => 'a'], 'b' => ['state' => 'b']], $result);
        $this->assertSame(['request a', 'request b'], array_slice($events, 0, 2));
        $this->assertCount(1, array_unique(array_map('spl_object_id', $handlers)));
        // Later single reads use what the batch fetched.
        $this->assertSame(['state' => 'a'], $usage->get($this->server('a')));
    }

    public function test_batch_reports_failures_without_caching_them_and_releases_locks(): void
    {
        $cache = new Repository(new ArrayStore());
        $repository = Mockery::mock(DaemonServerRepository::class);
        $repository->allows('setServer')->andReturnSelf();
        $repository->expects('getDetailsAsync')->andReturn(Create::rejectionFor(new \RuntimeException('offline')));
        $usage = new ServerResourceUsageService($cache, $repository);

        $result = $usage->getMany([$this->server('a')]);

        $this->assertInstanceOf(\RuntimeException::class, $result['a']);
        $this->assertNull($cache->get('resources:a'));
        $this->assertTrue($cache->getStore()->lock('resources:a:lock', 1)->get());
    }

    public function test_batch_keeps_going_when_a_request_cannot_even_be_created(): void
    {
        $cache = new Repository(new ArrayStore());
        $current = null;
        $repository = Mockery::mock(DaemonServerRepository::class);
        $repository->allows('setServer')->andReturnUsing(function (Server $server) use (&$current, $repository) {
            $current = $server->uuid;

            return $repository;
        });
        $repository->allows('getDetailsAsync')->andReturnUsing(function () use (&$current) {
            if ($current === 'broken') {
                throw new \InvalidArgumentException('node missing');
            }

            return Create::promiseFor(['state' => $current]);
        });
        $usage = new ServerResourceUsageService($cache, $repository);

        $result = $usage->getMany([$this->server('broken'), $this->server('fine')]);

        $this->assertInstanceOf(\InvalidArgumentException::class, $result['broken']);
        $this->assertSame(['state' => 'fine'], $result['fine']);
        $this->assertTrue($cache->getStore()->lock('resources:broken:lock', 1)->get());
    }
}
