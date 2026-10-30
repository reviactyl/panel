import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { SWRConfig, unstable_serialize } from 'swr';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import DashboardContainer from './DashboardContainer';

const mocks = vi.hoisted(() => ({ getServers: vi.fn() }));
vi.mock('@/api/getServers', () => ({ default: mocks.getServers }));
vi.mock('@/api/account/getServerCategories', () => ({
    default: async () => [
        { uuid: 'small', name: 'Small' },
        { uuid: 'large', name: 'Large' },
    ],
}));
vi.mock('@/api/getClientEggs', () => ({ default: async () => [] }));
vi.mock('easy-peasy', () => ({
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({ user: { data: { uuid: 'user', rootAdmin: false, rolePermissions: [] } }, designify: { data: {} } }),
}));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/plugins/usePersistedState', () => ({ usePersistedState: () => [false, vi.fn()] }));
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
vi.mock('@/reviactyl/components/button', () => ({
    Button: { Text: (props: React.ComponentProps<'button'>) => <button {...props} /> },
}));
vi.mock('@/components/dashboard/CategoryManagerModal', () => ({ default: () => null }));
vi.mock('@/components/dashboard/search/SearchContainer', () => ({ default: () => null }));
vi.mock('@/components/dashboard/ServerLayout', () => ({ LayoutContainer: () => null, ServerLayout: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));
vi.mock('@/components/dashboard/CategorySection', () => ({
    default: ({ servers }: { servers: { name: string }[] }) => (
        <div>{servers.map((server) => server.name).join(',')}</div>
    ),
}));

describe('dashboard category pagination', () => {
    let container: HTMLDivElement;
    let root: Root;

    beforeEach(() => {
        vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
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

    const render = async () => {
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
