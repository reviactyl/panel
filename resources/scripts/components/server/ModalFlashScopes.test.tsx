import { act, type ButtonHTMLAttributes, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import FlashMessageRender from '@/components/FlashMessageRender';
import flashes from '@/state/flashes';
import NewDirectoryButton from './files/NewDirectoryButton';
import UrlDownloadButton from './files/UrlDownloadButton';
import MoveFileModal from './files/MoveFileModal';
import RenameFileModal from './files/RenameFileModal';
import ChmodFileModal from './files/ChmodFileModal';
import GSLTokenModalFeature from './features/GSLTokenModalFeature';
import HytaleOauthRequireFeature from './features/HytaleOauthRequireFeature';
import JavaVersionModalFeature from './features/JavaVersionModalFeature';
import PIDLimitModalFeature from './features/PIDLimitModalFeature';
import SteamDiskSpaceFeature from './features/SteamDiskSpaceFeature';
import EulaModalFeature from './features/eula/EulaModalFeature';

const mocks = vi.hoisted(() => {
    const listeners = new Set<(line: string) => void>();
    const state = {
        server: { data: { uuid: 'server-uuid' } },
        files: { directory: '/' },
        status: { value: 'offline' },
        socket: {
            connected: true,
            instance: {
                addListener: (_event: string, listener: (line: string) => void) => listeners.add(listener),
                removeListener: (_event: string, listener: (line: string) => void) => listeners.delete(listener),
            },
        },
    };
    return {
        listeners,
        state,
        mutate: vi.fn(),
        setProps: vi.fn(),
        close: vi.fn(),
        loadDirectory: vi.fn(async () => []),
        renameFiles: vi.fn(),
    };
});
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: typeof mocks.state) => unknown) => selector(mocks.state),
        useStoreActions: () => vi.fn(),
    },
}));
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/api/server/files/loadDirectory', () => ({ default: mocks.loadDirectory }));
vi.mock('@/api/server/files/renameFiles', () => ({ default: mocks.renameFiles }));
vi.mock('@/api/swr/getServerStartup', () => ({
    default: () => ({ data: { dockerImages: { Java: 'java:21' } }, mutate: mocks.mutate }),
}));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('react-i18next', () => {
    const t = (key: string) => key;
    return { useTranslation: () => ({ t }) };
});
vi.mock('@/reviactyl/elements/InputSpinner', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/components/button/index', () => {
    const Button = (props: ButtonHTMLAttributes<HTMLButtonElement>) => <button {...props} />;
    return { Button: Object.assign(Button, { Text: Button }) };
});
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({ visible, children }: { visible: boolean; children: ReactNode }) =>
        visible ? <div role='dialog'>{children}</div> : null,
}));
vi.mock('@/reviactyl/elements/dialog', async () => {
    const { createContext } = await import('react');
    const Dialog = ({ open, children }: { open: boolean; children: ReactNode }) =>
        open ? <div role='dialog'>{children}</div> : null;
    return {
        Dialog: Object.assign(Dialog, { Footer: ({ children }: { children: ReactNode }) => children }),
        DialogWrapperContext: createContext({ setProps: mocks.setProps, close: mocks.close }),
    };
});

const cases = [
    { scope: 'files:rename-modal', prompt: <RenameFileModal visible files={['example.txt']} onDismissed={() => {}} /> },
    {
        scope: 'files:chmod-modal',
        prompt: <ChmodFileModal visible files={[{ file: 'example.txt', mode: '644' }]} onDismissed={() => {}} />,
    },
    { scope: 'files:directory-modal', prompt: <NewDirectoryButton />, button: 'create-directory' },
    { scope: 'files:url-download-modal', prompt: <UrlDownloadButton />, button: 'url-download.button' },
    {
        scope: 'files:move-modal',
        prompt: <MoveFileModal visible files={['example.txt']} onDismissed={() => {}} />,
    },
    { scope: 'feature:gslToken', prompt: <GSLTokenModalFeature />, line: '(gsl token expired)' },
    {
        scope: 'feature:hytaleOauth',
        prompt: <HytaleOauthRequireFeature />,
        line: 'https://oauth.accounts.hytale.com/oauth2/device/verify?user_code=demo',
    },
    {
        scope: 'feature:javaVersion',
        prompt: <JavaVersionModalFeature />,
        line: 'java.lang.UnsupportedClassVersionError',
    },
    { scope: 'feature:pidLimit', prompt: <PIDLimitModalFeature />, line: 'pthread_create failed' },
    { scope: 'feature:steamDiskSpace', prompt: <SteamDiskSpaceFeature />, line: '0x202 after update job' },
    {
        scope: 'feature:eula',
        prompt: <EulaModalFeature />,
        line: 'You need to agree to the EULA in order to run the server',
    },
];

let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.listeners.clear();
    mocks.loadDirectory.mockReset().mockResolvedValue([]);
    mocks.renameFiles.mockReset();
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

describe('modal flash scopes', () => {
    it.each(cases)('$scope displays only its own messages without moving or clearing other scopes', async (test) => {
        const store = createStore({ flashes, user: { data: { rootAdmin: true } } });
        await act(async () =>
            root.render(
                <StoreProvider store={store}>
                    <section data-scope='files'>
                        <FlashMessageRender byKey='files' />
                    </section>
                    {test.prompt}
                </StoreProvider>,
            ),
        );
        await act(async () => {
            if (test.button) {
                container.querySelector<HTMLButtonElement>(`[aria-label="${test.button}"]`)!.click();
            }
            if (test.line) mocks.listeners.forEach((listener) => listener(test.line!));
        });
        const dialog = container.querySelector('[role="dialog"]');
        expect(dialog).not.toBeNull();

        await act(async () => {
            const { addFlash } = store.getActions().flashes;
            addFlash({ key: 'files', type: 'error', message: 'File page message' });
            addFlash({ type: 'info', message: 'Global message' });
            for (const { scope } of cases.filter(({ scope }) => scope !== test.scope)) {
                addFlash({ key: scope, type: 'warning', message: `Other prompt: ${scope}` });
            }
        });
        expect(dialog!.querySelectorAll('[role="alert"]')).toHaveLength(0);

        await act(async () => {
            store.getActions().flashes.addFlash({ key: test.scope, type: 'error', message: 'Own dialog message' });
        });
        expect(Array.from(dialog!.querySelectorAll('[role="alert"]'), (alert) => alert.textContent)).toEqual([
            'Own dialog message',
        ]);
        expect(container.querySelector('[data-scope="files"]')!.textContent).toBe('File page message');
        expect(store.getState().flashes.items).toHaveLength(cases.length + 2);
        expect(store.getState().flashes.items.find((flash) => !flash.key)?.message).toBe('Global message');
        expect(
            store.getState().flashes.items.filter((flash) => flash.message.startsWith('Other prompt:')),
        ).toHaveLength(cases.length - 1);
    });

    it('renders failed MoveFileModal move errors inside the dialog rather than the page scope', async () => {
        let rejectRename!: (error: Error) => void;
        mocks.loadDirectory.mockResolvedValue([{ name: 'target', key: 'target', isFile: false } as never]);
        mocks.renameFiles.mockReturnValueOnce(
            new Promise<void>((_resolve, reject) => {
                rejectRename = reject;
            }),
        );
        const onDismissed = vi.fn();
        const store = createStore({ flashes, user: { data: { rootAdmin: true } } });
        store.getActions().flashes.addFlash({ key: 'files', type: 'warning', message: 'Existing page flash' });
        store.getActions().flashes.addFlash({ key: 'files:move-modal', type: 'error', message: 'Stale modal error' });

        await act(async () =>
            root.render(
                <StoreProvider store={store}>
                    <section data-scope='files'>
                        <FlashMessageRender byKey='files' />
                    </section>
                    <MoveFileModal visible files={['example.txt']} onDismissed={onDismissed} />
                </StoreProvider>,
            ),
        );

        const clickButton = async (label: string) => {
            const target = Array.from(container.querySelectorAll('button')).find((el) => el.textContent === label);
            expect(target).toBeDefined();
            await act(async () => target!.click());
        };

        await clickButton('target');
        await act(async () => {
            store
                .getActions()
                .flashes.addFlash({ key: 'files:move-modal', type: 'error', message: 'Previous move error' });
        });
        await clickButton('move');

        const dialog = container.querySelector('[role="dialog"]');
        expect(dialog).not.toBeNull();
        expect(dialog!.querySelectorAll('[role="alert"]')).toHaveLength(0);
        expect(container.querySelector('[data-scope="files"]')!.textContent).toBe('Existing page flash');

        await act(async () => rejectRename(new Error('Permission denied moving file')));

        expect(onDismissed).not.toHaveBeenCalled();
        expect(Array.from(dialog!.querySelectorAll('[role="alert"]'), (alert) => alert.textContent)).toEqual([
            'ErrorPermission denied moving file',
        ]);
        expect(container.querySelector('[data-scope="files"]')!.textContent).toBe('');
    });
});
