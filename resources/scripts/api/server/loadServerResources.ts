import http from '@/api/http';
import { takePrefetchedCovering } from '@/api/prefetch';
import { rawDataToServerStats, type ServerStats } from '@/api/server/getServerResourceUsage';

const BATCH_WINDOW = 15;
const MAX_PER_REQUEST = 50;

interface Waiter {
    resolve: (stats: ServerStats) => void;
    reject: (error: unknown) => void;
}

interface BatchEntry {
    object?: string;
    attributes?: unknown;
    errors?: { status?: string; detail?: string }[];
}

let queue = new Map<string, Waiter[]>();
let timer: ReturnType<typeof setTimeout> | undefined;

const settle = (uuids: string[], data: Record<string, BatchEntry | undefined>, waiting: Map<string, Waiter[]>) => {
    for (const uuid of uuids) {
        const entry = data[uuid];
        for (const waiter of waiting.get(uuid) ?? []) {
            if (entry?.object === 'stats') waiter.resolve(rawDataToServerStats(entry.attributes));
            else waiter.reject(Object.assign(new Error(entry?.errors?.[0]?.detail ?? 'Server not found.'), { entry }));
        }
    }
};

const flush = async () => {
    timer = undefined;
    const waiting = queue;
    queue = new Map();
    const uuids = [...waiting.keys()];

    for (let index = 0; index < uuids.length; index += MAX_PER_REQUEST) {
        const chunk = uuids.slice(index, index + MAX_PER_REQUEST);
        try {
            const prefetched = await takePrefetchedCovering('/api/client/resources', 'servers', chunk)?.catch(
                () => undefined,
            );
            const data =
                prefetched && prefetched.status === 200
                    ? JSON.parse(prefetched.body).data
                    : (await http.get('/api/client/resources', { params: { servers: chunk.join(',') } })).data.data;
            settle(chunk, data ?? {}, waiting);
        } catch (error) {
            for (const uuid of chunk) for (const waiter of waiting.get(uuid) ?? []) waiter.reject(error);
        }
    }
};

export default function loadServerResources(uuid: string): Promise<ServerStats> {
    return new Promise((resolve, reject) => {
        const waiters = queue.get(uuid) ?? [];
        waiters.push({ resolve, reject });
        queue.set(uuid, waiters);
        timer ??= setTimeout(() => void flush(), BATCH_WINDOW);
    });
}
