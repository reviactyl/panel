import { act, type ReactNode, type ButtonHTMLAttributes } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { FileObject } from '@/api/server/files/loadDirectory';
import MoveFileModal from './MoveFileModal';

const mocks = vi.hoisted(() => ({ load: vi.fn(), rename: vi.fn(), error: vi.fn(), mutate: vi.fn() }));
vi.mock('@/api/server/files/loadDirectory', () => ({ default: mocks.load }));
vi.mock('@/api/server/files/renameFiles', () => ({ default: mocks.rename }));
vi.mock('@/api/server/files/createDirectory', () => ({ default: vi.fn() }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'uuid' } }, files: { directory: '/' } }),
        useStoreActions: () => vi.fn(),
    },
}));
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: mocks.error }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({ visible, children }: { visible: boolean; children: ReactNode }) => (visible ? children : null),
}));
vi.mock('@/reviactyl/elements/Button', () => ({
    default: ({
        isSecondary: _secondary,
        ...props
    }: ButtonHTMLAttributes<HTMLButtonElement> & { isSecondary?: boolean }) => <button {...props} />,
}));
const folder = (name: string) => ({ name, key: name, isFile: false }) as FileObject;
function deferred() {
    let resolve!: (files: FileObject[]) => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<FileObject[]>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
}
let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.load.mockReset();
    mocks.rename.mockResolvedValue(undefined);
    mocks.mutate.mockResolvedValue(undefined);
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});
const render = async (visible = true) => {
    await act(async () =>
        root.render(<MoveFileModal visible={visible} files={['config.txt']} onDismissed={vi.fn()} />),
    );
};
const button = (label: string) =>
    Array.from(container.querySelectorAll('button')).find((element) => element.textContent === label);
const click = async (label: string) => {
    expect(button(label)).toBeDefined();
    await act(async () => button(label)!.click());
};
async function navigateToB(a: ReturnType<typeof deferred>, b: ReturnType<typeof deferred>) {
    mocks.load.mockImplementation((_uuid: string, directory: string) => {
        if (directory === '/a') return a.promise;
        if (directory === '/b') return b.promise;
        return Promise.resolve([folder('a'), folder('b')]);
    });
    await render();
    await click('a');
    await click('/home/container');
    await click('b');
}

describe('move picker request ordering', () => {
    it.each(['success', 'failure'])('keeps selected rows until the move request settles (%s)', async (result) => {
        const pending = deferred();
        mocks.load.mockResolvedValue([folder('target')]);
        mocks.rename.mockReturnValue(pending.promise);
        await render();
        await click('target');
        await click('move');
        expect(mocks.mutate).not.toHaveBeenCalled();
        await act(async () => {
            if (result === 'success') pending.resolve([]);
            else pending.reject(new Error('Move failed'));
        });
        expect(mocks.mutate).toHaveBeenCalledExactlyOnceWith();
        if (result === 'failure') {
            expect(mocks.error).toHaveBeenCalledWith({ key: 'files', error: expect.any(Error) });
        }
    });

    it('keeps B entries and moves into B after A finishes last', async () => {
        const a = deferred();
        const b = deferred();
        await navigateToB(a, b);
        await act(async () => b.resolve([folder('b-child')]));
        await act(async () => a.resolve([folder('a-child')]));
        expect(button('a-child')).toBeUndefined();
        expect(button('b-child')).toBeDefined();
        await click('b-child');
        await click('move');
        expect(mocks.rename).toHaveBeenCalledWith('uuid', '/', [{ from: 'config.txt', to: 'b/b-child/config.txt' }]);
    });
    it.each(['success', 'failure'])('keeps B loading after a stale A %s', async (result) => {
        const a = deferred();
        const b = deferred();
        await navigateToB(a, b);
        await act(async () => {
            if (result === 'success') a.resolve([folder('a-child')]);
            else a.reject(new Error('A failed'));
        });
        expect(container.textContent).toContain('move-modal.loading');
        expect(button('a-child')).toBeUndefined();
        expect(mocks.error).not.toHaveBeenCalled();
        await act(async () => b.resolve([folder('b-child')]));
        expect(button('b-child')).toBeDefined();
    });
    it('clears previous entries and reports an active request failure', async () => {
        const a = deferred();
        const b = deferred();
        await navigateToB(a, b);
        const error = new Error('B failed');
        await act(async () => b.reject(error));
        expect(button('a')).toBeUndefined();
        expect(container.textContent).not.toContain('move-modal.loading');
        expect(mocks.error).toHaveBeenCalledWith({ key: 'files:move-modal', error });
    });
    it('ignores a request from a dismissed modal after reopening', async () => {
        const old = deferred();
        mocks.load.mockReturnValueOnce(old.promise).mockResolvedValue([folder('current')]);
        await render();
        await render(false);
        await render();
        await act(async () => old.resolve([folder('obsolete')]));
        expect(button('obsolete')).toBeUndefined();
        expect(button('current')).toBeDefined();
    });
});
