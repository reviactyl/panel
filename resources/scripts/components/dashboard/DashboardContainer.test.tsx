import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { SWRConfig, unstable_serialize, useSWRConfig } from 'swr';
import { folderTree } from './folderTree';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import DashboardContainer from './DashboardContainer';

const mocks = vi.hoisted(() => ({
    getServers: vi.fn(),
    organization: 'categories',
    rootAdmin: false,
    getClientEggs: vi.fn(),
    categoryError: false,
    categories: [] as { uuid: string; name: string }[],
}));
vi.mock('./folderTree', async (importOriginal) => {
    const actual = await importOriginal<typeof import('./folderTree')>();
    return { ...actual, folderTree: vi.fn(actual.folderTree) };
});
vi.mock('@/api/getServers', () => ({ default: mocks.getServers }));
vi.mock('@/api/account/getServerCategories', () => ({
    default: async () => {
        if (mocks.categoryError) throw new Error('Unavailable');
        return mocks.categories;
    },
}));
vi.mock('@/api/getClientEggs', () => ({ default: mocks.getClientEggs }));
vi.mock('easy-peasy', () => ({
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({
            user: { data: { uuid: 'user', rootAdmin: mocks.rootAdmin, rolePermissions: [] } },
            designify: { data: { serverOrganization: mocks.organization } },
        }),
}));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/plugins/useFlash', () => ({ default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }) }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/PageContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/ui/PageHeader', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/ui/Card', () => ({ default: ({ children }: { children: ReactNode }) => <div>{children}</div> }));
vi.mock('@/reviactyl/elements/Select', () => ({
    default: (props: React.ComponentProps<'select'>) => <select {...props} />,
}));
vi.mock('@/api/account/deleteServerCategory', () => ({ default: vi.fn() }));
vi.mock('@/components/dashboard/CategoryManagerModal', () => ({ default: () => null }));
vi.mock('@/components/dashboard/search/SearchContainer', () => ({ default: () => null }));
vi.mock('@/components/dashboard/ServerLayout', () => ({
    LayoutContainer: ({ children }: { children: ReactNode }) => children,
    ServerLayout: ({
        server,
        onCategoryChanged,
        onMove,
    }: {
        server: { name: string };
        onCategoryChanged?: () => void;
        onMove?: () => void;
    }) => <button onClick={onMove || onCategoryChanged}>{server.name}</button>,
}));
vi.mock('@/components/dashboard/MoveServersModal', () => ({
    default: ({ onSaved, onDismissed }: { onSaved: () => Promise<unknown>; onDismissed: () => void }) => (
        <button
            onClick={async () => {
                await onSaved();
                onDismissed();
            }}
        >
            Confirm Move
        </button>
    ),
}));
vi.mock('@/components/dashboard/FolderFormModal', () => ({ default: () => null }));
vi.mock('@/components/dashboard/DeleteFolderModal', () => ({ default: () => null }));
vi.mock('@/components/dashboard/MoveFolderModal', () => ({ default: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('@/components/dashboard/CategorySection', () => ({
    default: ({ servers, showOnlyAdmin }: { servers: { name: string }[]; showOnlyAdmin: boolean }) => (
        <div>
            {servers.map((server) => server.name).join(',')}
            {!showOnlyAdmin && <button>Category actions</button>}
        </div>
    ),
}));

describe('dashboard category pagination', () => {
    let container: HTMLDivElement;
    let root: Root;

    beforeEach(() => {
        vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
        localStorage.clear();
        mocks.rootAdmin = false;
        mocks.getClientEggs.mockReset().mockResolvedValue([
            { id: 1, name: 'Egg One' },
            { id: 2, name: 'Egg Two' },
        ]);
        mocks.organization = 'categories';
        mocks.categoryError = false;
        mocks.categories = [
            { uuid: 'small', name: 'Small' },
            { uuid: 'large', name: 'Large' },
        ];
        vi.mocked(folderTree).mockClear();
        mocks.getServers.mockReset().mockImplementation(async ({ page, 'filter[category_uuid]': category }) => ({
            items:
                category === 'small' && page > 1
                    ? []
                    : [{ uuid: 'server', name: `Server ${page}`, category: { uuid: category || 'large' } }],
            pagination: {
                currentPage: page,
                totalPages: category === 'small' ? 1 : 2,
                total: category === 'small' ? 1 : 2,
                count: 1,
                perPage: 1,
            },
        }));
        container = document.createElement('div');
        document.body.appendChild(container);
        root = createRoot(container);
    });

    afterEach(async () => {
        await act(async () => root.unmount());
        container.remove();
        vi.unstubAllGlobals();
    });

    const render = async (controls?: ReactNode) => {
        await act(async () => {
            root.render(
                <SWRConfig
                    value={{
                        provider: () => new Map(),
                        dedupingInterval: 0,
                        fallback: {
                            [unstable_serialize(['/api/client/servers', false, 2, 'small', null])]: {
                                items: [],
                                pagination: { currentPage: 2, totalPages: 1, total: 1, count: 0, perPage: 1 },
                            },
                        },
                    }}
                >
                    {controls}
                    <MemoryRouter>
                        <DashboardContainer />
                    </MemoryRouter>
                </SWRConfig>,
            );
        });
    };
    const clickPageTwo = async () => {
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === '2')!
                .click(),
        );
    };
    const selectCategory = async (category: string) => {
        await act(async () => container.querySelector<HTMLButtonElement>('[aria-label="filter-label"]')!.click());
        await act(async () => {
            const select = container.querySelector<HTMLSelectElement>('[aria-label="categories.all-categories"]')!;
            select.value = category;
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
    };

    const openFilters = async () => {
        const button = container.querySelector<HTMLButtonElement>('[aria-label="filter-label"]');
        expect(button).not.toBeNull();
        await act(async () => button!.click());
    };
    const selectEgg = async (egg: string) => {
        await openFilters();
        await act(async () => {
            const select = container.querySelector<HTMLSelectElement>('[aria-label="eggs.filter-label"]')!;
            select.value = egg;
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
    };
    const toggleAdmin = async () => {
        await act(async () => container.querySelector<HTMLInputElement>('[name="show_all_servers"]')!.click());
    };
    const mockEggResults = () => {
        mocks.getServers.mockImplementation(async ({ page, type, eggId }) => ({
            items: (eggId ? [eggId] : [1, 2]).map((id) => ({
                uuid: `server-${id}`,
                name: `${type === 'admin' ? 'Other' : 'Personal'} Egg ${id}`,
            })),
            pagination: { currentPage: page, totalPages: 2, total: 2, count: 2, perPage: 2 },
        }));
    };

    it('restores personal filters and category actions after admin access is removed with a persisted toggle', async () => {
        localStorage.setItem('user:show_all_servers', 'true');
        mocks.rootAdmin = true;
        await render();
        expect(mocks.getServers).toHaveBeenLastCalledWith(expect.objectContaining({ type: 'admin' }));

        mocks.rootAdmin = false;
        await render();
        expect(localStorage.getItem('user:show_all_servers')).toBe('true');
        expect(container.querySelector('[name="show_all_servers"]')).toBeNull();
        expect(mocks.getServers).toHaveBeenLastCalledWith(expect.objectContaining({ type: undefined }));
        expect(container.textContent).toContain('Category actions');
        expect(container.querySelector('[aria-label="categories.manage"]')).not.toBeNull();
        await openFilters();
        expect(container.querySelector('[aria-label="categories.all-categories"]')).not.toBeNull();
        expect(container.querySelector('[aria-label="eggs.filter-label"]')).not.toBeNull();
    });

    it.each([false, true])(
        'filters server results by Egg with admin view %s and resets pagination',
        async (adminView) => {
            mocks.rootAdmin = true;
            localStorage.setItem('user:show_all_servers', String(adminView));
            mockEggResults();
            await render();
            expect(mocks.getClientEggs).toHaveBeenLastCalledWith(adminView ? 'admin' : undefined);
            await clickPageTwo();
            await selectEgg('2');
            expect(mocks.getServers).toHaveBeenLastCalledWith(
                expect.objectContaining({ page: 1, type: adminView ? 'admin' : undefined, eggId: 2 }),
            );
            expect(container.textContent).toContain(`${adminView ? 'Other' : 'Personal'} Egg 2`);
            expect(container.textContent).not.toContain('Egg 1');
            await selectEgg('');
            expect(mocks.getServers).toHaveBeenLastCalledWith(expect.objectContaining({ eggId: undefined }));
            expect(container.textContent).toContain(`${adminView ? 'Other' : 'Personal'} Egg 1`);
        },
    );

    it('ignores the personal Category in Other Servers while preserving Egg filtering across view changes', async () => {
        mocks.rootAdmin = true;
        mockEggResults();
        mocks.getClientEggs.mockImplementation(async (type) =>
            type === 'admin' ? [{ id: 1, name: 'Other Egg' }] : [{ id: 2, name: 'Personal Egg' }],
        );
        await render();
        await selectCategory('small');
        // The Category selector keeps the filter panel open.
        await openFilters();
        await selectEgg('2');
        await toggleAdmin();
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ type: 'admin', 'filter[category_uuid]': undefined, eggId: 2 }),
        );
        await openFilters();
        expect(container.querySelector('[aria-label="categories.all-categories"]')).toBeNull();
        expect(container.querySelector<HTMLSelectElement>('[aria-label="eggs.filter-label"]')?.value).toBe('2');
        await toggleAdmin();
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ type: undefined, 'filter[category_uuid]': 'small', eggId: 2 }),
        );
        await toggleAdmin();
        await openFilters();
        const selected = container.querySelector<HTMLSelectElement>('[aria-label="eggs.filter-label"]')!;
        expect(selected.selectedOptions.item(0)?.textContent).toBe('Personal Egg');
        await act(async () => {
            selected.value = '';
            selected.dispatchEvent(new Event('change', { bubbles: true }));
        });
        expect(mocks.getServers).toHaveBeenLastCalledWith(expect.objectContaining({ type: 'admin', eggId: undefined }));
    });

    it('opens folders and returns Home using server-side category filters', async () => {
        mocks.organization = 'folders';
        await render();
        expect(mocks.getServers).toHaveBeenLastCalledWith(expect.objectContaining({ 'filter[category_uuid]': 'null' }));
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'Small')!
                .click(),
        );
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ page: 1, 'filter[category_uuid]': 'small' }),
        );
        expect(container.querySelector('[aria-current="page"]')?.textContent).toBe('Small');
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'folders.home')!
                .click(),
        );
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ page: 1, 'filter[category_uuid]': 'null' }),
        );
        expect(container.textContent).toContain('Small');
    });

    it('returns Home on page one when a refresh removes the folder being viewed', async () => {
        const RefreshFolders = () => {
            const { mutate } = useSWRConfig();
            return <button onClick={() => mutate('/api/client/account/categories')}>Refresh Folders</button>;
        };
        mocks.organization = 'folders';
        await render(<RefreshFolders />);
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'Large')!
                .click(),
        );
        await clickPageTwo();
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ page: 2, 'filter[category_uuid]': 'large' }),
        );
        mocks.categories = mocks.categories.filter((folder) => folder.uuid !== 'large');
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'Refresh Folders')!
                .click(),
        );
        expect(container.querySelector('[aria-current="page"]')?.textContent).toBe('folders.home');
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ page: 1, 'filter[category_uuid]': 'null' }),
        );
    });

    it('reuses folder ranks when changing server pages', async () => {
        await render();
        const treeBuilds = vi.mocked(folderTree).mock.calls.length;
        expect(treeBuilds).toBeGreaterThan(0);
        await clickPageTwo();
        expect(vi.mocked(folderTree)).toHaveBeenCalledTimes(treeBuilds);
        expect(mocks.getServers).toHaveBeenLastCalledWith(expect.objectContaining({ page: 2 }));
    });

    it('invalidates the cached Home list when a server moves out of a folder', async () => {
        mocks.organization = 'folders';
        let location = 'small';
        mocks.getServers.mockImplementation(async ({ 'filter[category_uuid]': category }) => ({
            items:
                category === location ? [{ uuid: 'server', name: 'Move fixture', category: { uuid: location } }] : [],
            pagination: {
                currentPage: 1,
                totalPages: 1,
                total: category === location ? 1 : 0,
                count: category === location ? 1 : 0,
                perPage: 50,
            },
        }));
        await render();
        expect(container.textContent).toContain('folders.home-empty');
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'Small')!
                .click(),
        );
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'Move fixture')!
                .click(),
        );
        location = 'null';
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'Confirm Move')!
                .click(),
        );
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'folders.home')!
                .click(),
        );
        expect(container.textContent).toContain('Move fixture');
    });

    it('falls back to all servers when folders cannot load and recovers on retry', async () => {
        mocks.organization = 'folders';
        mocks.categoryError = true;
        await render();
        expect(container.textContent).toContain('folders.load-folders-error');
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ 'filter[category_uuid]': undefined }),
        );
        mocks.categoryError = false;
        await act(async () =>
            Array.from(container.querySelectorAll('button'))
                .find((button) => button.textContent === 'folders.retry')!
                .click(),
        );
        expect(container.textContent).not.toContain('folders.load-folders-error');
        expect(container.textContent).toContain('Small');
    });

    it('shows a one-page category selected from a later page', async () => {
        await render();
        await clickPageTwo();
        expect(container.textContent).toContain('Server 2');
        await selectCategory('small');
        expect(container.textContent).toContain('Server 1');
        expect(container.textContent).not.toContain('no-servers');
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ page: 1, 'filter[category_uuid]': 'small' }),
        );
    });

    it('still paginates within a category after resetting the selected page', async () => {
        await render();
        await clickPageTwo();
        await selectCategory('large');
        expect(container.textContent).toContain('Server 1');
        await clickPageTwo();
        expect(container.textContent).toContain('Server 2');
        expect(mocks.getServers).toHaveBeenLastCalledWith(
            expect.objectContaining({ page: 2, 'filter[category_uuid]': 'large' }),
        );
    });
});
