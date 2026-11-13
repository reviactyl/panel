import React, { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import ServerGrid from './ServerGrid';
import ServerRow from './ServerRow';
import getServerResourceUsage, { ServerStats } from '@/api/server/getServerResourceUsage';
import type { Server } from '@/api/server/getServer';

vi.mock('@/routers/preloadServerConsole', () => ({ default: vi.fn() }));
vi.mock('@/api/server/getServerResourceUsage', () => ({ default: vi.fn() }));
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
    root = createRoot(document.createElement('div'));
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
            await act(async () => root.render(<Component server={server} showCategory={false} />));
            await act(async () => root.render(null));
            await act(async () => resolve(stats));
            await act(async () => vi.advanceTimersByTime(90000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(1);
            expect(vi.getTimerCount()).toBe(0);
        });

        it('pauses hidden and offscreen cards, then resumes without overlapping requests', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            intersecting = false;
            await act(async () => root.render(<Component server={server} showCategory={false} />));
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
            await act(async () => root.render(<Component server={server} showCategory={false} />));
            await act(async () =>
                root.render(
                    <Component server={{ ...server, uuid: 'other', name: 'Other server' }} showCategory={false} />,
                ),
            );
            await act(async () => resolve({ ...stats, isSuspended: true }));
            await act(async () => vi.advanceTimersByTimeAsync(30000));
            expect(vi.mocked(getServerResourceUsage).mock.calls.map(([uuid]) => uuid)).toEqual([
                'test-server',
                'other',
                'other',
            ]);
        });

        it('refreshes every 30 seconds while mounted and stops on unmount', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            await act(async () => root.render(<Component server={server} showCategory={false} />));
            await act(async () => vi.advanceTimersByTimeAsync(60000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(3);
            await act(async () => root.render(null));
            await act(async () => vi.advanceTimersByTimeAsync(60000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(3);
            expect(vi.getTimerCount()).toBe(0);
        });
    });
}
