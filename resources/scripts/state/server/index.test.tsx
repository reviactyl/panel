import { act } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { ServerContext } from './index';
import getServer, { Server } from '@/api/server/getServer';

vi.mock('@/api/server/getServer', () => ({ default: vi.fn() }));

let root: Root;
let store: ReturnType<typeof ServerContext.useStore>;
const Capture = () => {
    store = ServerContext.useStore();
    return null;
};
const response = (id: string): [Server, string[]] => [{ id, uuid: id } as Server, [id]];
const deferred = () => {
    let resolve!: (value: [Server, string[]]) => void;
    const promise = new Promise<[Server, string[]]>((done) => (resolve = done));
    return { promise, resolve };
};

beforeEach(async () => {
    vi.mocked(getServer).mockReset();
    root = createRoot(document.createElement('div'));
    await act(async () => {
        root.render(
            <ServerContext.Provider>
                <Capture />
            </ServerContext.Provider>,
        );
    });
});
afterEach(async () => {
    await act(async () => root.unmount());
});

it('keeps A and its permissions when an older B request finishes after returning to A', async () => {
    const b = deferred();
    const a = deferred();
    vi.mocked(getServer)
        .mockResolvedValueOnce(response('A'))
        .mockReturnValueOnce(b.promise)
        .mockReturnValueOnce(a.promise);
    await store.getActions().server.getServer('A');
    store.getActions().clearServerState();
    const loadB = store.getActions().server.getServer('B');
    store.getActions().clearServerState();
    const loadA = store.getActions().server.getServer('A');
    a.resolve(response('A'));
    await loadA;
    b.resolve(response('B'));
    await loadB;
    expect(store.getState().server.data?.id).toBe('A');
    expect(store.getState().server.permissions).toEqual(['A']);
});

it('does not repopulate cleared state when navigating away during a request', async () => {
    const a = deferred();
    vi.mocked(getServer).mockReturnValueOnce(a.promise);
    const load = store.getActions().server.getServer('A');
    store.getActions().clearServerState();
    a.resolve(response('A'));
    await load;
    expect(store.getState().server.data).toBeUndefined();
    expect(store.getState().server.permissions).toEqual([]);
});

it('loads the selected server and propagates current request failures', async () => {
    vi.mocked(getServer).mockResolvedValueOnce(response('B')).mockRejectedValueOnce(new Error('Unavailable'));
    await store.getActions().server.getServer('B');
    expect(store.getState().server.data?.id).toBe('B');
    expect(store.getState().server.permissions).toEqual(['B']);
    await expect(store.getActions().server.getServer('A')).rejects.toThrow('Unavailable');
});

it('ignores an older load even without a state clear between requests', async () => {
    const old = deferred();
    vi.mocked(getServer).mockReturnValueOnce(old.promise).mockResolvedValueOnce(response('B'));
    const first = store.getActions().server.getServer('A');
    await store.getActions().server.getServer('B');
    old.resolve(response('A'));
    await first;
    expect(store.getState().server.data?.id).toBe('B');
    expect(store.getState().server.permissions).toEqual(['B']);
});
