import React, { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import ServerGrid from './ServerGrid';
import ServerRow from './ServerRow';
import type { ServerStats } from '@/api/server/getServerResourceUsage';
// The rows load usage through the batching loader; each call stands for one server's request.
import getServerResourceUsage from '@/api/server/loadServerResources';
import type { Server } from '@/api/server/getServer';
import {
    ServerResourcesContext,
    getServerResourceCache,
    recordSocketStats,
    recordSocketStatus,
} from '@/context/ServerResourcesContext';

vi.mock('@/routers/preloadServerConsole', () => ({ default: vi.fn() }));
vi.mock('@/state/hooks', () => ({
    useStoreState: (select: (state: { designify: { data: object } }) => unknown) => select({ designify: { data: {} } }),
}));
vi.mock('@/api/server/loadServerResources', () => ({ default: vi.fn() }));
vi.mock('@/components/dashboard/ChangeCategoryModal', () => ({ default: () => null }));
vi.mock('react-router-dom', () => ({
    Link: ({ children, ref }: React.PropsWithChildren<{ ref: React.Ref<HTMLAnchorElement> }>) => (
        <a ref={ref}>{children}</a>
    ),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/ui/Blur', () => ({ default: ({ children }: React.PropsWithChildren) => <div>{children}</div> }));

const server = {
    uuid: 'test-server',
    id: 'test',
    name: 'Test server',
    status: null,
    isNodeUnderMaintenance: false,
    limits: { cpu: 100, memory: 1024, disk: 1024 },
    allocations: [],
} as unknown as Server;
const stats = {
    isSuspended: false,
    status: 'running',
    cpuUsagePercent: 0,
    memoryUsageInBytes: 0,
    diskUsageInBytes: 0,
} as ServerStats;
let root: Root;
let container: HTMLDivElement;
let cache: NonNullable<React.ContextType<typeof ServerResourcesContext>>;
const render = (children: React.ReactNode) =>
    root.render(<ServerResourcesContext.Provider value={cache}>{children}</ServerResourcesContext.Provider>);
let intersect: (entries: Partial<IntersectionObserverEntry>[]) => void;
let hidden = false;
let intersecting = true;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    hidden = false;
    intersecting = true;
    Object.defineProperty(document, 'hidden', { configurable: true, get: () => hidden });
    vi.stubGlobal(
        'IntersectionObserver',
        class {
            constructor(callback: typeof intersect) {
                intersect = callback;
            }
            observe() {
                intersect([{ isIntersecting: intersecting }]);
            }
            disconnect() {}
        },
    );
    vi.mocked(getServerResourceUsage).mockReset();
    cache = new Map();
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    vi.useRealTimers();
    vi.unstubAllGlobals();
});

for (const [layout, Component] of [
    ['grid', ServerGrid],
    ['row', ServerRow],
] as const) {
    describe(`${layout} resource polling`, () => {
        it('does not start polling when the initial request settles after unmount', async () => {
            let resolve!: (value: ServerStats) => void;
            vi.mocked(getServerResourceUsage).mockReturnValue(new Promise((done) => (resolve = done)));
            await act(async () => render(<Component server={server} showCategory={false} />));
            await act(async () => render(null));
            await act(async () => resolve(stats));
            await act(async () => vi.advanceTimersByTime(90000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            expect(vi.getTimerCount()).toBe(0);
        });

        it('pauses hidden and offscreen cards, then resumes without overlapping requests', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            intersecting = false;
            await act(async () => render(<Component server={server} showCategory={false} />));
            expect(getServerResourceUsage).not.toHaveBeenCalled();
            await act(async () => intersect([{ isIntersecting: true }]));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            hidden = true;
            document.dispatchEvent(new Event('visibilitychange'));
            await act(async () => vi.advanceTimersByTimeAsync(90000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            hidden = false;
            await act(async () => document.dispatchEvent(new Event('visibilitychange')));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(2);
            await act(async () => intersect([{ isIntersecting: false }]));
            await act(async () => vi.advanceTimersByTimeAsync(90000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(2);
        });

        it('ignores a response belonging to the previous server identity', async () => {
            let resolve!: (value: ServerStats) => void;
            vi.mocked(getServerResourceUsage).mockReturnValueOnce(new Promise((done) => (resolve = done)));
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            await act(async () => render(<Component server={server} showCategory={false} />));
            await act(async () =>
                render(<Component server={{ ...server, uuid: 'other', name: 'Other server' }} showCategory={false} />),
            );
            await act(async () => resolve({ ...stats, isSuspended: true }));
            await act(async () => vi.advanceTimersByTimeAsync(30000));
            expect(vi.mocked(getServerResourceUsage).mock.calls.map(([uuid]) => uuid)).toEqual([
                'test-server',
                'other',
                'other',
            ]);
        });

        it('keeps fresh stats when a category or folder is reopened without resetting the refresh deadline', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            await act(async () => render(<Component server={server} showCategory={false} />));
            const loaded = container.textContent;
            await act(async () => render(null));
            await act(async () => vi.advanceTimersByTimeAsync(10000));
            vi.mocked(getServerResourceUsage).mockReturnValue(new Promise(() => {}));
            await act(async () => render(<Component server={server} showCategory={false} />));
            expect(container.textContent).toBe(loaded);
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            await act(async () => vi.advanceTimersByTimeAsync(19999));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            await act(async () => vi.advanceTimersByTimeAsync(1));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(2);
        });

        it('uses live websocket stats from the console instead of requesting them again', async () => {
            cache = getServerResourceCache(`websocket-${layout}`);
            recordSocketStats(
                `websocket-${layout}`,
                server.uuid,
                { state: 'starting', memory_bytes: 1, cpu_absolute: 2, disk_bytes: 3 },
                false,
            );
            recordSocketStatus(`websocket-${layout}`, server.uuid, 'running');
            vi.mocked(getServerResourceUsage).mockResolvedValue({ ...stats, status: 'offline' });

            await act(async () => render(<Component server={server} showCategory={false} />));
            expect(getServerResourceUsage).not.toHaveBeenCalled();
            expect(cache.get(server.uuid)?.stats).toMatchObject({ status: 'running', cpuUsagePercent: 2 });
            await act(async () => vi.advanceTimersByTimeAsync(30000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
        });

        it('keeps a power state that arrives before the first stats frame', async () => {
            const scope = `status-first-${layout}`;
            recordSocketStatus(scope, server.uuid, 'running');
            recordSocketStats(scope, server.uuid, { memory_bytes: 1 }, false);

            expect(getServerResourceCache(scope).get(server.uuid)?.stats).toMatchObject({
                status: 'running',
                memoryUsageInBytes: 1,
            });
        });

        it('reuses an in-flight stats request when reopened', async () => {
            let resolve!: (value: ServerStats) => void;
            vi.mocked(getServerResourceUsage).mockReturnValue(new Promise((done) => (resolve = done)));
            await act(async () => render(<Component server={server} showCategory={false} />));
            await act(async () => render(null));
            await act(async () => render(<Component server={server} showCategory={false} />));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            await act(async () => resolve(stats));
            expect(container.textContent).toContain('0 Bytes');
        });

        it('shows stale stats while refreshing a reopened folder', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            await act(async () => render(<Component server={server} showCategory={false} />));
            const loaded = container.textContent;
            await act(async () => render(null));
            await act(async () => vi.advanceTimersByTimeAsync(45000));
            let resolve!: (value: ServerStats) => void;
            vi.mocked(getServerResourceUsage).mockReturnValue(new Promise((done) => (resolve = done)));
            await act(async () => render(<Component server={server} showCategory={false} />));
            expect(container.textContent).toBe(loaded);
            expect(getServerResourceUsage).toHaveBeenCalledTimes(2);
            await act(async () => resolve({ ...stats, cpuUsagePercent: 25 }));
            expect(container.textContent).toContain('25.00');
        });

        it('retains a request completed while the category was collapsed', async () => {
            let resolve!: (value: ServerStats) => void;
            vi.mocked(getServerResourceUsage).mockReturnValue(new Promise((done) => (resolve = done)));
            await act(async () => render(<Component server={server} showCategory={false} />));
            await act(async () => render(null));
            await act(async () => resolve(stats));
            expect(vi.getTimerCount()).toBe(0);
            await act(async () => render(<Component server={server} showCategory={false} />));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            expect(container.textContent).toContain('0 Bytes');
        });

        it('isolates stats and pending requests when the dashboard cache changes', async () => {
            let resolve!: (value: ServerStats) => void;
            vi.mocked(getServerResourceUsage).mockReturnValueOnce(new Promise((done) => (resolve = done)));
            vi.mocked(getServerResourceUsage).mockResolvedValue({ ...stats, cpuUsagePercent: 25 });
            await act(async () => render(<Component server={server} showCategory={false} />));
            cache = new Map();
            await act(async () => render(<Component server={server} showCategory={false} />));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(2);
            await act(async () => resolve({ ...stats, cpuUsagePercent: 75 }));
            expect(container.textContent).toContain('25.00');
            expect(container.textContent).not.toContain('75.00');
        });

        it('preserves the cached display offscreen without refreshing until visible', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            await act(async () => render(<Component server={server} showCategory={false} />));
            const loaded = container.textContent;
            await act(async () => render(null));
            await act(async () => vi.advanceTimersByTimeAsync(45000));
            intersecting = false;
            await act(async () => render(<Component server={server} showCategory={false} />));
            expect(container.textContent).toBe(loaded);
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            await act(async () => intersect([{ isIntersecting: true }]));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(2);
        });

        it('skips suspended and maintenance servers even with cached stats', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            await act(async () => render(<Component server={server} showCategory={false} />));
            await act(async () => render(null));
            await act(async () => vi.advanceTimersByTimeAsync(45000));
            await act(async () =>
                render(<Component server={{ ...server, status: 'suspended' }} showCategory={false} />),
            );
            await act(async () => render(null));
            await act(async () =>
                render(<Component server={{ ...server, isNodeUnderMaintenance: true }} showCategory={false} />),
            );
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            expect(vi.getTimerCount()).toBe(0);
        });

        it('refreshes every 30 seconds while mounted and stops on unmount', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            await act(async () => render(<Component server={server} showCategory={false} />));
            await act(async () => vi.advanceTimersByTimeAsync(60000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(3);
            await act(async () => render(null));
            await act(async () => vi.advanceTimersByTimeAsync(60000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(3);
            expect(vi.getTimerCount()).toBe(0);
        });
    });
}
