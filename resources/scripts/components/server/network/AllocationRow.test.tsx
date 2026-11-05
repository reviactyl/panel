import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Allocation } from '@/api/server/getServer';
import AllocationRow from './AllocationRow';

const mocks = vi.hoisted(() => ({
    save: vi.fn(),
    primary: vi.fn(),
    mutate: vi.fn(),
    clearFlashes: vi.fn(),
    addError: vi.fn(),
    serverUuid: 'server-uuid',
}));
vi.mock('@/api/server/network/setServerAllocationNotes', () => ({ default: mocks.save }));
vi.mock('@/api/server/network/setPrimaryServerAllocation', () => ({ default: mocks.primary }));
vi.mock('@/api/swr/getServerAllocations', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/state/server', () => ({ ServerContext: { useStoreState: () => mocks.serverUuid } }));
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
    mocks.serverUuid = 'server-uuid';
    mocks.save.mockReset().mockResolvedValue(undefined);
    mocks.primary.mockReset().mockImplementation(async (_uuid, id) => ({ ...allocation, id, isDefault: true }));
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

    it.each([0, 750])('shares save ordering across remounts with %i ms of newer debounce elapsed', async (elapsed) => {
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
        await edit('queued');
        await debounce();
        await act(async () => root.render(null));
        await render();
        await edit('latest');
        await debounce(elapsed);
        expect(mocks.save).toHaveBeenCalledTimes(1);
        await act(async () => older.resolve());
        expect(cached[0]?.notes).toBe('initial');
        await debounce(750 - elapsed);
        expect(mocks.save.mock.calls.map((args) => args[2])).toEqual(['old', 'latest']);
        expect(loading()).toBe('true');
        await act(async () => newer.resolve());
        expect(persisted).toBe('latest');
        expect(cached[0]?.notes).toBe('latest');
        expect(loading()).toBe('false');
    });

    it.each(['server', 'allocation'])('keeps independent %s saves separate across remounts', async (identity) => {
        const older = deferred();
        const newer = deferred();
        mocks.save.mockReturnValueOnce(older.promise).mockReturnValueOnce(newer.promise);
        await edit('old');
        await debounce();
        await act(async () => root.render(null));
        if (identity === 'server') mocks.serverUuid = 'other-server';
        await render(identity === 'allocation' ? { id: 2 } : {});
        await edit('other allocation notes');
        await debounce();
        expect(mocks.save).toHaveBeenCalledTimes(2);
        expect(mocks.save).toHaveBeenLastCalledWith(
            identity === 'server' ? 'other-server' : 'server-uuid',
            identity === 'allocation' ? 2 : 1,
            'other allocation notes',
        );
        await act(async () => newer.resolve());
        await act(async () => older.resolve());
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

describe('primary allocation saving', () => {
    const renderRows = async () => {
        await act(async () =>
            root.render(
                <>
                    {cached.map((item) => (
                        <div key={item.id} data-allocation={item.id}>
                            <AllocationRow allocation={item} />
                        </div>
                    ))}
                </>,
            ),
        );
    };
    const choose = async (id: number) => {
        await act(async () => {
            const button = Array.from(container.querySelectorAll(`[data-allocation="${id}"] button`)).find(
                (button) => button.textContent === 'Make Primary',
            ) as HTMLButtonElement;
            button.click();
        });
        await renderRows();
    };
    const primaryId = () => cached.find((item) => item.isDefault)?.id;

    beforeEach(async () => {
        cached = [
            allocation,
            { ...allocation, id: 2, port: 25566, isDefault: false },
            { ...allocation, id: 3, port: 25567, isDefault: false },
        ];
        await renderRows();
    });

    it('serializes overlapping successful choices and preserves the latest primary and displayed address', async () => {
        const older = deferred();
        const newer = deferred();
        let persisted = 1;
        mocks.primary.mockImplementation((_uuid, id) =>
            (id === 2 ? older : newer).promise.then(() => {
                persisted = id;
                return { ...allocation, id, isDefault: true };
            }),
        );
        await choose(2);
        await choose(3);
        const callsWhilePending = mocks.primary.mock.calls.length;
        expect(primaryId()).toBe(3);
        await act(async () => newer.resolve());
        await act(async () => older.resolve());
        await renderRows();
        expect(persisted).toBe(3);
        expect(primaryId()).toBe(persisted);
        expect(callsWhilePending).toBe(1);
        expect(container.querySelector('[data-allocation="3"] button')?.textContent).toBe('Primary');
    });

    it('shares ordering across rows remounted while a request is pending', async () => {
        const older = deferred();
        mocks.primary.mockImplementationOnce(() => older.promise.then(() => ({ ...allocation, id: 2 })));
        await choose(2);
        await act(async () => root.render(null));
        await renderRows();
        await choose(3);
        expect(mocks.primary).toHaveBeenCalledTimes(1);
        await act(async () => older.resolve());
        expect(mocks.primary.mock.calls.map((args) => args[1])).toEqual([2, 3]);
        expect(primaryId()).toBe(3);
    });

    it('confirms an ordinary successful change without overwriting newer notes', async () => {
        const request = deferred();
        mocks.primary.mockImplementationOnce(() =>
            request.promise.then(() => ({ ...allocation, id: 2, notes: 'stale response notes', isDefault: true })),
        );
        await choose(2);
        cached[1] = { ...cached[1]!, notes: 'new notes' };
        await act(async () => request.resolve());
        expect(primaryId()).toBe(2);
        expect(cached[1]?.notes).toBe('new notes');
        expect(mocks.primary).toHaveBeenCalledExactlyOnceWith('server-uuid', 2);
        expect(mocks.addError).not.toHaveBeenCalled();
    });

    it('continues after an obsolete failure without reverting the newer choice', async () => {
        const older = deferred();
        mocks.primary.mockReturnValueOnce(older.promise);
        await choose(2);
        await choose(3);
        await act(async () => older.reject(new Error('obsolete')));
        expect(primaryId()).toBe(3);
        expect(mocks.addError).not.toHaveBeenCalled();
        expect(mocks.mutate.mock.calls.every((args) => args.length > 0)).toBe(true);
        expect(mocks.primary.mock.calls.map((args) => args[1])).toEqual([2, 3]);
    });

    it('revalidates after the latest failure and accepts another choice', async () => {
        const error = new Error('failed primary save');
        mocks.primary.mockRejectedValueOnce(error);
        mocks.mutate.mockImplementation((updater) => {
            cached = updater ? updater(cached) : cached.map((item) => ({ ...item, isDefault: item.id === 1 }));
            return Promise.resolve(cached);
        });
        await choose(2);
        expect(primaryId()).toBe(1);
        expect(mocks.mutate).toHaveBeenCalledWith();
        expect(mocks.addError).toHaveBeenCalledWith({ error, key: 'server:network' });
        await choose(3);
        expect(primaryId()).toBe(3);
    });

    it('recovers the persisted primary when the latest queued change fails', async () => {
        const older = deferred();
        const newer = deferred();
        let persisted = 1;
        mocks.primary
            .mockImplementationOnce(() =>
                older.promise.then(() => {
                    persisted = 2;
                    return { ...allocation, id: 2, isDefault: true };
                }),
            )
            .mockReturnValueOnce(newer.promise);
        mocks.mutate.mockImplementation((updater) => {
            cached = updater ? updater(cached) : cached.map((item) => ({ ...item, isDefault: item.id === persisted }));
            return Promise.resolve(cached);
        });
        await choose(2);
        await choose(3);
        await act(async () => older.resolve());
        expect(primaryId()).toBe(3);
        await act(async () => newer.reject(new Error('latest failed')));
        expect(primaryId()).toBe(2);
        expect(mocks.addError).toHaveBeenCalledTimes(1);
        await choose(1);
        expect(primaryId()).toBe(1);
    });

    it('skips replaced queued choices and retains only the latest choice', async () => {
        const older = deferred();
        mocks.primary.mockImplementationOnce(() => older.promise.then(() => ({ ...allocation, id: 2 })));
        await choose(2);
        await choose(3);
        await choose(1);
        await act(async () => older.resolve());
        expect(mocks.primary.mock.calls.map((args) => args[1])).toEqual([2, 1]);
        expect(primaryId()).toBe(1);
    });

    it('continues saving after both a request and failure revalidation fail', async () => {
        const request = deferred();
        mocks.primary.mockReturnValueOnce(request.promise);
        await choose(2);
        mocks.mutate.mockRejectedValueOnce(new Error('refresh failed'));
        await act(async () => request.reject(new Error('save failed')));
        await choose(3);
        expect(primaryId()).toBe(3);
        expect(mocks.primary.mock.calls.map((args) => args[1])).toEqual([2, 3]);
    });

    it('keeps different servers independent', async () => {
        const older = deferred();
        mocks.primary.mockImplementationOnce(() => older.promise.then(() => ({ ...allocation, id: 2 })));
        await choose(2);
        await act(async () => root.render(null));
        mocks.serverUuid = 'other-server';
        cached = cached.map((item) => ({ ...item, isDefault: item.id === 1 }));
        await renderRows();
        await choose(3);
        expect(mocks.primary).toHaveBeenLastCalledWith('other-server', 3);
        await act(async () => older.resolve());
    });
});
