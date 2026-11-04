import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import type { AxiosProgressEvent } from 'axios';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import files from '@/state/server/files';
import UploadButton from './UploadButton';
import FileManagerStatus from './FileManagerStatus';

const mocks = vi.hoisted(() => ({ post: vi.fn(), error: vi.fn(), mutate: vi.fn() }));
vi.mock('axios', () => ({ default: { post: mocks.post, isCancel: (error: Error) => error.name === 'CanceledError' } }));
vi.mock('@/i18n', () => ({ default: { t: (key: string) => key } }));
vi.mock('@/api/http', () => ({ default: {} }));
vi.mock('@/api/server/files/getFileUploadUrl', () => ({ default: async () => 'https://agent.test/upload' }));
vi.mock('@/state/server', async () => {
    const { useStoreState, useStoreActions } = await import('easy-peasy');
    return { ServerContext: { useStoreState, useStoreActions } };
});
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/plugins/useFlash', () => ({
    useFlashKey: () => ({ addError: mocks.error, clearAndAddHttpError: mocks.error }),
}));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/Portal', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/hoc/asDialog', () => ({
    default:
        () =>
        (Component: () => ReactNode) =>
        ({ open }: { open: boolean }) =>
            open && <Component />,
}));
vi.mock('@/reviactyl/elements/dialog', async () => {
    const { createContext } = await import('react');
    return {
        Dialog: { Footer: ({ children }: { children: ReactNode }) => children },
        DialogWrapperContext: createContext({ close: vi.fn(), setProps: vi.fn() }),
    };
});

interface Request {
    directory: string;
    signal: AbortSignal;
    progress: (event: AxiosProgressEvent) => void;
    resolve: () => void;
    reject: (error: Error) => void;
}
const makeStore = () => createStore({ files, server: { data: { uuid: 'server-uuid' } } });
let store: ReturnType<typeof makeStore>;
let container: HTMLDivElement;
let root: Root;
let requests: Request[];

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    vi.clearAllMocks();
    store = makeStore();
    requests = [];
    mocks.post.mockImplementation(
        (_url, _body, options) =>
            new Promise<void>((resolve, reject) => {
                requests.push({
                    directory: options.params.directory,
                    signal: options.signal,
                    progress: options.onUploadProgress,
                    resolve,
                    reject,
                });
                options.signal.addEventListener('abort', () => {
                    const error = new Error('Canceled');
                    error.name = 'CanceledError';
                    reject(error);
                });
            }),
    );
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
    await act(async () =>
        root.render(
            <StoreProvider store={store}>
                <UploadButton />
                <FileManagerStatus />
            </StoreProvider>,
        ),
    );
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.useRealTimers();
});
const upload = async (directory: string, folder = false, filenames = ['config.yml']) => {
    await act(async () => store.getActions().files.setDirectory(directory));
    const files = filenames.map((name) => {
        const file = new File(['configuration'], name);
        if (folder) Object.defineProperty(file, 'webkitRelativePath', { value: `Plugin/${name}` });
        return file;
    });
    const input = container.querySelectorAll<HTMLInputElement>('input[type="file"]')[folder ? 1 : 0]!;
    Object.defineProperty(input, 'files', { configurable: true, value: files });
    await act(async () => input.dispatchEvent(new Event('change', { bubbles: true })));
};
const openUploads = async () => {
    await act(async () => container.querySelector<HTMLButtonElement>('button:not([aria-label])')!.click());
};
const rows = () => Array.from(container.querySelectorAll('code')).map((code) => code.parentElement!);
const cancel = async (index: number) => {
    await act(async () => rows()[index]!.querySelector('button')!.click());
};

describe('overlapping upload identities', () => {
    it('keeps identical filenames in different directories independently visible and cancellable', async () => {
        await upload('/plugins/One');
        await upload('/plugins/Two');
        expect(requests.map(({ directory }) => directory)).toEqual(['/plugins/One', '/plugins/Two']);
        await openUploads();
        expect(rows()).toHaveLength(2);
        await act(async () => requests[0]!.progress({ loaded: 3 } as AxiosProgressEvent));
        expect(Object.values(store.getState().files.uploads).map(({ loaded }) => loaded)).toEqual([3, 0]);
        await cancel(0);
        expect(requests[0]!.signal.aborted).toBe(true);
        expect(requests[1]!.signal.aborted).toBe(false);
        expect(rows()).toHaveLength(1);
        expect(rows()[0]!.textContent).toContain('/plugins/Two/config.yml');
    });

    it.each([false, true])(
        'does not remove or update another request when one finishes (folder: %s)',
        async (folder) => {
            await upload('/plugins/One', folder);
            await upload('/plugins/Two', folder);
            await openUploads();
            await act(async () => requests[0]!.resolve());
            await act(async () => vi.advanceTimersByTime(500));
            expect(rows()).toHaveLength(1);
            expect(rows()[0]!.textContent).toContain(folder ? '/plugins/Two/Plugin' : '/plugins/Two/config.yml');
            expect(Object.values(store.getState().files.uploads)[0]!.loaded).toBe(0);
            await cancel(0);
            expect(requests[1]!.signal.aborted).toBe(true);
        },
    );

    it('removes only the failed request when another upload has the same filename', async () => {
        await upload('/plugins/One');
        await upload('/plugins/Two');
        await openUploads();
        const error = new Error('Upload failed');
        await act(async () => requests[0]!.reject(error));
        expect(rows()).toHaveLength(1);
        expect(rows()[0]!.textContent).toContain('/plugins/Two/config.yml');
        expect(requests[1]!.signal.aborted).toBe(false);
        expect(mocks.error).toHaveBeenCalledWith(error);
    });

    it('also separates overlapping requests to the same destination', async () => {
        await upload('/plugins/One');
        await upload('/plugins/One');
        await openUploads();
        expect(rows()).toHaveLength(2);
        await cancel(1);
        expect(requests[0]!.signal.aborted).toBe(false);
        expect(requests[1]!.signal.aborted).toBe(true);
        expect(rows()).toHaveLength(1);
    });

    it.each(['/plugins/One', '/plugins/Two'])(
        'keeps repeated folder groups independently cancellable in %s',
        async (destination) => {
            await upload('/plugins/One', true);
            await upload(destination, true);
            await openUploads();
            expect(rows()).toHaveLength(2);
            await cancel(0);
            expect(requests[0]!.signal.aborted).toBe(true);
            expect(requests[1]!.signal.aborted).toBe(false);
            expect(rows()[0]!.textContent).toContain(`${destination}/Plugin`);
        },
    );

    it('aggregates all files in each folder and cancels only the selected folder submission', async () => {
        await upload('/plugins/One', true, ['config.yml', 'settings.yml']);
        await upload('/plugins/Two', true, ['config.yml', 'settings.yml']);
        expect(requests.map(({ directory }) => directory)).toEqual([
            '/plugins/One/Plugin',
            '/plugins/One/Plugin',
            '/plugins/Two/Plugin',
            '/plugins/Two/Plugin',
        ]);
        await openUploads();
        expect(rows()).toHaveLength(2);
        expect(rows().map((row) => row.querySelector('code')!.textContent)).toEqual([
            '/plugins/One/Plugin',
            '/plugins/Two/Plugin',
        ]);
        await act(async () => {
            [3, 10, 2, 4].forEach((loaded, index) => requests[index]!.progress({ loaded } as AxiosProgressEvent));
        });
        const progressOffset = (row: HTMLElement) =>
            Number.parseFloat(row.querySelectorAll('circle')[1]!.style.strokeDashoffset);
        expect(progressOffset(rows()[0]!)).toBeCloseTo(14 * Math.PI);
        expect(progressOffset(rows()[1]!)).toBeCloseTo((1 - 6 / 26) * 28 * Math.PI);
        await cancel(0);
        expect(requests.map(({ signal }) => signal.aborted)).toEqual([true, true, false, false]);
        expect(rows()).toHaveLength(1);
        expect(rows()[0]!.textContent).toContain('/plugins/Two/Plugin');
        expect(progressOffset(rows()[0]!)).toBeCloseTo((1 - 6 / 26) * 28 * Math.PI);
    });
});
