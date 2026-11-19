import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import FlashMessageRender from '@/components/FlashMessageRender';
import flashes from '@/state/flashes';
import RenameFileModal from './RenameFileModal';
import ChmodFileModal from './ChmodFileModal';

const mocks = vi.hoisted(() => ({
    rename: vi.fn(),
    chmod: vi.fn(),
    mutate: vi.fn(),
    select: vi.fn(),
}));
vi.mock('@/api/server/files/renameFiles', () => ({ default: mocks.rename }));
vi.mock('@/api/server/files/chmodFiles', () => ({ default: mocks.chmod }));
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { uuid: 'uuid' } }, files: { directory: '/config' } }),
        useStoreActions: () => mocks.select,
    },
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({
        visible,
        children,
        showSpinnerOverlay,
    }: {
        visible: boolean;
        children: ReactNode;
        showSpinnerOverlay: boolean;
    }) =>
        visible ? (
            <div role='dialog' aria-busy={showSpinnerOverlay}>
                {children}
            </div>
        ) : null,
}));

const cases = [
    {
        action: 'rename',
        files: ['a.txt'],
        value: 'existing.txt',
        scope: 'files:rename-modal',
        data: [{ from: 'a.txt', to: 'existing.txt' }],
    },
    {
        action: 'rename',
        files: ['a.txt'],
        value: 'target/a.txt',
        scope: 'files:rename-modal',
        data: [{ from: 'a.txt', to: 'target/a.txt' }],
    },
    {
        action: 'move',
        files: ['a.txt'],
        value: 'target/a.txt',
        scope: 'files:rename-modal',
        data: [{ from: 'a.txt', to: 'target/a.txt' }],
    },
    {
        action: 'move',
        files: ['a.txt', 'b.txt'],
        value: 'target',
        scope: 'files:rename-modal',
        data: [
            { from: 'a.txt', to: 'target/a.txt' },
            { from: 'b.txt', to: 'target/b.txt' },
        ],
    },
    {
        action: 'chmod',
        files: ['a.txt'],
        value: '750',
        scope: 'files:chmod-modal',
        data: [{ file: 'a.txt', mode: '750' }],
    },
    {
        action: 'chmod',
        files: ['a.txt', 'b.txt'],
        value: '1750',
        scope: 'files:chmod-modal',
        data: [
            { file: 'a.txt', mode: '1750' },
            { file: 'b.txt', mode: '1750' },
        ],
    },
];
let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.resetAllMocks();
    mocks.mutate.mockResolvedValue([]);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

const change = async (input: HTMLInputElement, value: string) => {
    await act(async () => {
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, value);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
};
const submit = async () => {
    await act(async () => {
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
    });
};

describe('file save dialogs', () => {
    it.each(cases)(
        'preserves $action input on failure and dismisses only after a successful retry ($value)',
        async (test) => {
            let reject!: (error: Error) => void;
            let resolve!: () => void;
            const api = test.action === 'chmod' ? mocks.chmod : mocks.rename;
            api.mockReturnValueOnce(new Promise<void>((_resolve, fail) => (reject = fail))).mockReturnValueOnce(
                new Promise<void>((done) => (resolve = done)),
            );
            const dismissed = vi.fn();
            const store = createStore({ flashes });
            const render = async (visible = true) => {
                await act(async () =>
                    root.render(
                        <StoreProvider store={store}>
                            <section data-scope='page'>
                                <FlashMessageRender byKey='files' />
                            </section>
                            {test.action === 'chmod' ? (
                                <ChmodFileModal
                                    visible={visible}
                                    files={test.files.map((file) => ({ file, mode: '644' }))}
                                    onDismissed={dismissed}
                                />
                            ) : (
                                <RenameFileModal
                                    visible={visible}
                                    files={test.files}
                                    useMoveTerminology={test.action === 'move'}
                                    onDismissed={dismissed}
                                />
                            )}
                        </StoreProvider>,
                    ),
                );
            };
            await render();
            const input = container.querySelector<HTMLInputElement>(
                test.action === 'chmod' ? '#file_mode' : '#file_name',
            )!;
            await change(input, test.value);
            await submit();
            expect(api).toHaveBeenLastCalledWith('uuid', '/config', test.data);
            expect(dismissed).not.toHaveBeenCalled();
            const pendingBusy = container.querySelector('[role=dialog]')!.getAttribute('aria-busy');
            const pendingMutations = mocks.mutate.mock.calls.length;
            expect(mocks.select).not.toHaveBeenCalled();
            await act(async () => reject(new Error('Permission denied saving file')));
            expect(dismissed).not.toHaveBeenCalled();
            expect(pendingBusy).toBe('true');
            if (test.action !== 'chmod') expect(pendingMutations).toBe(0);
            expect(input.value).toBe(test.value);
            expect(container.querySelector('[role=dialog]')!.getAttribute('aria-busy')).toBe('false');
            expect(container.querySelector('[role=dialog] [role=alert]')?.textContent).toContain(
                'Permission denied saving file',
            );
            expect(container.querySelector('[data-scope=page]')!.textContent).toBe('');
            expect(mocks.select).not.toHaveBeenCalled();
            await act(async () =>
                store.getActions().flashes.addFlash({ key: 'unrelated', type: 'info', message: 'Keep me' }),
            );
            await submit();
            expect(api).toHaveBeenCalledTimes(2);
            expect(container.querySelector('[role=dialog] [role=alert]')).toBeNull();
            expect(dismissed).not.toHaveBeenCalled();
            await act(async () => resolve());
            expect(mocks.select).toHaveBeenCalledExactlyOnceWith([]);
            expect(dismissed).toHaveBeenCalledOnce();
            expect(mocks.mutate).toHaveBeenCalledWith();
            await render(false);
            await render();
            expect(container.querySelector('[role=dialog] [role=alert]')).toBeNull();
            expect(store.getState().flashes.items.map((flash) => flash.message)).toEqual(['Keep me']);
        },
    );
});
