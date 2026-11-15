import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter } from 'react-router-dom';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import type { Server } from '@/api/server/getServer';
import type { DesignifySettings } from '@/state/designify';
import ServerGrid from './ServerGrid';

const state = vi.hoisted(() => ({ designify: { data: {} as Partial<DesignifySettings> } }));
vi.mock('@/state/hooks', () => ({ useStoreState: (select: (value: typeof state) => unknown) => select(state) }));
vi.mock('@/plugins/useServerResources', () => ({
    default: () => ({ ref: null, stats: null, isSuspended: true }),
}));
vi.mock('@/routers/preloadServerConsole', () => ({ default: vi.fn() }));
vi.mock('@/components/dashboard/ChangeCategoryModal', () => ({ default: () => null }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/ui/Blur', () => ({ default: ({ children }: { children: ReactNode }) => children }));

let root: Root;
let container: HTMLDivElement;
const server = {
    id: 'test',
    name: 'Minecraft',
    status: 'suspended',
    eggImage: '/minecraft.png',
    limits: { cpu: 100, memory: 1024, disk: 2048 },
} as Server;

beforeEach(() => {
    state.designify.data = {};
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
});

const render = async (eggImage = server.eggImage) => {
    await act(async () => {
        root.render(
            <MemoryRouter>
                <ServerGrid server={{ ...server, eggImage }} showCategory={false} />
            </MemoryRouter>,
        );
    });
    return container.querySelector('img')!;
};

it('keeps the larger classic icon for legacy settings and ignores the background size', async () => {
    state.designify.data.cardIconSize = 'enlarged';
    const image = await render();
    expect(image.getAttribute('src')).toBe('/minecraft.png');
    expect(image.getAttribute('alt')).toBe('');
    expect(image.classList.contains('right-4')).toBe(true);
    expect(image.classList.contains('h-40')).toBe(true);
    expect(image.classList.contains('opacity-40')).toBe(true);
    expect(image.classList.contains('scale-150')).toBe(false);
    expect(container.querySelector('a')?.getAttribute('href')).toBe('/server/test/');
    expect(container.textContent).toContain('server.suspended');
});

it('updates the centered background and uniform enlargement when preview settings change', async () => {
    state.designify.data.cardIconStyle = 'background';
    let image = await render();
    expect(image.classList.contains('inset-0')).toBe(true);
    expect(image.classList.contains('object-contain')).toBe(true);
    expect(image.classList.contains('scale-150')).toBe(false);
    state.designify.data.cardIconSize = 'enlarged';
    image = await render();
    expect(image.classList.contains('scale-150')).toBe(true);
    state.designify.data.cardIconStyle = 'classic';
    image = await render();
    expect(image.classList.contains('right-4')).toBe(true);
    expect(image.classList.contains('scale-150')).toBe(false);
});

it('uses the same background treatment for the fallback icon', async () => {
    state.designify.data = { cardIconStyle: 'background', cardIconSize: 'enlarged' };
    const image = await render('');
    expect(image.getAttribute('src')).toBe('/reviactyl/icon.png');
    expect(image.classList.contains('scale-150')).toBe(true);
});
