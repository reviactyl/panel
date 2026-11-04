import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ServerContext } from '@/state/server';
import UploadButton from './UploadButton';

const mocks = vi.hoisted(() => ({ post: vi.fn(), mutate: vi.fn(), error: vi.fn() }));
vi.mock('axios', () => ({ default: { post: mocks.post, isCancel: () => false } }));
vi.mock('@/api/server/files/getFileUploadUrl', () => ({ default: async () => 'https://agent.test/upload' }));
vi.mock('@/state/server', async () => {
    const { createContextStore } = await import('easy-peasy');
    const { default: files } = await import('@/state/server/files');
    return { ServerContext: createContextStore({ server: { data: { uuid: 'uuid' } }, files }) };
});
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/plugins/useFlash', () => ({
    useFlashKey: () => ({ addError: mocks.error, clearAndAddHttpError: mocks.error }),
}));
vi.mock('@/plugins/useFilePicker', () => ({ default: () => ({ open: vi.fn() }) }));
vi.mock('@/api/http', () => ({ default: {} }));
vi.mock('@/i18n', () => ({ default: { t: (key: string) => key } }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/Modal', () => ({ ModalMask: () => null }));
vi.mock('@/reviactyl/elements/Portal', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));

let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof ServerContext.useStore>;

const StoreObserver = () => {
    store = ServerContext.useStore();
    return null;
};
const render = async (show: boolean) => {
    await act(async () =>
        root.render(
            <ServerContext.Provider>
                <StoreObserver />
                {show && <UploadButton />}
            </ServerContext.Provider>,
        ),
    );
};
const submit = async (folder = false) => {
    const file = new File(['contents'], 'config.yml');
    if (folder) Object.defineProperty(file, 'webkitRelativePath', { value: 'settings/config.yml' });
    const input = container.querySelectorAll<HTMLInputElement>('input[type="file"]')[folder ? 1 : 0]!;
    Object.defineProperty(input, 'files', { configurable: true, value: [file] });
    await act(async () => input.dispatchEvent(new Event('change', { bubbles: true })));
};
const uploads = () => Object.values(store.getState().files.uploads);

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    vi.useFakeTimers();
    mocks.post.mockResolvedValue({});
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.useRealTimers();
});

describe('upload completion across Files navigation', () => {
    it.each([false, true])('removes a completed upload after unmount during the delay (folder: %s)', async (folder) => {
        await render(true);
        await submit(folder);
        expect(uploads()).toHaveLength(1);
        expect(uploads()[0]!.loaded).toBe(uploads()[0]!.total);
        await act(async () => vi.advanceTimersByTime(100));
        await render(false);
        await act(async () => vi.advanceTimersByTime(600));
        await render(true);
        expect(uploads()).toHaveLength(0);
        expect(mocks.post).toHaveBeenCalledTimes(1);
    });

    it('preserves an active transfer and its cancellation controller across unmount and remount', async () => {
        mocks.post.mockReturnValue(new Promise(() => {}));
        await render(true);
        await submit();
        const signal = mocks.post.mock.calls[0]![2].signal as AbortSignal;
        const key = Object.keys(store.getState().files.uploads)[0]!;
        await render(false);
        await act(async () => vi.advanceTimersByTime(700));
        await render(true);
        expect(uploads()).toHaveLength(1);
        expect(signal.aborted).toBe(false);
        await act(async () => store.getActions().files.cancelFileUpload(key));
        expect(signal.aborted).toBe(true);
        expect(uploads()).toHaveLength(0);
    });

    it('cleans up a transfer that finishes while Files is unmounted', async () => {
        let complete!: (value: unknown) => void;
        mocks.post.mockReturnValue(new Promise((resolve) => (complete = resolve)));
        await render(true);
        await submit();
        await render(false);
        await act(async () => complete({}));
        expect(uploads()[0]!.loaded).toBe(uploads()[0]!.total);
        await act(async () => vi.advanceTimersByTime(500));
        await render(true);
        expect(uploads()).toHaveLength(0);
    });
});
