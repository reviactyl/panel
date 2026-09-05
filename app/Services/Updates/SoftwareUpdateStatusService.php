<?php

namespace App\Services\Updates;

use Illuminate\Contracts\Cache\Repository;

class SoftwareUpdateStatusService
{
    private const CACHE_PREFIX = 'software-update:';

    public function __construct(private Repository $cache) {}

    public function get(string $component): ?array
    {
        $status = $this->cache->get(self::CACHE_PREFIX.$component);

        return is_array($status) ? $status : null;
    }

    public function set(string $component, string $state, string $message, ?string $version = null): void
    {
        $this->cache->put(self::CACHE_PREFIX.$component, [
            'state' => $state,
            'message' => $message,
            'version' => $version,
            'updated_at' => now()->toIso8601String(),
        ], now()->addDay());
    }

    public function panelKey(): string
    {
        return 'panel';
    }

    public function agentKey(int $nodeId): string
    {
        return 'agent:'.$nodeId;
    }
}
