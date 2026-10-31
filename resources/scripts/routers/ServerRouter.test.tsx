import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter, Route, Routes, useNavigate } from 'react-router-dom';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import ServerRouter from './ServerRouter';

const mocks = vi.hoisted(() => ({ load: vi.fn(), clear: vi.fn() }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) => selector({ server: {} }),
        useStoreActions: (selector: (actions: unknown) => unknown) =>
            selector({ server: { getServer: mocks.load }, clearServerState: mocks.clear }),
    },
}));
vi.mock('easy-peasy', async (importOriginal) => ({
    ...(await importOriginal<typeof import('easy-peasy')>()),
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({ designify: { data: { layoutType: 'default' } }, user: { data: {} } }),
}));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/extensions/useExtensionRoutes', () => ({ useExtensionRoutes: () => [] }));
vi.mock('@/routers/routes', () => ({ default: { server: { control: [], management: [], administration: [] } } }));
vi.mock('@/api/http', () => ({ httpErrorToHuman: (error: Error) => error.message }));
vi.mock('@/reviactyl/ui/RouterContainer', () => ({
    RouterContainer: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/ScreenBlock', () => ({
    ServerError: ({ message }: { message: string }) => <div role='alert'>{message}</div>,
}));
vi.mock('@/reviactyl/elements/Spinner', () => ({ default: () => <div>Loading server</div> }));

vi.mock('@/components/server/TransferListener', () => ({ default: () => null }));
vi.mock('@/components/server/WebsocketHandler', () => ({ default: () => null }));
vi.mock('@/components/server/InstallListener', () => ({ default: () => null }));
vi.mock('@/components/server/ConflictStateRenderer', () => ({ default: () => null }));
vi.mock('@/components/server/TopServerDetails', () => ({ default: () => null }));
vi.mock('@/reviactyl/components/Navigate', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/ErrorBoundary', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/PermissionRoute', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/MobileBottomBar', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/Announcement', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/MaintenanceAlert', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/Maintenance', () => ({ default: () => null }));
vi.mock('@/reviactyl/components/Layout', () => ({ Sidebar: () => null, Navbar: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));

let navigate: ReturnType<typeof useNavigate>;
function Navigation() {
    navigate = useNavigate();
    return (
        <Routes>
            <Route path='/server/:id/*' element={<ServerRouter />} />
        </Routes>
    );
}
const deferred = () => {
    let reject!: (error: Error) => void;
    const promise = new Promise<void>((_, fail) => (reject = fail));
    return { promise, reject };
};
let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    vi.clearAllMocks();
    mocks.load.mockReset();
    container = document.createElement('div');
    root = createRoot(container);
    vi.spyOn(console, 'error').mockImplementation(() => undefined);
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.restoreAllMocks();
});

it('ignores a late failure from A after navigating to B but displays B failures', async () => {
    const a = deferred();
    const b = deferred();
    mocks.load.mockReturnValueOnce(a.promise).mockReturnValueOnce(b.promise);
    await act(async () => {
        root.render(
            <MemoryRouter initialEntries={['/server/A']}>
                <Navigation />
            </MemoryRouter>,
        );
    });
    expect(mocks.load).toHaveBeenLastCalledWith('A');
    await act(async () => navigate('/server/B'));
    expect(mocks.load).toHaveBeenLastCalledWith('B');
    await act(async () => a.reject(new Error('A failed')));
    expect(container.querySelector('[role=alert]')).toBeNull();
    expect(container.textContent).toBe('Loading server');
    await act(async () => b.reject(new Error('B failed')));
    expect(container.querySelector('[role=alert]')?.textContent).toBe('B failed');
});
