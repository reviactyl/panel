import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import type { ChartData } from 'chart.js';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { SocketEvent } from '@/components/server/events';
import StatGraphs from './StatGraphs';

const mocks = vi.hoisted(() => ({ stats: (_data: string) => {} }));

vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ status: { value: 'running' }, server: { data: { limits: { cpu: 100, memory: 1024 } } } }),
    },
}));
vi.mock('@/plugins/useWebsocketEvent', () => ({
    default: (event: SocketEvent, callback: (data: string) => void) => {
        if (event === SocketEvent.STATS) mocks.stats = callback;
    },
}));
vi.mock('react-chartjs-2', () => ({
    Line: ({ data }: { data: ChartData<'line'> }) => (
        <output data-chart={data.datasets[0]?.label}>{JSON.stringify(data.datasets)}</output>
    ),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ content, children }: { content: string; children: ReactNode }) => (
        <span data-tooltip={content}>{children}</span>
    ),
}));

let container: HTMLDivElement;
let root: Root;

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    container = document.createElement('div');
    root = createRoot(container);
    await act(async () => root.render(<StatGraphs />));
});

afterEach(async () => {
    await act(async () => root.unmount());
});

const sample = async (rx: number, tx: number) => {
    await act(async () =>
        mocks.stats(JSON.stringify({ cpu_absolute: 1, memory_bytes: 1024, network: { rx_bytes: rx, tx_bytes: tx } })),
    );
};

const datasets = (): ChartData<'line'>['datasets'] =>
    JSON.parse(container.querySelector('[data-chart="network-in"]')!.textContent!);
const latest = () => datasets().map((dataset) => dataset.data.at(-1));

describe('console network graph directions', () => {
    it.each([
        [1100, 150, 1000, 50],
        [150, 1100, 50, 1000],
    ])('plots RX=%i and TX=%i in the inbound and outbound series', async (rx, tx, inbound, outbound) => {
        await sample(100, 100);
        await sample(rx, tx);
        expect(latest()).toEqual([inbound, outbound]);
        expect(datasets()).toMatchObject([
            { label: 'network-in', borderColor: '#60a5fa' },
            { label: 'network-out', borderColor: '#fbbf24' },
        ]);
        expect(container.querySelector('[data-tooltip="inbound"] svg')?.getAttribute('class')).toContain(
            'text-blue-400',
        );
        expect(container.querySelector('[data-tooltip="outbound"] svg')?.getAttribute('class')).toContain(
            'text-amber-400',
        );
    });

    it('starts with zero deltas even when the first counters are nonzero', async () => {
        await sample(1000, 50);
        expect(latest()).toEqual([0, 0]);
    });

    it.each([
        [10, 150, 0, 50],
        [1100, 10, 1000, 0],
        [10, 10, 0, 0],
    ])('clamps reset counters RX=%i and TX=%i and uses their new baseline', async (rx, tx, inbound, outbound) => {
        await sample(100, 100);
        await sample(rx, tx);
        expect(latest()).toEqual([inbound, outbound]);
        await sample(rx + 500, tx + 20);
        expect(latest()).toEqual([500, 20]);
    });
});
