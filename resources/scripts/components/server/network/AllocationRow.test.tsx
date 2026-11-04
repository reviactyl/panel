import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Allocation } from '@/api/server/getServer';
import AllocationRow from './AllocationRow';

const mocks = vi.hoisted(() => ({
    save: vi.fn(),
    mutate: vi.fn(),
    clearFlashes: vi.fn(),
    addError: vi.fn(),
}));
vi.mock('@/api/server/network/setServerAllocationNotes', () => ({ default: mocks.save }));
vi.mock('@/api/swr/getServerAllocations', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/state/server', () => ({ ServerContext: { useStoreState: () => 'server-uuid' } }));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [true] }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: mocks.clearFlashes, clearAndAddHttpError: mocks.addError }),
}));
vi.mock('@/reviactyl/elements/InputSpinner', () => ({
    default: ({ children, visible }: { children: ReactNode; visible: boolean }) => (
        <div data-loading={visible}>{children}</div>
    ),
}));
vi.mock('@/reviactyl/elements/CopyOnClick', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/components/server/network/DeleteAllocationButton', () => ({ default: () => null }));

const allocation: Allocation = { id: 1, ip: '127.0.0.1', alias: null, port: 25565, notes: 'initial', isDefault: true };
let container: HTMLDivElement;
let root: Root;
let cached: Allocation[];

const deferred = () => {
    let resolve!: () => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<void>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
};
const render = async (overrides: Partial<Allocation> = {}) => {
    await act(async () => root.render(<AllocationRow allocation={{ ...allocation, ...overrides }} />));
};
const edit = async (notes: string) => {
    await act(async () => {
        const input = container.querySelector('textarea')!;
        Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value')!.set!.call(input, notes);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
};
const debounce = async (ms = 750) => {
    await act(async () => vi.advanceTimersByTimeAsync(ms));
};
const loading = () => container.querySelector('[data-loading]')?.getAttribute('data-loading');

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    vi.clearAllMocks();
    mocks.save.mockReset().mockResolvedValue(undefined);
    cached = [{ ...allocation }];
    mocks.mutate.mockReset().mockImplementation((updater) => {
        if (updater) cached = updater(cached);
        return Promise.resolve(cached);
    });
    container = document.createElement('div');
    root = createRoot(container);
    await render();
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.useRealTimers();
});

describe('allocation note autosaving', () => {
    it('debounces edits across rerenders and saves the latest notes', async () => {
        await edit('first');
        await debounce(400);
        await render({ alias: 'server.example.com' });
        await edit('latest');
        await debounce(749);
        expect(mocks.save).not.toHaveBeenCalled();
        await debounce(1);
        expect(mocks.save).toHaveBeenCalledExactlyOnceWith('server-uuid', 1, 'latest');
        expect(cached[0]?.notes).toBe('latest');
        expect(loading()).toBe('false');
    });

    it('waits for an older save before sending the latest edit and never caches its stale completion', async () => {
        const older = deferred();
        const newer = deferred();
        let persisted = 'initial';
        mocks.save
            .mockImplementationOnce(() =>
                older.promise.then(() => {
                    persisted = 'old';
                }),
            )
            .mockImplementationOnce(() =>
                newer.promise.then(() => {
                    persisted = 'latest';
                }),
            );
        await edit('old');
        await debounce();
        await edit('latest');
        await debounce();
        expect(mocks.save).toHaveBeenCalledTimes(1);
        await act(async () => older.resolve());
        expect(mocks.save).toHaveBeenCalledTimes(2);
        expect(mocks.save).toHaveBeenLastCalledWith('server-uuid', 1, 'latest');
        expect(cached[0]?.notes).toBe('initial');
        expect(loading()).toBe('true');
        await act(async () => newer.resolve());
        expect(persisted).toBe('latest');
        expect(cached[0]?.notes).toBe('latest');
        expect(container.querySelector('textarea')?.value).toBe('latest');
        expect(loading()).toBe('false');
    });

    it('invalidates an older completion immediately while the next edit is still debouncing', async () => {
        const older = deferred();
        mocks.save.mockReturnValueOnce(older.promise);
        await edit('old');
        await debounce();
        await edit('latest');
        await act(async () => older.resolve());
        expect(mocks.mutate).not.toHaveBeenCalled();
        expect(loading()).toBe('true');
        await debounce();
        expect(cached[0]?.notes).toBe('latest');
        expect(loading()).toBe('false');
    });

    it('skips queued edits that have already been replaced', async () => {
        const older = deferred();
        mocks.save.mockReturnValueOnce(older.promise);
        await edit('old');
        await debounce();
        await edit('intermediate');
        await debounce();
        await edit('latest');
        await debounce();
        await act(async () => older.resolve());
        expect(mocks.save.mock.calls.map((args) => args[2])).toEqual(['old', 'latest']);
        expect(cached[0]?.notes).toBe('latest');
    });

    it('continues after an obsolete failure without showing its error or clearing loading', async () => {
        const older = deferred();
        const newer = deferred();
        mocks.save.mockReturnValueOnce(older.promise).mockReturnValueOnce(newer.promise);
        await edit('old');
        await debounce();
        await edit('latest');
        await debounce();
        await act(async () => older.reject(new Error('obsolete')));
        expect(mocks.addError).not.toHaveBeenCalled();
        expect(loading()).toBe('true');
        await act(async () => newer.resolve());
        expect(cached[0]?.notes).toBe('latest');
        expect(loading()).toBe('false');
    });

    it('revalidates notes when the latest save fails after an older save succeeded', async () => {
        const older = deferred();
        const newer = deferred();
        mocks.save.mockReturnValueOnce(older.promise).mockReturnValueOnce(newer.promise);
        mocks.mutate.mockImplementation((updater) => {
            cached = updater ? updater(cached) : [{ ...allocation, notes: 'old' }];
            return Promise.resolve(cached);
        });
        await edit('old');
        await debounce();
        await edit('latest');
        await debounce();
        await act(async () => older.resolve());
        expect(mocks.mutate).not.toHaveBeenCalled();
        await act(async () => newer.reject(new Error('failed latest save')));
        expect(mocks.mutate).toHaveBeenCalledExactlyOnceWith();
        expect(cached[0]?.notes).toBe('old');
        expect(mocks.addError).toHaveBeenCalled();
        expect(loading()).toBe('false');
    });

    it('reports the current failure and allows a subsequent save even if revalidation fails', async () => {
        const error = new Error('failed');
        mocks.save.mockRejectedValueOnce(error);
        mocks.mutate.mockRejectedValueOnce(new Error('refresh failed'));
        await edit('failed');
        await debounce();
        expect(mocks.addError).toHaveBeenCalledWith({ error, key: 'server:network' });
        expect(cached[0]?.notes).toBe('initial');
        expect(loading()).toBe('false');
        await edit('');
        await debounce();
        expect(mocks.save).toHaveBeenLastCalledWith('server-uuid', 1, '');
        expect(cached[0]?.notes).toBe('');
    });

    it('flushes pending debounce on unmount', async () => {
        await edit('unsaved');
        await act(async () => root.render(null));
        await debounce();
        expect(mocks.save).toHaveBeenCalledExactlyOnceWith('server-uuid', 1, 'unsaved');
        expect(cached[0]?.notes).toBe('unsaved');
    });

    it('finishes queued saves in order after unmount', async () => {
        const older = deferred();
        mocks.save.mockReturnValueOnce(older.promise);
        await edit('old');
        await debounce();
        await edit('queued');
        await debounce();
        await act(async () => root.render(null));
        await act(async () => older.resolve());
        expect(mocks.save.mock.calls.map((args) => args[2])).toEqual(['old', 'queued']);
        expect(cached[0]?.notes).toBe('queued');
    });
});
