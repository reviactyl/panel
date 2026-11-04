import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter, useLocation } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { rawDataToFileObject } from '@/api/transformers';
import RecursiveFileRow, { SearchResult } from './RecursiveFileRow';

const permissions = vi.hoisted(() => ({ read: true, contents: true }));
vi.mock('@/plugins/usePermissions', () => ({
    usePermissions: ([permission]: string[]) => [permission === 'file.read' ? permissions.read : permissions.contents],
}));

let container: HTMLDivElement;
let root: Root;
const preview = vi.fn();
const navigate = vi.fn();
function Location() {
    const location = useLocation();
    return <output>{location.pathname + location.hash}</output>;
}
function entry(name: string, mimetype: string, isFile = true): SearchResult {
    return {
        ...rawDataToFileObject({
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
        }),
        fullPath: `/nested #folder/${name}`,
    };
}
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    permissions.read = permissions.contents = true;
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
});
async function render(file: SearchResult, initial = '/server/test/files#/elsewhere') {
    await act(async () => {
        root.render(
            <MemoryRouter initialEntries={[initial]}>
                <RecursiveFileRow file={file} serverId='test' onMediaClick={preview} onNavigate={navigate} />
                <Location />
            </MemoryRouter>,
        );
    });
}
async function click() {
    const target = container.querySelector<HTMLElement>('button, a, span');
    expect(target).not.toBeNull();
    await act(async () => target!.click());
}

describe('recursive file result actions', () => {
    it.each([
        ['image.png', 'image/png'],
        ['vector.svg', 'image/svg+xml'],
        ['video.mp4', 'video/mp4'],
    ])('previews %s using its full path without navigating', async (name, mimetype) => {
        const file = entry(name, mimetype);
        await render(file);
        await click();
        expect(preview).toHaveBeenCalledExactlyOnceWith(file, file.fullPath);
        expect(container.querySelector('output')!.textContent).toBe('/server/test/files#/elsewhere');
        expect(navigate).not.toHaveBeenCalled();
    });
    it.each([
        ['world.zip', 'application/zip'],
        ['data.bin', 'application/octet-stream'],
        ['server.jar', 'application/jar'],
    ])('opens the containing folder for %s instead of the editor', async (name, mimetype) => {
        await render(entry(name, mimetype));
        await click();
        expect(container.querySelector('output')!.textContent).toBe('/server/test/files#/nested%20%23folder');
        expect(preview).not.toHaveBeenCalled();
        expect(navigate).toHaveBeenCalledOnce();
    });
    it('clears search when an archive is already in the current directory', async () => {
        const file = { ...entry('world.zip', 'application/zip'), fullPath: '/world.zip' };
        await render(file, '/server/test/files#/');
        await click();
        expect(container.querySelector('output')!.textContent).toBe('/server/test/files#/');
        expect(navigate).toHaveBeenCalledOnce();
    });
    it.each([
        ['config #1.txt', 'text/plain', true, '/edit#/nested%20%23folder/config%20%231.txt'],
        ['subdirectory', 'inode/directory', false, '#/nested%20%23folder/subdirectory'],
    ])('preserves navigation for %s', async (name, mimetype, isFile, suffix) => {
        await render(entry(name, mimetype, isFile));
        await click();
        expect(container.querySelector('output')!.textContent).toBe(`/server/test/files${suffix}`);
        expect(preview).not.toHaveBeenCalled();
        expect(navigate).toHaveBeenCalledOnce();
    });
    it('does not open text contents without file.read-content', async () => {
        permissions.contents = false;
        await render(entry('config.txt', 'text/plain'));
        await click();
        expect(container.querySelector('a, button')).toBeNull();
        expect(preview).not.toHaveBeenCalled();
        expect(navigate).not.toHaveBeenCalled();
    });
    it('does not preview media without file.read-content', async () => {
        permissions.contents = false;
        await render(entry('image.png', 'image/png'));
        await click();
        expect(preview).not.toHaveBeenCalled();
        expect(container.querySelector('output')!.textContent).toBe('/server/test/files#/nested%20%23folder');
    });
    it.each([
        ['world.zip', 'application/zip', true],
        ['subdirectory', 'inode/directory', false],
    ])('does not navigate %s without file.read', async (name, mimetype, isFile) => {
        permissions.read = false;
        await render(entry(name, mimetype, isFile));
        await click();
        expect(container.querySelector('a, button')).toBeNull();
        expect(navigate).not.toHaveBeenCalled();
    });
});
