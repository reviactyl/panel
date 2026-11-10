import { MemoryRouter } from 'react-router-dom';
import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { getByRole, getAllByRole } from '@testing-library/dom';
import { beforeEach, afterEach, describe, expect, it, vi } from 'vitest';
import RecycleBin from './RecycleBin';

const mocks = vi.hoisted(() => ({
    post: vi.fn(),
    mutate: vi.fn(),
    conflict: false,
    nested: false,
    load: vi.fn(),
    navigate: vi.fn(),
}));
vi.mock('@/state/hooks', () => ({ useStoreState: () => 31 }));
vi.mock('@/api/server/files/loadDirectory', () => ({ default: mocks.load }));
vi.mock('react-router-dom', async (importOriginal) => ({
    ...(await importOriginal<typeof import('react-router-dom')>()),
    useNavigate: () => mocks.navigate,
}));
vi.mock('@/api/http', () => ({ default: { post: mocks.post }, httpErrorToHuman: () => 'Failed' }));
vi.mock('swr', () => ({
    default: (key: unknown) =>
        Array.isArray(key)
            ? {
                  data: [{ name: 'nested.txt', isFile: true, size: 3, modifiedAt: new Date('2026-11-10T12:00:00Z') }],
                  mutate: mocks.mutate,
              }
            : !key
              ? {}
              : {
                    data: {
                        size: 3,
                        data: [
                            {
                                id: 'a',
                                original_path: mocks.nested ? '/world' : '/world/one.txt',
                                created_at: '2026-11-10T12:00:00Z',
                                size: 1,
                                is_file: true,
                            },
                            {
                                id: 'b',
                                original_path: '/world/two.txt',
                                created_at: '2026-11-10T12:00:00Z',
                                size: 2,
                                is_file: true,
                            },
                        ],
                    },
                    mutate: mocks.mutate,
                },
}));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({
                server: { data: { uuid: 'server', id: 'server' }, permissions: ['*'] },
                files: { directory: '/.recycle-bin' },
            }),
    },
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/dialog', () => ({
    Dialog: Object.assign(({ children }: { children: ReactNode }) => <div role='dialog'>{children}</div>, {
        Footer: ({ children }: { children: ReactNode }) => <div>{children}</div>,
    }),
}));
vi.mock('@/reviactyl/elements/DropdownMenu', () => ({ default: () => null }));
let container: HTMLDivElement;
let root: Root;
beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.conflict = false;
    mocks.nested = false;
    mocks.navigate.mockReset();
    mocks.load
        .mockReset()
        .mockResolvedValue([{ name: 'file', isFile: true, mimetype: 'text/plain', isEditable: () => true }]);
    mocks.post.mockReset();
    mocks.post.mockImplementation(async (_url, body) => ({
        data: { conflict: body.preview && mocks.conflict ? '/world/one.txt' : null },
    }));
    mocks.mutate.mockReset().mockResolvedValue(undefined);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
    await act(async () =>
        root.render(
            <MemoryRouter initialEntries={['/server/server/files#/.recycle-bin']}>
                <RecycleBin />
            </MemoryRouter>,
        ),
    );
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});
const click = async (element: HTMLElement) => {
    await act(async () => element.click());
};

describe('recycle bin restore workflow', () => {
    it('previews a bulk restore and waits for explicit confirmation', async () => {
        await click(getByRole(container, 'checkbox', { name: 'strings:select_all' }));
        await click(getByRole(container, 'button', { name: 'restore' }));
        expect(mocks.post).toHaveBeenCalledTimes(2);
        expect(mocks.post.mock.calls.every((call) => call[1].preview === true)).toBe(true);
        const dialog = getByRole(container, 'dialog');
        expect(dialog.textContent).toContain('/world/one.txt');
        expect(dialog.textContent).toContain('/world/two.txt');
        await click(getByRole(dialog, 'button', { name: 'restore' }));
        expect(mocks.post.mock.calls.slice(2).map((call) => call[1])).toEqual([
            { id: 'a', replace: false },
            { id: 'b', replace: false },
        ]);
    });
    it('offers an explicit replacement action and cancelling makes no changes', async () => {
        mocks.conflict = true;
        await click(getByRole(container, 'checkbox', { name: '/world/one.txt' }));
        await click(getByRole(container, 'button', { name: 'restore' }));
        const dialog = getByRole(container, 'dialog');
        expect(getByRole(dialog, 'button', { name: 'replace' })).toBeDefined();
        expect(dialog.querySelector('input[type=checkbox]')).toBeNull();
        expect(dialog.textContent).toContain('replace-warning');
        await click(getByRole(dialog, 'button', { name: 'cancel' }));
        expect(mocks.post).toHaveBeenCalledTimes(1);
    });
    it('only deletes selected entries after the second permanent confirmation', async () => {
        await click(getByRole(container, 'checkbox', { name: '/world/two.txt' }));
        await click(getByRole(container, 'button', { name: 'server/files:delete' }));
        const dialog = getByRole(container, 'dialog');
        await click(getByRole(dialog, 'button', { name: 'permanent' }));
        expect(mocks.post).not.toHaveBeenCalled();
        await click(getByRole(dialog, 'button', { name: 'back' }));
        expect(mocks.post).not.toHaveBeenCalled();
        await click(getByRole(dialog, 'button', { name: 'permanent' }));
        await click(getByRole(dialog, 'button', { name: 'permanent' }));
        expect(mocks.post).toHaveBeenCalledExactlyOnceWith('/api/client/servers/server/files/recycle-bin/delete', {
            id: 'b',
        });
        expect(getAllByRole(container, 'checkbox')).toHaveLength(3);
    });
    it('opens a recycled file in the existing editor while preserving its display name', async () => {
        await click(getByRole(container, 'button', { name: /one.txt/ }));
        expect(mocks.load).toHaveBeenCalledExactlyOnceWith('server', '/.recycle-bin/a');
        expect(mocks.navigate).toHaveBeenCalledExactlyOnceWith('/server/server/files/edit#/.recycle-bin/a/one.txt');
        expect(container.textContent).not.toContain('storage');
    });
    it('restores and deletes nested selections using the parent entry ID and relative path', async () => {
        mocks.nested = true;
        await act(async () =>
            root.render(
                <MemoryRouter key='nested' initialEntries={['/server/server/files#/.recycle-bin/a/world/deep']}>
                    <RecycleBin />
                </MemoryRouter>,
            ),
        );
        await click(getByRole(container, 'checkbox', { name: '/world/deep/nested.txt' }));
        await click(getByRole(container, 'button', { name: 'restore' }));
        expect(mocks.post).toHaveBeenLastCalledWith('/api/client/servers/server/files/recycle-bin/restore', {
            id: 'a',
            path: 'deep/nested.txt',
            preview: true,
        });
        await click(getByRole(getByRole(container, 'dialog'), 'button', { name: 'restore' }));
        expect(mocks.post).toHaveBeenLastCalledWith('/api/client/servers/server/files/recycle-bin/restore', {
            id: 'a',
            path: 'deep/nested.txt',
            replace: false,
        });
        await click(getByRole(container, 'checkbox', { name: '/world/deep/nested.txt' }));
        await click(getByRole(container, 'button', { name: 'server/files:delete' }));
        await click(getByRole(getByRole(container, 'dialog'), 'button', { name: 'permanent' }));
        await click(getByRole(getByRole(container, 'dialog'), 'button', { name: 'permanent' }));
        expect(mocks.post).toHaveBeenLastCalledWith('/api/client/servers/server/files/recycle-bin/delete', {
            id: 'a',
            path: 'deep/nested.txt',
        });
    });
});
