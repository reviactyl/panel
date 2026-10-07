import { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import useScheduleAnimation from './useScheduleAnimation';
import type { RunRequestState } from './RunScheduleButton';

let container: HTMLDivElement;
let root: Root;

function Status({ processing, request }: { processing: boolean; request: RunRequestState }) {
    return <span>{useScheduleAnimation(processing, request)}</span>;
}

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    vi.useRealTimers();
});

const render = async (processing = false, request: RunRequestState = 'idle') => {
    await act(async () => root.render(<Status processing={processing} request={request} />));
};
const advance = async (ms: number) => {
    await act(async () => vi.advanceTimersByTime(ms));
};

describe('schedule completion feedback', () => {
    it('shows one full spin for immediate completion, then a check before the status', async () => {
        await render();
        expect(container.textContent).toBe('idle');
        await render(false, 'pending');
        await advance(50);
        await render();
        await advance(949);
        expect(container.textContent).toBe('spinning');
        await advance(1);
        expect(container.textContent).toBe('check');
        await advance(1600);
        expect(container.textContent).toBe('idle');
    });

    it('waits for real completion on longer runs', async () => {
        await render(true);
        await advance(10000);
        expect(container.textContent).toBe('spinning');
        await render();
        await advance(0);
        expect(container.textContent).toBe('check');
    });

    it('does not show a completion check when the execution request fails', async () => {
        await render(false, 'pending');
        await render(false, 'failed');
        await advance(3000);
        expect(container.textContent).toBe('idle');
        await render(true, 'failed');
        await render(false, 'failed');
        await advance(1000);
        expect(container.textContent).toBe('check');
    });

    it('restarts feedback when another run starts during the check', async () => {
        await render(false, 'pending');
        await render();
        await advance(1000);
        expect(container.textContent).toBe('check');
        await render(false, 'pending');
        await advance(2000);
        expect(container.textContent).toBe('spinning');
        await render();
        await advance(0);
        expect(container.textContent).toBe('check');
    });

    it('cancels animation timers when navigating away', async () => {
        await render(false, 'pending');
        await render();
        await act(async () => root.render(null));
        expect(vi.getTimerCount()).toBe(0);
    });
});
