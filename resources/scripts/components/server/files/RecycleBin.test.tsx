import { MemoryRouter } from 'react-router-dom';
import { act, createElement, type ReactNode } from 'react';
import { renderToStaticMarkup } from 'react-dom/server';
import {
    FaArrowDownAZ,
    FaArrowDownZA,
    FaArrowDown19,
    FaArrowDown91,
    FaArrowDownShortWide,
    FaArrowDownWideShort,
} from 'react-icons/fa6';
import { createRoot, type Root } from 'react-dom/client';
import { getByRole, getAllByRole } from '@testing-library/dom';
import { beforeEach, afterEach, describe, expect, it, vi } from 'vitest';
import RecycleBin from './RecycleBin';

const mocks = vi.hoisted(() => ({
    post: vi.fn(),
    mutate: vi.fn(),
    refreshChildren: vi.fn(),
    listingError: false,
    browseError: false,
    conflict: false,
    nested: false,
    sorting: false,
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
                  ...(mocks.browseError ? { data: undefined, error: new Error('Directory unavailable') } : {}),
                  mutate: mocks.refreshChildren,
              }
            : !key
              ? {}
              : {
                    data: {
                        size: 3,
                        data: mocks.sorting
                            ? [
                                  {
                                      id: 'z',
                                      original_path: '/zebra.txt',
                                      size: 1,
                                      created_at: '2026-11-02T12:00:00Z',
                                      is_file: true,
                                  },
                                  {
                                      id: 'a',
                                      original_path: '/alpha.txt',
                                      size: 2,
                                      created_at: '2026-11-03T12:00:00Z',
                                      is_file: true,
                                  },
                                  {
                                      id: 'm',
                                      original_path: '/middle.txt',
                                      size: 3,
                                      created_at: '2026-11-01T12:00:00Z',
                                      is_file: true,
                                  },
                              ]
                            : [
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
                    ...(mocks.listingError ? { data: undefined, error: new Error('Listing unavailable') } : {}),
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
    mocks.sorting = false;
    mocks.listingError = false;
    mocks.browseError = false;
    mocks.navigate.mockReset();
    mocks.load
        .mockReset()
        .mockResolvedValue([{ name: 'file', isFile: true, mimetype: 'text/plain', isEditable: () => true }]);
    mocks.post.mockReset();
    mocks.post.mockImplementation(async (_url, body) => ({
        data: { conflict: body.preview && mocks.conflict ? '/world/one.txt' : null },
    }));
    mocks.mutate.mockReset().mockResolvedValue(undefined);
    mocks.refreshChildren.mockReset().mockResolvedValue(undefined);
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

describe('recycle bin sort indicators and order', () => {
    it.each([
        {
            column: 'name',
            ascending: ['/alpha.txt', '/middle.txt', '/zebra.txt'],
            icons: [FaArrowDownAZ, FaArrowDownZA],
        },
        {
            column: 'size',
            ascending: ['/zebra.txt', '/alpha.txt', '/middle.txt'],
            icons: [FaArrowDown19, FaArrowDown91],
        },
        {
            column: 'date',
            ascending: ['/middle.txt', '/zebra.txt', '/alpha.txt'],
            icons: [FaArrowDownShortWide, FaArrowDownWideShort],
        },
    ])('uses upright $column indicators matching both sort directions', async ({ column, ascending, icons }) => {
        mocks.sorting = true;
        await act(async () =>
            root.render(
                <MemoryRouter>
                    <RecycleBin />
                </MemoryRouter>,
            ),
        );
        const button = getByRole(container, 'button', { name: `server/files:${column}` });
        const names = () =>
            getAllByRole(container, 'checkbox')
                .slice(1)
                .map((checkbox) => checkbox.getAttribute('aria-label'));
        if (column !== 'name') await click(button);
        for (const [index, expected] of [ascending, [...ascending].reverse()].entries()) {
            if (index) await click(button);
            expect(names()).toEqual(expected);
            const expectedSvg = document.createElement('div');
            expectedSvg.innerHTML = renderToStaticMarkup(createElement(icons[index]!));
            expect(button.querySelector('svg')?.innerHTML).toBe(expectedSvg.querySelector('svg')!.innerHTML);
            expect(button.querySelector('[class*="rotate"], [style*="transform"]')).toBeNull();
            for (const other of ['name', 'size', 'date'].filter((value) => value !== column)) {
                expect(
                    getByRole(container, 'button', { name: `server/files:${other}` }).querySelector('svg'),
                ).toBeNull();
            }
        }
        await click(getByRole(container, 'button', { name: `server/files:${column === 'name' ? 'date' : 'name'}` }));
        await click(button);
        expect(names()).toEqual(ascending);
        await click(getByRole(container, 'checkbox', { name: ascending[0] }));
        await click(getByRole(container, 'button', { name: 'restore' }));
        expect(mocks.post).toHaveBeenCalledExactlyOnceWith('/api/client/servers/server/files/recycle-bin/restore', {
            id: ascending[0] === '/zebra.txt' ? 'z' : ascending[0] === '/alpha.txt' ? 'a' : 'm',
            preview: true,
        });
    });
});

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

describe('recycle bin loading recovery', () => {
    it.each([
        { nested: true, listingError: true, browseError: false },
        { nested: true, listingError: false, browseError: true },
        { nested: true, listingError: true, browseError: true },
        { nested: false, listingError: true, browseError: false },
    ])('recovers nested=$nested listingError=$listingError browseError=$browseError', async (scenario) => {
        Object.assign(mocks, scenario);
        const render = () =>
            root.render(
                <MemoryRouter
                    key='retry'
                    initialEntries={[
                        scenario.nested
                            ? '/server/server/files#/.recycle-bin/a/world/deep'
                            : '/server/server/files#/.recycle-bin',
                    ]}
                >
                    <RecycleBin />
                </MemoryRouter>,
            );
        mocks.mutate.mockImplementation(async () => {
            mocks.listingError = false;
            render();
        });
        mocks.refreshChildren.mockImplementation(async () => {
            mocks.browseError = false;
            render();
        });
        await act(async () => render());
        expect(getByRole(container, 'alert').textContent).toBe('Failed');
        await click(getByRole(container, 'button', { name: 'retry' }));
        expect(mocks.mutate).toHaveBeenCalledTimes(scenario.listingError ? 1 : 0);
        expect(mocks.refreshChildren).toHaveBeenCalledTimes(scenario.browseError ? 1 : 0);
        expect(container.querySelector('[role="alert"]')).toBeNull();
        expect(
            getByRole(container, 'checkbox', { name: scenario.nested ? '/world/deep/nested.txt' : '/world/one.txt' }),
        ).toBeDefined();
    });
});
