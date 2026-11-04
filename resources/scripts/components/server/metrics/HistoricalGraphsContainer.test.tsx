import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { Tooltip, type ChartData, type ChartOptions, type TooltipItem, type TooltipModel } from 'chart.js';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import HistoricalGraphsContainer from './HistoricalGraphsContainer';

const mocks = vi.hoisted(() => ({
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
vi.mock('swr', () => ({ default: () => ({ data: { data: mocks.stats } }) }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'server-uuid' } } }),
    },
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/api/http', () => ({ default: {} }));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/ScreenBlock', () => ({ ServerError: () => null }));

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.line.mockClear();
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
});

describe('historical metrics sample times', () => {
    it.each([
        ['1', ['12:05', '12:55']],
        ['3', ['11/03 12:05', '11/03 12:55']],
        ['7', ['11/03 12:05', '11/03 12:55']],
    ])('preserves minutes in every chart tooltip for the %s-day range', async (days, expected) => {
        await act(async () => root.render(<HistoricalGraphsContainer />));
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
