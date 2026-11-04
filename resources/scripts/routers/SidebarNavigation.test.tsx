import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter, Route, Routes, useNavigate } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import DashboardRouter from './DashboardRouter';
import ServerRouter from './ServerRouter';

const mocks = vi.hoisted(() => ({ load: vi.fn().mockResolvedValue(undefined), clear: vi.fn() }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { id: 'demo', uuid: 'demo-uuid' } } }),
        useStoreActions: (selector: (actions: unknown) => unknown) =>
            selector({ server: { getServer: mocks.load }, clearServerState: mocks.clear }),
    },
}));
vi.mock('easy-peasy', async (importOriginal) => ({
    ...(await importOriginal<typeof import('easy-peasy')>()),
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({ designify: { data: { layoutType: 'classic' } }, user: { data: {} } }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/extensions/useExtensionRoutes', () => ({ useExtensionRoutes: () => [] }));
vi.mock('@/extensions/useExtensions', () => ({ useExtensions: () => ({ data: [] }) }));
vi.mock('@/routers/routes', () => ({
    default: {
        account: [
            { name: 'Overview', route: '', end: true, component: () => <main>Account overview page</main> },
            { name: 'API', route: 'api', component: () => <main>Account API page</main> },
        ],
        server: {
            control: [
                { name: 'Console', route: '', end: true, component: () => <main>Console page</main> },
                { name: 'Files', route: 'files', component: () => <main>Files page</main> },
            ],
            management: [],
            administration: [],
        },
    },
}));
vi.mock('@/reviactyl/ui/RouterContainer', () => ({
    RouterContainer: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/ui/ContentContainer', () => ({
    ContentContainer: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/components/Layout', () => ({
    Sidebar: ({ isOpen, children }: { isOpen: boolean; children: ReactNode }) => (
        <aside data-open={isOpen}>{children}</aside>
    ),
    Navbar: ({ children }: { children: ReactNode }) => <header>{children}</header>,
}));
vi.mock('@/reviactyl/elements/Spinner', () => ({
    default: Object.assign(() => null, { Suspense: ({ children }: { children: ReactNode }) => children }),
}));
vi.mock('@/reviactyl/elements/ScreenBlock', () => ({ NotFound: () => null, ServerError: () => null }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/ErrorBoundary', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/PermissionRoute', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/components/dashboard/DashboardContainer', () => ({ default: () => null }));
vi.mock('@/components/dashboard/PasskeysContainer', () => ({ default: () => null }));
vi.mock('@/components/server/TransferListener', () => ({ default: () => null }));
vi.mock('@/components/server/WebsocketHandler', () => ({ default: () => null }));
vi.mock('@/components/server/InstallListener', () => ({ default: () => null }));
vi.mock('@/components/server/ConflictStateRenderer', () => ({ default: () => null }));
vi.mock('@/components/server/TopServerDetails', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/MobileBottomBar', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/Announcement', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/MaintenanceAlert', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/Maintenance', () => ({ default: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));

let navigate: ReturnType<typeof useNavigate>;
function Navigation() {
    navigate = useNavigate();
    return (
        <Routes>
            <Route path='/server/:id/*' element={<ServerRouter />} />
            <Route path='*' element={<DashboardRouter />} />
        </Routes>
    );
}

let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

for (const [name, initial, destination, page] of [
    ['account', '/account/', '/account/api', 'Account API page'],
    ['server', '/server/demo/', '/server/demo/files', 'Files page'],
] as const) {
    describe(`${name} mobile sidebar`, () => {
        const sidebar = () => container.querySelector('aside')!;
        const backdrop = () => container.querySelector('.fixed.inset-0');
        const toggle = () => container.querySelector('header button')!;
        const click = async (element: Element) => {
            await act(async () => element.dispatchEvent(new MouseEvent('click', { bubbles: true, button: 0 })));
        };
        beforeEach(async () => {
            await act(async () => {
                root.render(
                    <MemoryRouter initialEntries={[initial]}>
                        <Navigation />
                    </MemoryRouter>,
                );
            });
        });

        it('closes the sidebar and backdrop after selecting a navigation link', async () => {
            await click(toggle());
            expect(sidebar().dataset.open).toBe('true');
            expect(backdrop()).not.toBeNull();
            await click(sidebar().querySelector(`a[href="${destination}"]`)!);
            expect(container.querySelector('main')?.textContent).toBe(page);
            expect(sidebar().dataset.open).toBe('false');
            expect(backdrop()).toBeNull();
            await click(toggle());
            expect(sidebar().dataset.open).toBe('true');
        });

        it('still opens and dismisses through the toggle and backdrop', async () => {
            expect(sidebar().dataset.open).toBe('false');
            await click(toggle());
            expect(sidebar().dataset.open).toBe('true');
            await click(toggle());
            expect(sidebar().dataset.open).toBe('false');
            expect(backdrop()).toBeNull();
            await click(toggle());
            await click(backdrop()!);
            expect(sidebar().dataset.open).toBe('false');
            expect(backdrop()).toBeNull();
        });

        it('closes on history navigation', async () => {
            await act(async () => navigate(destination));
            await click(toggle());
            expect(sidebar().dataset.open).toBe('true');
            await act(async () => navigate(-1));
            expect(sidebar().dataset.open).toBe('false');
            expect(backdrop()).toBeNull();
        });
    });
}
