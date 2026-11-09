import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { Tooltip, type ChartData, type ChartOptions, type TooltipItem, type TooltipModel } from 'chart.js';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { SWRConfig, useSWRConfig, type Cache } from 'swr';
import HistoricalGraphsContainer from './HistoricalGraphsContainer';

const mocks = vi.hoisted(() => ({
    get: vi.fn(),
    uuid: 'server-uuid',
    line: vi.fn<({ data, options }: { data: ChartData<'line'>; options: ChartOptions<'line'> }) => null>(() => null),
    stats: [5, 55].map((minute) => ({
        timestamp: new Date(2026, 10, 3, 12, minute).toISOString(),
        cpu_absolute: minute,
        memory_bytes: 1048576,
        disk_bytes: 2097152,
        network_rx_bytes: 3145728,
        network_tx_bytes: 4194304,
    })),
}));

vi.mock('react-chartjs-2', () => ({ Line: mocks.line }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) => selector({ server: { data: { uuid: mocks.uuid } } }),
    },
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/api/http', () => ({ default: { get: mocks.get } }));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/ScreenBlock', () => ({ ServerError: () => null }));

let container: HTMLDivElement;
let root: Root;
let mutate: ReturnType<typeof useSWRConfig>['mutate'];
let cache: Cache;

function Metrics() {
    mutate = useSWRConfig().mutate;
    return <HistoricalGraphsContainer />;
}

function renderMetrics() {
    root.render(
        <SWRConfig value={{ provider: () => cache, dedupingInterval: 0 }}>
            <Metrics />
        </SWRConfig>,
    );
}

function response(cpu: number) {
    return { data: { data: [{ ...mocks.stats[0], cpu_absolute: cpu }] } };
}

function latestCpu() {
    return mocks.line.mock.calls.at(-4)?.[0].data.datasets[0]?.data;
}

async function selectRange(days: string) {
    await act(async () => {
        const select = container.querySelector('select')!;
        select.value = days;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.line.mockClear();
    mocks.uuid = 'server-uuid';
    mocks.get.mockReset().mockResolvedValue({ data: { data: mocks.stats } });
    cache = new Map();
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    vi.useRealTimers();
});

describe('historical metrics sample times', () => {
    it.each([
        ['1', ['12:05', '12:55']],
        ['3', ['11/03 12:05', '11/03 12:55']],
        ['7', ['11/03 12:05', '11/03 12:55']],
    ])('preserves minutes in every chart tooltip for the %s-day range', async (days, expected) => {
        await act(async () => renderMetrics());
        if (days !== '1') {
            const select = container.querySelector('select')!;
            mocks.line.mockClear();
            await act(async () => {
                select.value = days;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        const charts = mocks.line.mock.calls;
        expect(charts).toHaveLength(4);
        for (const [{ data, options }] of charts) {
            expect(data.labels).toEqual(expected);
            const titles = data.labels!.map((label, dataIndex) =>
                Tooltip.defaults!.callbacks.title.call({ options: {} } as TooltipModel<'line'>, [
                    { chart: { data }, label, dataIndex } as TooltipItem<'line'>,
                ]),
            );
            expect(titles).toEqual(expected);
            expect(options.scales?.x?.ticks?.maxTicksLimit).toBe(10);
        }
    });
});

describe('historical metrics refresh', () => {
    beforeEach(() => vi.useFakeTimers());

    it('refreshes the mounted range with deferred data and stops after unmount', async () => {
        mocks.get.mockResolvedValueOnce(response(10));
        await act(async () => renderMetrics());
        expect(latestCpu()).toEqual([10]);

        let resolveRefresh!: (value: ReturnType<typeof response>) => void;
        mocks.get.mockImplementationOnce(() => new Promise((resolve) => (resolveRefresh = resolve)));
        await act(async () => vi.advanceTimersByTimeAsync(60_000));
        expect(mocks.get).toHaveBeenCalledTimes(2);
        expect(mocks.get).toHaveBeenLastCalledWith('/api/client/servers/server-uuid/resources/history', {
            params: { days: 1 },
        });
        expect(latestCpu()).toEqual([10]);
        await act(async () => resolveRefresh(response(20)));
        expect(latestCpu()).toEqual([20]);

        await act(async () => root.unmount());
        await act(async () => vi.advanceTimersByTimeAsync(180_000));
        expect(mocks.get).toHaveBeenCalledTimes(2);
    });

    it('ignores an older deferred refresh after a newer revalidation completes', async () => {
        mocks.get.mockResolvedValueOnce(response(10));
        await act(async () => renderMetrics());
        let resolveOlder!: (value: ReturnType<typeof response>) => void;
        mocks.get.mockImplementationOnce(() => new Promise((resolve) => (resolveOlder = resolve)));
        await act(async () => vi.advanceTimersByTimeAsync(60_000));
        expect(mocks.get).toHaveBeenCalledTimes(2);

        mocks.get.mockResolvedValueOnce(response(30));
        await act(async () => {
            await mutate([mocks.uuid, '/resources/history', '1']);
        });
        expect(latestCpu()).toEqual([30]);
        await act(async () => resolveOlder(response(20)));
        expect(latestCpu()).toEqual([30]);
    });

    it('requests a changed range immediately and isolates a deferred previous range', async () => {
        mocks.get.mockResolvedValueOnce(response(10));
        await act(async () => renderMetrics());
        let resolveOlder!: (value: ReturnType<typeof response>) => void;
        mocks.get.mockImplementationOnce(() => new Promise((resolve) => (resolveOlder = resolve)));
        await act(async () => vi.advanceTimersByTimeAsync(60_000));
        expect(mocks.get).toHaveBeenCalledTimes(2);

        mocks.get.mockResolvedValueOnce(response(70));
        await selectRange('7');
        expect(mocks.get).toHaveBeenLastCalledWith('/api/client/servers/server-uuid/resources/history', {
            params: { days: 7 },
        });
        expect(latestCpu()).toEqual([70]);
        await act(async () => resolveOlder(response(20)));
        expect(latestCpu()).toEqual([70]);
        mocks.get.mockResolvedValueOnce(response(80));
        await act(async () => vi.advanceTimersByTimeAsync(60_000));
        expect(mocks.get).toHaveBeenLastCalledWith('/api/client/servers/server-uuid/resources/history', {
            params: { days: 7 },
        });
        expect(latestCpu()).toEqual([80]);
    });

    it('isolates server navigation from a pending refresh and polls the new server', async () => {
        mocks.get.mockResolvedValueOnce(response(10));
        await act(async () => renderMetrics());
        let resolveOlder!: (value: ReturnType<typeof response>) => void;
        mocks.get.mockImplementationOnce(() => new Promise((resolve) => (resolveOlder = resolve)));
        await act(async () => vi.advanceTimersByTimeAsync(60_000));
        expect(mocks.get).toHaveBeenCalledTimes(2);

        mocks.uuid = 'second-server';
        mocks.get.mockResolvedValueOnce(response(90));
        await act(async () => renderMetrics());
        expect(latestCpu()).toEqual([90]);
        await act(async () => resolveOlder(response(20)));
        expect(latestCpu()).toEqual([90]);
        await act(async () => vi.advanceTimersByTimeAsync(60_000));
        expect(mocks.get).toHaveBeenLastCalledWith('/api/client/servers/second-server/resources/history', {
            params: { days: 1 },
        });
    });
});
