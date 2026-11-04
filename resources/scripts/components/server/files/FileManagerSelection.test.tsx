import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ServerContext } from '@/state/server';
import type { FileObject } from '@/api/server/files/loadDirectory';
import SelectFileCheckbox from './SelectFileCheckbox';
import FileManagerContainer from './FileManagerContainer';

const mocks = vi.hoisted(() => ({
    files: [] as FileObject[],
    grid: false,
    compress: vi.fn(),
    remove: vi.fn(),
    mutate: vi.fn(),
    load: vi.fn(),
}));
vi.mock('@/state/server', async () => {
    const { createContextStore } = await import('easy-peasy');
    const { default: files } = await import('@/state/server/files');
    return { ServerContext: createContextStore({ server: { data: { id: 'server', uuid: 'uuid' } }, files }) };
});
vi.mock('@/state/hooks', () => ({
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({ designify: { data: { fileLayout: mocks.grid ? 'grid' : 'list' } } }),
    useStoreActions: () => vi.fn(),
}));
vi.mock('easy-peasy', async (original) => ({
    ...(await original<typeof import('easy-peasy')>()),
    useStoreState: () => 'tar.gz',
}));
vi.mock('@/plugins/useFileManagerSwr', () => ({ default: () => ({ data: mocks.files, mutate: mocks.mutate }) }));
vi.mock('@/plugins/useFlash', () => ({ default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }) }));
vi.mock('@/api/server/files/compressFiles', () => ({ default: mocks.compress }));
vi.mock('@/api/server/files/deleteFiles', () => ({ default: mocks.remove }));
vi.mock('@/api/server/files/loadDirectory', () => ({ default: mocks.load }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('react-router-dom', () => ({ useLocation: () => ({ hash: '' }), NavLink: () => null }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/ErrorBoundary', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/ui/Card', () => ({ default: ({ children }: { children: ReactNode }) => <div>{children}</div> }));
vi.mock('@/reviactyl/elements/tooltip/Tooltip', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/dialog', () => ({
    Dialog: {
        Confirm: ({ open, onConfirmed }: { open: boolean; onConfirmed: () => void }) =>
            open ? <button onClick={onConfirmed}>confirm delete</button> : null,
    },
}));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('./FileObjectRow', () => ({
    default: ({ file }: { file: FileObject }) => <SelectFileCheckbox name={file.name} />,
}));
vi.mock('./FileManagerBreadcrumbs', () => ({ default: ({ renderLeft }: { renderLeft: ReactNode }) => renderLeft }));
vi.mock('./NewDirectoryButton', () => ({ default: () => null }));
vi.mock('./UrlDownloadButton', () => ({ default: () => null }));
vi.mock('./FileManagerStatus', () => ({ default: () => null }));
vi.mock('./UploadButton', () => ({ default: () => null }));
vi.mock('./ImageViewerModal', () => ({ default: () => null }));
vi.mock('./VideoViewerModal', () => ({ default: () => null }));
vi.mock('./MoveFileModal', () => ({ default: () => null }));

const entries = (count: number) =>
    Array.from({ length: count }, (_, index) => ({
        name: `file-${index.toString().padStart(3, '0')}.txt`,
        key: `${index}`,
        isFile: true,
        size: count - index,
        modifiedAt: new Date(),
    })) as FileObject[];
let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.compress.mockResolvedValue(undefined);
    mocks.remove.mockResolvedValue(undefined);
    mocks.mutate.mockResolvedValue(undefined);
    mocks.load.mockResolvedValue([]);
    mocks.files = entries(251);
    mocks.grid = false;
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.useRealTimers();
});
const render = async () => {
    await act(async () =>
        root.render(
            <ServerContext.Provider>
                <FileManagerContainer />
            </ServerContext.Provider>,
        ),
    );
};
const selectAll = () => container.querySelector<HTMLInputElement>('input[type="checkbox"]')!;
const rows = () => Array.from(container.querySelectorAll<HTMLInputElement>('input[name="selectedFiles"]'));
const click = async (element: HTMLElement) => {
    await act(async () => element.click());
};
const action = (label: string) => container.querySelector<HTMLButtonElement>(`button[aria-label="${label}"]`)!;

describe('visible file bulk selection', () => {
    it('renders one named select-all control without hiding it at a breakpoint', async () => {
        await render();
        expect(selectAll().className).not.toMatch(/(?:^|\s)(?:\S+:)?hidden(?:\s|$)/);
        const controls = container.querySelectorAll('input[aria-label="strings:select_all"]');
        expect(controls).toHaveLength(1);
        expect(selectAll()).toBe(controls[0]);
    });

    it.each([
        { count: 2, grid: false },
        { count: 250, grid: false },
        { count: 251, grid: false },
        { count: 251, grid: true },
    ])('archives only displayed rows ($count entries, grid: $grid)', async ({ count, grid }) => {
        mocks.grid = grid;
        mocks.files = entries(count);
        await render();
        const visible = rows().map((row) => row.value);
        expect(visible).toHaveLength(Math.min(count, 250));
        await click(selectAll());
        expect(selectAll().checked).toBe(true);
        expect(rows().every((row) => row.checked)).toBe(true);
        await click(action('archive-as'));
        expect(mocks.compress).toHaveBeenCalledExactlyOnceWith('uuid', '/', visible, 'tar.gz');
        if (count > 250) expect(mocks.compress.mock.calls[0]![2]).not.toContain('file-000.txt');
    });

    it('allows deselecting every selected row and uses the remaining selection for delete', async () => {
        await render();
        await click(selectAll());
        const omitted = rows()[0]!;
        await click(omitted);
        expect(selectAll().checked).toBe(false);
        const visible = rows()
            .filter((row) => row.checked)
            .map((row) => row.value);
        expect(visible).toHaveLength(249);
        await click(action('delete'));
        await click(
            Array.from(container.querySelectorAll('button')).find((button) => button.textContent === 'confirm delete')!,
        );
        expect(mocks.remove).toHaveBeenCalledExactlyOnceWith('uuid', '/', visible);
        expect(visible).not.toContain(omitted.value);
        expect(visible).not.toContain('file-000.txt');
    });

    it('clears all selections when toggled off', async () => {
        await render();
        await click(selectAll());
        await click(selectAll());
        expect(rows().some((row) => row.checked)).toBe(false);
        expect(action('delete')).toBeNull();
    });

    it('removes selections that move beyond the display limit after a refresh', async () => {
        await render();
        await click(selectAll());
        const hidden = mocks.files.find((file) => file.name === rows().at(-1)!.value)!;
        const remaining = rows()
            .map((row) => row.value)
            .filter((name) => name !== hidden.name);
        mocks.files = [{ ...hidden, key: 'new-file', name: 'new-file.txt', size: 0 }, ...[...mocks.files].reverse()];
        expect(mocks.files[250]).toBe(hidden);
        await render();
        expect(rows().map((row) => row.value)).not.toContain(hidden.name);
        expect(
            rows()
                .filter((row) => row.checked)
                .map((row) => row.value),
        ).toEqual(remaining);
        await click(action('archive-as'));
        expect(mocks.compress).toHaveBeenCalledExactlyOnceWith('uuid', '/', remaining, 'tar.gz');
        expect(mocks.compress.mock.calls[0]![2]).not.toContain(hidden.name);
    });

    it('disables select-all for an empty directory', async () => {
        mocks.files = [];
        await render();
        expect(selectAll().checked).toBe(false);
        expect(selectAll().disabled).toBe(true);
    });

    it('clears selection while recursive search replaces selectable rows', async () => {
        await render();
        await click(selectAll());
        vi.useFakeTimers();
        const input = container.querySelector<HTMLInputElement>('input[type="text"]')!;
        await act(async () => {
            Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, 'match');
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
        await act(async () => vi.advanceTimersByTime(300));
        expect(rows()).toHaveLength(0);
        expect(selectAll().disabled).toBe(true);
        expect(action('delete')).toBeNull();
    });
});
