<?php

namespace App\Services\Helpers;

use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\Utils;
use Illuminate\Cache\Lock;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockProvider;

class ServerResourceUsageService
{
    private const TTL = 20;

    public function __construct(private Repository $cache, private DaemonServerRepository $repository) {}

    public function get(Server $server): array
    {
        $key = $this->key($server);
        $cached = $this->cache->get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $fetch = fn (): array => $this->cache->remember($key, self::TTL, fn (): array => $this->repository->setServer($server)->getDetails());
        $store = $this->cache->getStore();
        $timeout = $this->lockTimeout();
        if (! $store instanceof LockProvider || $timeout <= 0) {
            return $fetch();
        }

        $lock = $store->lock($key.':lock', $timeout + 5);
        if ($lock instanceof Lock) {
            $lock->betweenBlockedAttemptsSleepFor(25);
        }

        return $lock->block($timeout + 1, $fetch);
    }

    /**
     * Resource usage for several servers at once, keyed by UUID. Cached values are used as-is
     * and the rest are fetched from their nodes concurrently, so the total wait is that of the
     * slowest node rather than the sum of all of them. A server whose usage could not be loaded
     * maps to the exception that get() would have thrown.
     *
     * @param  iterable<Server>  $servers
     * @return array<string, array|\Throwable>
     */
    public function getMany(iterable $servers): array
    {
        $results = [];
        $pending = [];
        $waiting = [];
        $locks = [];
        $store = $this->cache->getStore();
        $timeout = $this->lockTimeout();
        $handler = HandlerStack::create();

        try {
            foreach ($servers as $server) {
                $cached = $this->cache->get($this->key($server));
                if (is_array($cached)) {
                    $results[$server->uuid] = $cached;

                    continue;
                }

                if ($store instanceof LockProvider && $timeout > 0) {
                    $lock = $store->lock($this->key($server).':lock', $timeout + 5);
                    if (! $lock->get()) {
                        $waiting[] = $server;

                        continue;
                    }
                    $locks[$server->uuid] = $lock;
                }

                try {
                    $pending[$server->uuid] = $this->repository->setServer($server)->getDetailsAsync($handler);
                } catch (\Throwable $exception) {
                    $results[$server->uuid] = $exception;
                }
            }

            foreach (Utils::settle($pending)->wait() as $uuid => $outcome) {
                if ($outcome['state'] === 'fulfilled') {
                    $this->cache->put($this->key($uuid), $outcome['value'], self::TTL);
                    $results[$uuid] = $outcome['value'];
                } else {
                    $results[$uuid] = $outcome['reason'];
                }
            }
        } finally {
            foreach ($locks as $lock) {
                $lock->release();
            }
        }

        foreach ($waiting as $server) {
            try {
                $results[$server->uuid] = $this->get($server);
            } catch (\Throwable $exception) {
                $results[$server->uuid] = $exception;
            }
        }

        return $results;
    }

    private function key(Server|string $server): string
    {
        return 'resources:'.($server instanceof Server ? $server->uuid : $server);
    }

    private function lockTimeout(): int
    {
        return (int) ceil((float) config('panel.guzzle.timeout', 15));
    }
}
