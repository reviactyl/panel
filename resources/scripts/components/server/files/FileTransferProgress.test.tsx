import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { fireEvent } from '@testing-library/dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import files from '@/state/server/files';
import FileManagerStatus from './FileManagerStatus';
import UrlDownloadButton from './UrlDownloadButton';

const mocks = vi.hoisted(() => ({ pull: vi.fn(), downloads: vi.fn(), mutate: vi.fn(), error: vi.fn() }));
vi.mock('@/api/server/files/pullFile', () => ({ default: mocks.pull }));
vi.mock('@/api/server/files/getFileDownloads', () => ({ default: mocks.downloads }));
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/plugins/useFlash', () => ({
    useFlashKey: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: mocks.error }),
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/state/server', async () => {
    const { useStoreState, useStoreActions } = await import('easy-peasy');
    return { ServerContext: { useStoreState, useStoreActions } };
});
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
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
        Dialog: Object.assign(
            ({ open, children }: { open: boolean; children: ReactNode }) => open && <div>{children}</div>,
            { Footer: ({ children }: { children: ReactNode }) => children },
        ),
        DialogWrapperContext: createContext({ close: vi.fn(), setProps: vi.fn() }),
    };
});

const makeStore = () => createStore({ files, server: { data: { uuid: 'server-uuid' } } });
let store: ReturnType<typeof makeStore>;
let container: HTMLDivElement;
let root: Root;
const rings = () => Array.from(container.querySelectorAll<SVGElement>('circle[stroke-dasharray]'));
const expectThemedRings = (count: number) => {
    expect(rings()).toHaveLength(count);
    rings().forEach((ring) => {
        expect(ring.getAttribute('stroke')).toBe('currentColor');
        expect(ring.previousElementSibling!.getAttribute('stroke')).toBe('currentColor');
        expect(ring.previousElementSibling!.classList.contains('opacity-25')).toBe(true);
    });
};

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    vi.clearAllMocks();
    store = makeStore();
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    document.documentElement.className = '';
    vi.useRealTimers();
});

describe.each(['light', 'dark'])('%s file transfer indicators', (theme) => {
    beforeEach(() => {
        document.documentElement.className = theme;
    });

    it('inherits the foreground for aggregate and per-file upload arcs as progress changes and cancellation works', async () => {
        const abort = new AbortController();
        store.getActions().files.pushFileUpload({
            name: 'upload',
            data: { name: 'test.txt', loaded: 25, total: 100, abort },
        });
        await act(async () =>
            root.render(
                <StoreProvider store={store}>
                    <FileManagerStatus />
                </StoreProvider>,
            ),
        );
        expectThemedRings(1);
        expect(Number(rings()[0]!.style.strokeDashoffset)).toBeCloseTo(0.75 * 28 * Math.PI);
        await act(async () => container.querySelector('button')!.click());
        expectThemedRings(2);
        await act(async () => store.getActions().files.setUploadProgress({ name: 'upload', loaded: 75 }));
        rings().forEach((ring) => expect(Number(ring.style.strokeDashoffset)).toBeCloseTo(0.25 * 28 * Math.PI));
        await act(async () => store.getActions().files.setUploadProgress({ name: 'upload', loaded: 100 }));
        rings().forEach((ring) => expect(Number(ring.style.strokeDashoffset)).toBe(0));
        await act(async () => container.querySelector('code')!.parentElement!.querySelector('button')!.click());
        expect(abort.signal.aborted).toBe(true);
        expectThemedRings(0);
    });

    it('shows an animated themed URL download arc until the download finishes and refreshes files', async () => {
        mocks.pull.mockResolvedValue('download-id');
        mocks.downloads.mockResolvedValueOnce([{ identifier: 'download-id' }]).mockResolvedValueOnce([]);
        await act(async () =>
            root.render(
                <StoreProvider store={store}>
                    <UrlDownloadButton />
                </StoreProvider>,
            ),
        );
        expectThemedRings(0);
        await act(async () => container.querySelector('button')!.click());
        await act(async () =>
            fireEvent.change(container.querySelector('#url')!, {
                target: { value: 'https://example.com/test.zip' },
            }),
        );
        const download = Array.from(container.querySelectorAll('button')).find(
            (button) => button.textContent === 'url-download.download',
        )!;
        await act(async () => download.click());
        expect(mocks.pull).toHaveBeenCalledWith('server-uuid', 'https://example.com/test.zip', '/');
        expectThemedRings(1);
        expect(rings()[0]!.classList.contains('animate-spin')).toBe(true);
        expect(Number(rings()[0]!.style.strokeDashoffset)).toBeCloseTo(0.25 * 28 * Math.PI);
        await act(async () => vi.advanceTimersByTime(2000));
        expectThemedRings(1);
        await act(async () => vi.advanceTimersByTime(2000));
        expectThemedRings(0);
        expect(mocks.mutate).toHaveBeenCalledOnce();
        expect(mocks.error).not.toHaveBeenCalled();
    });
});
