import { act, type ComponentType, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { renderToStaticMarkup } from 'react-dom/server';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { Server } from '@/api/server/getServer';
import type { ServerStats } from '@/api/server/getServerResourceUsage';
import ServerGrid from './ServerGrid';
import ServerRow from './ServerRow';

const resources = vi.hoisted(() => ({ stats: null as ServerStats | null, isSuspended: false }));
vi.mock('@/plugins/useServerResources', () => ({ default: () => ({ ref: null, ...resources }) }));
vi.mock('@/state/hooks', () => ({ useStoreState: () => false }));
vi.mock('@/routers/preloadServerConsole', () => ({ default: vi.fn() }));
vi.mock('@/api/server/getServer', () => ({ prepareServer: vi.fn() }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/ui/Blur', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/components/dashboard/ChangeCategoryModal', () => ({
    default: ({ visible }: { visible: boolean }) => (visible ? <div data-category-modal /> : null),
}));

const server = {
    id: 'playground',
    uuid: 'test-server',
    name: 'Demo Playground',
    description: 'Minecraft survival',
    status: null,
    category: null,
    allocations: [{ isDefault: true, ip: '127.0.0.1', port: 25565 }],
    limits: { cpu: 100, memory: 1024, disk: 2048 },
} as Server;
const stats: ServerStats = {
    status: 'running',
    isSuspended: false,
    cpuUsagePercent: 12,
    memoryUsageInBytes: 1024,
    diskUsageInBytes: 2048,
    networkRxInBytes: 0,
    networkTxInBytes: 0,
    uptime: 1000,
};
let root: Root;
let container: HTMLDivElement;
type CardProps = React.ComponentProps<typeof ServerGrid>;
const render = async (Component: ComponentType<CardProps>, props: Partial<CardProps> = {}) => {
    await act(async () =>
        root.render(
            <MemoryRouter>
                <Component server={server} {...props} />
            </MemoryRouter>,
        ),
    );
};

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    resources.stats = null;
    resources.isSuspended = false;
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

it.each([null, { name: 'Games', color: '#3b82f6' }, { name: 'Survival', color: '' }])(
    'uses the same theme-aware category surface in Grid and Row for %j',
    async (category) => {
        const surfaces: string[] = [];
        for (const Component of [ServerGrid, ServerRow]) {
            await render(Component, { server: { ...server, category } as Server });
            const badge = container.querySelector<HTMLElement>('[role=button]')!;
            expect(badge.textContent).toBe(category?.name ?? 'categories.set-category');
            const markup = renderToStaticMarkup(
                <MemoryRouter>
                    <Component server={{ ...server, category } as Server} />
                </MemoryRouter>,
            );
            const style = markup.match(/role="button"[^>]*style="([^"]+)"/)![1]!;
            surfaces.push(style);
            expect(style).toContain(category ? '--color-100' : '--color-gray-400');
            expect(style).toContain(category ? 'color-mix' : '--color-gray-800');
            expect(style).toContain(category ? 'color-mix' : '--color-gray-700');
        }
        expect(surfaces[0]).toBe(surfaces[1]);
    },
);

it('keeps the Grid status pill absent until a state label arrives and clears it when resources reset', async () => {
    await render(ServerGrid);
    expect(container.querySelector('span.rounded-ui')).toBeNull();
    expect(container.querySelector('.animate-spin')).not.toBeNull();
    resources.stats = stats;
    await render(ServerGrid);
    expect(container.querySelector('span.rounded-ui')?.textContent?.trim()).toBe('server.online');
    expect(container.querySelector('.animate-spin')).toBeNull();
    resources.stats = null;
    await render(ServerGrid);
    expect(container.querySelector('span.rounded-ui')).toBeNull();
});

it.each([
    ['offline', 'offline', 'bg-danger/20'],
    ['running', 'online', 'bg-success/20'],
    ['starting', 'starting', 'bg-amber-500/20'],
    ['stopping', 'stopping', 'bg-orange-500/20'],
] as const)('preserves the Grid %s status label and color', async (status, label, surface) => {
    resources.stats = { ...stats, status };
    await render(ServerGrid);
    const pill = container.querySelector('span.rounded-ui')!;
    expect(pill.textContent?.trim()).toBe(`server.${label}`);
    expect(pill.classList.contains(surface)).toBe(true);
    expect(container.textContent).toContain('12.00%');
});

for (const [layout, Component] of [
    ['Grid', ServerGrid],
    ['Row', ServerRow],
] as const) {
    describe(layout, () => {
        it.each([
            [{ status: 'suspended' }, true, 'suspended'],
            [{}, true, 'connection-error'],
            [{ isNodeUnderMaintenance: true }, false, 'maintenance'],
            [{ isTransferring: true }, false, 'transferring'],
            [{ status: 'installing' }, false, 'installing'],
            [{ status: 'restoring_backup' }, false, 'restoring-backup'],
            [{ status: 'install_failed' }, false, 'unavailable'],
        ] as const)('preserves %j with a valid blurred status surface', async (overrides, suspended, label) => {
            resources.isSuspended = suspended;
            await render(Component, { server: { ...server, ...overrides } as Server });
            const text = Array.from(container.querySelectorAll('span, p')).find(
                (element) => element.textContent?.trim() === `server.${label}`,
            )!;
            const surface = layout === 'Grid' ? text.parentElement! : text;
            expect(surface.classList.contains('backdrop-blur-xs')).toBe(true);
            expect(container.querySelector('[class*=backdrop-blur-sm-xs]')).toBeNull();
            expect(
                Array.from(container.querySelectorAll('span.rounded-ui')).every((pill) => pill.textContent?.trim()),
            ).toBe(true);
        });

        it('keeps category activation and folder move controls usable by click, Enter and Space', async () => {
            await render(Component);
            const badge = container.querySelector<HTMLElement>('[role=button]')!;
            await act(async () => badge.click());
            expect(container.querySelector('[data-category-modal]')).not.toBeNull();
            const onMove = vi.fn();
            await render(Component, { showCategory: false, onMove });
            const move = container.querySelector<HTMLElement>('[role=button]')!;
            expect(move.getAttribute('aria-label')).toBe('folders.move-server: Demo Playground');
            expect(move.textContent).toBe('folders.home');
            expect(move.tabIndex).toBe(0);
            await act(async () => {
                move.click();
                for (const key of ['Enter', ' '])
                    move.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true }));
            });
            expect(onMove).toHaveBeenCalledTimes(3);
            expect(container.querySelector('a')?.getAttribute('href')).toMatch(/^\/server\/playground\/?$/);
            await render(Component, { showCategory: false });
            expect(container.querySelector('[role=button]')).toBeNull();
        });
    });
}
