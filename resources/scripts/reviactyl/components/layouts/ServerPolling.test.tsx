import React, { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import ServerGrid from './ServerGrid';
import ServerRow from './ServerRow';
import getServerResourceUsage, { ServerStats } from '@/api/server/getServerResourceUsage';
import type { Server } from '@/api/server/getServer';

vi.mock('@/api/server/getServerResourceUsage', () => ({ default: vi.fn() }));
vi.mock('@/components/dashboard/ChangeCategoryModal', () => ({ default: () => null }));
vi.mock('react-router-dom', () => ({ Link: ({ children }: React.PropsWithChildren) => <div>{children}</div> }));
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

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    vi.mocked(getServerResourceUsage).mockReset();
    root = createRoot(document.createElement('div'));
});

afterEach(async () => {
    await act(async () => root.unmount());
    vi.useRealTimers();
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

        it('refreshes every 30 seconds while mounted and stops on unmount', async () => {
            vi.mocked(getServerResourceUsage).mockResolvedValue(stats);
            await act(async () => root.render(<Component server={server} showCategory={false} />));
            await act(async () => vi.advanceTimersByTime(60000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(3);
            await act(async () => root.render(null));
            await act(async () => vi.advanceTimersByTime(60000));
            expect(getServerResourceUsage).toHaveBeenCalledTimes(3);
            expect(vi.getTimerCount()).toBe(0);
        });
    });
}
