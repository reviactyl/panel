import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import http from '@/api/http';
import getServerResourceUsage from '@/api/server/getServerResourceUsage';
import { PREVIEW_TOKEN_KEY } from '@/lib/subuserPreviewStorage';
import loadServerResources from './loadServerResources';

vi.mock('@/api/http', () => ({ default: { get: vi.fn() } }));
vi.mock('@/api/server/getServerResourceUsage', async (original) => ({
    ...(await original<object>()),
    default: vi.fn(),
}));

const stats = (memory: number) => ({
    object: 'stats',
    attributes: {
        current_state: 'running',
        is_suspended: false,
        resources: {
            memory_bytes: memory,
            cpu_absolute: 1,
            disk_bytes: 2,
            network_rx_bytes: 3,
            network_tx_bytes: 4,
            uptime: 5,
        },
    },
});

describe('batched server usage', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        vi.mocked(http.get).mockReset();
        delete window.__REVIACTYL_PREFETCH__;
    });

    afterEach(() => vi.useRealTimers());

    it('asks once for every server requested together', async () => {
        vi.mocked(http.get).mockResolvedValue({ data: { data: { a: stats(10), b: stats(20) } } });

        const loads = [loadServerResources('a'), loadServerResources('b'), loadServerResources('a')];
        await vi.advanceTimersByTimeAsync(20);

        await expect(Promise.all(loads)).resolves.toMatchObject([
            { memoryUsageInBytes: 10, status: 'running' },
            { memoryUsageInBytes: 20 },
            { memoryUsageInBytes: 10 },
        ]);
        expect(http.get).toHaveBeenCalledExactlyOnceWith('/api/client/resources', { params: { servers: 'a,b' } });
    });

    it('fails only the servers that could not report usage', async () => {
        vi.mocked(http.get).mockResolvedValue({
            data: { data: { a: stats(10), b: { errors: [{ status: '504', detail: 'node offline' }] } } },
        });

        const results = Promise.allSettled([
            loadServerResources('a'),
            loadServerResources('b'),
            loadServerResources('c'),
        ]);
        await vi.advanceTimersByTimeAsync(20);

        const [a, b, c] = await results;
        expect(a.status).toBe('fulfilled');
        expect(b).toMatchObject({ status: 'rejected', reason: { message: 'node offline' } });
        expect(c).toMatchObject({ status: 'rejected', reason: { message: 'Server not found.' } });
    });

    it('splits large dashboards into requests of at most 50 servers', async () => {
        vi.mocked(http.get).mockImplementation(async (_, config) => ({
            data: {
                data: Object.fromEntries(
                    String((config as { params: { servers: string } }).params.servers)
                        .split(',')
                        .map((uuid) => [uuid, stats(1)]),
                ),
            },
        }));

        const loads = Array.from({ length: 120 }, (_, index) => loadServerResources(`s${index}`));
        await vi.advanceTimersByTimeAsync(20);
        await Promise.all(loads);

        expect(
            vi.mocked(http.get).mock.calls.map(([, config]) => (config as any).params.servers.split(',').length),
        ).toEqual([50, 50, 20]);
    });

    it('uses a prefetched batch that covers the servers instead of asking again', async () => {
        window.__REVIACTYL_PREFETCH__ = {
            '/api/client/resources?servers=a%2Cb%2Cc': {
                at: Date.now(),
                promise: Promise.resolve({
                    status: 200,
                    body: JSON.stringify({ data: { a: stats(1), b: stats(2), c: stats(3) } }),
                    headers: {},
                }),
            },
        };

        const loads = [loadServerResources('b'), loadServerResources('a')];
        await vi.advanceTimersByTimeAsync(20);

        await expect(Promise.all(loads)).resolves.toMatchObject([{ memoryUsageInBytes: 2 }, { memoryUsageInBytes: 1 }]);
        expect(http.get).not.toHaveBeenCalled();
    });

    it('rejects every waiting server when the request fails', async () => {
        vi.mocked(http.get).mockRejectedValue(new Error('offline'));

        const results = Promise.allSettled([loadServerResources('a'), loadServerResources('b')]);
        await vi.advanceTimersByTimeAsync(20);

        expect((await results).map((result) => result.status)).toEqual(['rejected', 'rejected']);
    });

    it('uses the per-server route during a subuser preview, which only simulates those', async () => {
        sessionStorage.setItem(PREVIEW_TOKEN_KEY, 'preview');
        vi.mocked(getServerResourceUsage).mockResolvedValue({ memoryUsageInBytes: 7 } as never);

        try {
            await expect(loadServerResources('a')).resolves.toMatchObject({ memoryUsageInBytes: 7 });
            await vi.advanceTimersByTimeAsync(20);
            expect(getServerResourceUsage).toHaveBeenCalledWith('a');
            expect(http.get).not.toHaveBeenCalled();
        } finally {
            sessionStorage.clear();
        }
    });
});
