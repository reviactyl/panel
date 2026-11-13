import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter, useLocation } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { rawDataToFileObject } from '@/api/transformers';
import type { FileObject } from '@/api/server/files/loadDirectory';
import FileObjectRow from './FileObjectRow';

const permissions = vi.hoisted(() => ({ read: true, contents: true }));
vi.mock('@/plugins/usePermissions', () => ({
    usePermissions: ([permission]: string[]) => [permission === 'file.read' ? permissions.read : permissions.contents],
}));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { id: 'test' } }, files: { directory: '/nested #folder' } }),
    },
}));
vi.mock('./FileDropdownMenu', () => ({ default: () => null }));
vi.mock('./SelectFileCheckbox', () => ({ default: () => null }));

let container: HTMLDivElement;
let root: Root;
const preview = vi.fn();
function Location() {
    const location = useLocation();
    return <output>{location.pathname + location.hash}</output>;
}
function entry(name: string, mimetype: string, isFile = true): FileObject {
    return rawDataToFileObject({
        object: 'file_object',
        attributes: {
            name,
            mimetype,
            is_file: isFile,
            is_symlink: false,
            mode: '-rw-r--r--',
            mode_bits: '0644',
            size: 1024,
            created_at: '2026-11-01T00:00:00Z',
            modified_at: '2026-11-01T00:00:00Z',
        },
    });
}
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    permissions.read = permissions.contents = true;
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});
async function render(file: FileObject, grid = false) {
    await act(async () => {
        root.render(
            <MemoryRouter initialEntries={['/server/test/files#/nested%20%23folder']}>
                <FileObjectRow file={file} grid={grid} onMediaClick={preview} />
                <Location />
            </MemoryRouter>,
        );
    });
}

describe.each([false, true])('file row media controls (grid=%s)', (grid) => {
    it.each([
        ['image.png', 'image/png'],
        ['video.mp4', 'video/mp4'],
    ])('provides a focusable native preview button for %s', async (name, mimetype) => {
        const file = entry(name, mimetype);
        await render(file, grid);
        const button = container.querySelector('button');
        expect(button).not.toBeNull();
        expect(button!.type).toBe('button');
        expect(button!.tabIndex).toBe(0);
        button!.focus();
        expect(document.activeElement).toBe(button);
        await act(async () => button!.click());
        expect(preview).toHaveBeenCalledExactlyOnceWith(file);
        expect(container.querySelector('output')!.textContent).toBe('/server/test/files#/nested%20%23folder');
    });
    it.each([
        ['config #1.txt', 'text/plain', true, '/edit#/nested%20%23folder/config%20%231.txt'],
        ['subdirectory', 'inode/directory', false, '#/nested%20%23folder/subdirectory'],
    ])('preserves the navigation link for %s', async (name, mimetype, isFile, suffix) => {
        await render(entry(name, mimetype, isFile), grid);
        const link = container.querySelector('a');
        expect(link?.getAttribute('href')).toBe(`/server/test/files${suffix}`);
        await act(async () => link!.click());
        expect(container.querySelector('output')!.textContent).toBe(`/server/test/files${suffix}`);
        expect(preview).not.toHaveBeenCalled();
    });
    it.each([
        ['image.png', 'image/png'],
        ['video.mp4', 'video/mp4'],
        ['config.txt', 'text/plain'],
    ])('does not expose %s contents without permission', async (name, mimetype) => {
        permissions.contents = false;
        await render(entry(name, mimetype), grid);
        expect(container.querySelector('a, button')).toBeNull();
        expect(preview).not.toHaveBeenCalled();
    });
    it('does not expose folder navigation without permission', async () => {
        permissions.read = false;
        await render(entry('subdirectory', 'inode/directory', false), grid);
        expect(container.querySelector('a, button')).toBeNull();
    });
});
