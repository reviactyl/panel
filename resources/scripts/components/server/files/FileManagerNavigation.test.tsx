import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ServerContext } from '@/state/server';
import type { FileObject } from '@/api/server/files/loadDirectory';
import FileManagerContainer from './FileManagerContainer';
import { createMemoryRouter, RouterProvider } from 'react-router-dom';

const mocks = vi.hoisted(() => ({
    post: vi.fn(),
    uploadUrl: vi.fn(),
    files: [] as FileObject[],
    mutate: vi.fn(),
}));
vi.mock('@/state/server', async () => {
    const { createContextStore } = await import('easy-peasy');
    const { default: files } = await import('@/state/server/files');
    return { ServerContext: createContextStore({ server: { data: { id: 'server', uuid: 'uuid' } }, files }) };
});
vi.mock('@/state/hooks', () => ({
    useStoreState: (selector: (state: unknown) => unknown) => selector({ designify: { data: { fileLayout: 'list' } } }),
    useStoreActions: () => vi.fn(),
}));
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ data: mocks.files, mutate: mocks.mutate }) }));
vi.mock('@/i18n', () => ({ default: { t: (key: string) => key } }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));

vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/ErrorBoundary', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/ui/Card', () => ({ default: ({ children }: { children: ReactNode }) => <div>{children}</div> }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('./MassActionsBar', () => ({ default: () => null }));
vi.mock('./FileObjectRow', () => ({ default: () => null }));
vi.mock('./FileManagerBreadcrumbs', () => ({ default: ({ renderLeft }: { renderLeft: ReactNode }) => renderLeft }));
vi.mock('./NewDirectoryButton', () => ({ default: () => null }));
vi.mock('./UrlDownloadButton', () => ({ default: () => null }));
vi.mock('./FileManagerStatus', () => ({ default: () => null }));
vi.mock('axios', async (original) => ({
    default: { ...(await original<typeof import('axios')>()).default, post: mocks.post },
}));
vi.mock('@/api/server/files/getFileUploadUrl', () => ({ default: mocks.uploadUrl }));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/plugins/useFlash', () => ({
    useFlashKey: () => ({ addError: vi.fn(), clearAndAddHttpError: vi.fn() }),
    default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }),
}));
vi.mock('@/reviactyl/elements/Portal', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('./ImageViewerModal', () => ({ default: () => null }));
vi.mock('./VideoViewerModal', () => ({ default: () => null }));

let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof ServerContext.useStore>;
let router: ReturnType<typeof createMemoryRouter>;
const StoreProbe = () => {
    store = ServerContext.useStore();
    return null;
};

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.files = [];
    mocks.uploadUrl.mockResolvedValue('https://agent.test/upload');
    mocks.mutate.mockResolvedValue(undefined);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    router.dispose();
    container.remove();
    vi.useRealTimers();
});

describe('New File during an upload', () => {
    it.each(['', '#/world/config%20files'])('keeps the upload alive through editor navigation (%s)', async (hash) => {
        let complete!: () => void;
        mocks.post.mockImplementation(() => new Promise<void>((resolve) => (complete = resolve)));
        router = createMemoryRouter(
            [
                { path: '/server/server/files', element: <FileManagerContainer /> },
                { path: '/server/server/files/new', element: <div>New file editor</div> },
            ],
            { initialEntries: [`/server/server/files${hash}`] },
        );
        await act(async () =>
            root.render(
                <ServerContext.Provider>
                    <StoreProbe />
                    <RouterProvider router={router} />
                </ServerContext.Provider>,
            ),
        );
        const file = new File(['world data'], 'world.zip');
        const input = container.querySelector<HTMLInputElement>('input[type="file"]')!;
        await act(async () => {
            Object.defineProperty(input, 'files', { value: [file] });
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        expect(mocks.post).toHaveBeenCalledTimes(1);
        const options = mocks.post.mock.calls[0]![2];
        expect(options.params.directory).toBe(hash ? '/world/config files' : '/');
        await act(async () => options.onUploadProgress({ loaded: 3 }));
        const upload = Object.values(store.getState().files.uploads)[0]!;
        expect(upload.loaded).toBe(3);
        const documentUrl = window.location.href;
        await act(async () => container.querySelector<HTMLButtonElement>('button[aria-label="new-file"]')!.click());
        expect(router.state.location.pathname).toBe('/server/server/files/new');
        expect(router.state.location.hash).toBe(hash);
        expect(window.location.href).toBe(documentUrl);
        expect(container.textContent).toContain('New file editor');
        expect(options.signal.aborted).toBe(false);
        await act(async () => options.onUploadProgress({ loaded: 7 }));
        await act(async () => router.navigate(-1));
        expect(Object.values(store.getState().files.uploads)[0]!.loaded).toBe(7);
        expect(options.signal.aborted).toBe(false);
        vi.useFakeTimers();
        await act(async () => complete());
        expect(Object.values(store.getState().files.uploads)[0]!.loaded).toBe(file.size);
        expect(mocks.mutate).toHaveBeenCalled();
        await act(async () => vi.advanceTimersByTime(500));
        expect(store.getState().files.uploads).toEqual({});
    });
});
