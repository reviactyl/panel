import { act, type PropsWithChildren } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import axios from 'axios';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import PublicServerStatus from './PublicServerStatus';

const mocks = vi.hoisted(() => ({ id: 'first', clearFlashes: vi.fn() }));
vi.mock('axios', () => ({ default: { get: vi.fn() } }));
vi.mock('@/api/http', () => ({ httpErrorToHuman: (error: Error) => error.message }));
vi.mock('react-router-dom', () => ({ useParams: () => ({ id: mocks.id }) }));
vi.mock('easy-peasy', () => ({ useStoreActions: () => ({ clearFlashes: mocks.clearFlashes }) }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/PageContentBlock', () => ({
    default: ({ children }: PropsWithChildren) => <div>{children}</div>,
}));
vi.mock('@/reviactyl/elements/ContentBox', () => ({
    default: ({ children }: PropsWithChildren) => <div>{children}</div>,
}));

const response = (status: string, cpu = 0, memory = 0, disk = 0, name = 'First server') => ({
    data: {
        name,
        description: 'Server description',
        status,
        utilization: { cpu_absolute: cpu, memory_bytes: memory, disk_bytes: disk },
    },
});

describe('public server status refresh', () => {
    let container: HTMLDivElement;
    let root: Root;

    beforeEach(() => {
        Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
        vi.useFakeTimers();
        vi.mocked(axios.get).mockReset();
        mocks.id = 'first';
        mocks.clearFlashes.mockClear();
        container = document.createElement('div');
        root = createRoot(container);
    });

    afterEach(async () => {
        await act(async () => root.unmount());
        vi.useRealTimers();
        vi.restoreAllMocks();
    });

    const render = async () => {
        await act(async () => root.render(<PublicServerStatus />));
    };

    const refresh = async () => {
        await act(async () => vi.advanceTimersByTime(10000));
    };

    it('updates an open page from offline to running, refreshes usage, and returns to offline', async () => {
        vi.mocked(axios.get)
            .mockResolvedValueOnce(response('offline'))
            .mockResolvedValueOnce(response('running', 12.34, 1048576, 2097152))
            .mockResolvedValueOnce(response('running', 25.5, 2097152, 3145728))
            .mockResolvedValueOnce(response('offline'));
        await render();
        expect(container.textContent).toContain('offline');
        await refresh();
        expect(container.textContent).toContain('running');
        expect(container.textContent).toContain('12.34%');
        expect(container.textContent).toContain('1 MiB');
        expect(container.textContent).toContain('2 MiB');
        await refresh();
        expect(container.textContent).toContain('25.50%');
        expect(container.textContent).toContain('3 MiB');
        await refresh();
        expect(container.textContent).toContain('offline');
        expect(container.textContent).not.toContain('running');
        expect(container.textContent).toContain('0.00%');
        expect(axios.get).toHaveBeenCalledTimes(4);
    });

    it('keeps the current content visible and does not overlap slow refreshes', async () => {
        let resolve!: (value: ReturnType<typeof response>) => void;
        vi.mocked(axios.get)
            .mockResolvedValueOnce(response('offline'))
            .mockReturnValueOnce(new Promise((done) => (resolve = done)));
        await render();
        await refresh();
        await refresh();
        expect(axios.get).toHaveBeenCalledTimes(2);
        expect(container.textContent).toContain('offline');
        expect(container.textContent).not.toContain('loading-server-status');
        await act(async () => resolve(response('running')));
        expect(container.textContent).toContain('running');
    });

    it.each([true, false])('recovers after a failed request (initial: %s)', async (initial) => {
        vi.spyOn(console, 'error').mockImplementation(() => undefined);
        if (!initial) vi.mocked(axios.get).mockResolvedValueOnce(response('offline'));
        vi.mocked(axios.get)
            .mockRejectedValueOnce(new Error('Temporarily unavailable'))
            .mockResolvedValueOnce(response('running'));
        await render();
        if (!initial) await refresh();
        expect(container.textContent).toContain('Temporarily unavailable');
        if (!initial) expect(container.textContent).toContain('offline');
        await refresh();
        expect(container.textContent).toContain('running');
        expect(container.textContent).not.toContain('Temporarily unavailable');
    });

    it('stops scheduled refreshes when the page is left', async () => {
        vi.mocked(axios.get).mockResolvedValue(response('offline'));
        await render();
        await act(async () => root.render(null));
        await refresh();
        expect(axios.get).toHaveBeenCalledTimes(1);
        expect(vi.getTimerCount()).toBe(0);
    });

    it('aborts an in-flight refresh and does not restart polling when it settles after leaving', async () => {
        let reject!: (error: Error) => void;
        vi.mocked(axios.get)
            .mockResolvedValueOnce(response('offline'))
            .mockReturnValueOnce(new Promise((_resolve, fail) => (reject = fail)));
        await render();
        await refresh();
        const signal = vi.mocked(axios.get).mock.calls[1]![1]!.signal!;
        await act(async () => root.render(null));
        expect(signal.aborted).toBe(true);
        await act(async () => reject(new Error('Canceled')));
        await refresh();
        expect(axios.get).toHaveBeenCalledTimes(2);
        expect(vi.getTimerCount()).toBe(0);
    });

    it('discards late responses from the previous server when the route changes', async () => {
        let resolve!: (value: ReturnType<typeof response>) => void;
        vi.mocked(axios.get)
            .mockReturnValueOnce(new Promise((done) => (resolve = done)))
            .mockResolvedValue(response('running', 0, 0, 0, 'Second server'));
        await render();
        const signal = vi.mocked(axios.get).mock.calls[0]![1]!.signal!;
        mocks.id = 'second';
        await render();
        expect(signal.aborted).toBe(true);
        await act(async () => resolve(response('offline')));
        expect(container.textContent).toContain('Second server');
        expect(container.textContent).not.toContain('First server');
        expect(container.textContent).toContain('running');
        await refresh();
        expect(axios.get).toHaveBeenLastCalledWith('/api/public/servers/second', expect.any(Object));
        expect(axios.get).toHaveBeenCalledTimes(3);
    });
});
