import { createContext } from 'react';
import type { ServerPowerState, ServerStats } from '@/api/server/getServerResourceUsage';

export interface ResourceEntry {
    stats: ServerStats | null;
    lastUpdated?: number;
    pending?: Promise<ServerStats>;
}

export type ResourceCache = Map<string, ResourceEntry>;

export const ServerResourcesContext = createContext<ResourceCache | null>(null);

const caches = new Map<string, ResourceCache>();

export const getServerResourceCache = (scope: string): ResourceCache => {
    let cache = caches.get(scope);
    if (!cache) {
        cache = new Map();
        caches.set(scope, cache);
    }

    return cache;
};

interface SocketStats {
    memory_bytes?: number;
    cpu_absolute?: number;
    disk_bytes?: number;
    uptime?: number;
    state?: string;
    network?: { rx_bytes?: number; tx_bytes?: number };
}

const powerStates: ServerPowerState[] = ['offline', 'starting', 'running', 'stopping'];

export const recordSocketStats = (scope: string, uuid: string, raw: SocketStats, isSuspended: boolean) => {
    const cache = getServerResourceCache(scope);
    const entry = cache.get(uuid) ?? { stats: null };
    const previous = entry.stats;
    const status = powerStates.find((state) => state === raw.state) ?? previous?.status ?? 'offline';

    entry.stats = isSuspended
        ? null
        : {
              status,
              isSuspended: false,
              memoryUsageInBytes: raw.memory_bytes ?? previous?.memoryUsageInBytes ?? 0,
              cpuUsagePercent: raw.cpu_absolute ?? previous?.cpuUsagePercent ?? 0,
              diskUsageInBytes: raw.disk_bytes ?? previous?.diskUsageInBytes ?? 0,
              networkRxInBytes: raw.network?.rx_bytes ?? previous?.networkRxInBytes ?? 0,
              networkTxInBytes: raw.network?.tx_bytes ?? previous?.networkTxInBytes ?? 0,
              uptime: raw.uptime ?? previous?.uptime ?? 0,
          };
    entry.lastUpdated = Date.now();
    cache.set(uuid, entry);
};

/** Updates only the power state, which the websocket reports separately from usage. */
export const recordSocketStatus = (scope: string, uuid: string, state: string) => {
    const entry = getServerResourceCache(scope).get(uuid);
    const status = powerStates.find((value) => value === state);
    if (entry?.stats && status) entry.stats = { ...entry.stats, status };
};

export const resourceCacheScope = (userUuid: string | undefined, sessionUuid: string | undefined) =>
    `${userUuid ?? ''}:${sessionUuid ?? ''}`;
