<?php

namespace App\Services\Helpers;

use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use Illuminate\Cache\Lock;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockProvider;

class ServerResourceUsageService
{
    public function __construct(private Repository $cache, private DaemonServerRepository $repository) {}

    public function get(Server $server): array
    {
        $key = "resources:$server->uuid";
        $cached = $this->cache->get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $fetch = fn (): array => $this->cache->remember($key, 20, fn (): array => $this->repository->setServer($server)->getDetails());
        $store = $this->cache->getStore();
        $timeout = (int) ceil((float) config('panel.guzzle.timeout', 15));
        if (! $store instanceof LockProvider || $timeout <= 0) {
            return $fetch();
        }

        $lock = $store->lock($key.':lock', $timeout + 5);
        if ($lock instanceof Lock) {
            $lock->betweenBlockedAttemptsSleepFor(25);
        }

        return $lock->block($timeout + 1, $fetch);
    }
}
