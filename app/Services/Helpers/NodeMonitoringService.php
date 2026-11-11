<?php

namespace App\Services\Helpers;

use App\Models\Node;
use App\Repositories\Agent\DaemonMonitoringRepository;
use Illuminate\Cache\Lock;
use Illuminate\Support\Facades\Cache;

class NodeMonitoringService
{
    public function __construct(private DaemonMonitoringRepository $repository) {}

    public function get(Node $node): array
    {
        $key = 'monitoring:node:'.$node->id.':'.$node->updated_at?->getTimestamp();
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }

        $lock = Cache::lock($key.':lock', 10);
        if ($lock instanceof Lock) {
            $lock->betweenBlockedAttemptsSleepFor(25);
        }

        return $lock->block(3, fn (): array => Cache::remember($key, 5, function () use ($node): array {
            $this->repository->setNode($node);

            return $this->repository->getSystemMonitoring(3);
        }));
    }
}
