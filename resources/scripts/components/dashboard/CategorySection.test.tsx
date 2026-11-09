import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { Server } from '@/api/server/getServer';
import CategorySection from './CategorySection';

vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('framer-motion', () => ({
    AnimatePresence: ({ children }: { children: ReactNode }) => children,
    motion: {
        div: ({ children, className }: { children: ReactNode; className?: string }) => (
            <div className={className}>{children}</div>
        ),
    },
}));
vi.mock('@/components/dashboard/ServerLayout', () => ({
    LayoutContainer: ({ children }: { children: ReactNode }) => children,
    ServerLayout: ({ server }: { server: Server }) => <span>{server.name}</span>,
}));
vi.mock('@/reviactyl/ui/Card', () => ({ default: ({ children }: { children: ReactNode }) => <div>{children}</div> }));

const server = { uuid: 'server', name: 'Demo server' } as Server;
let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    localStorage.clear();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    localStorage.clear();
    vi.unstubAllGlobals();
});

it('exposes live expanded state and a stable server-list relationship while persisting mouse toggles', async () => {
    await act(async () => root.render(<CategorySection category={null} servers={[server]} showOnlyAdmin={false} />));
    const button = container.querySelector('button')!;
    expect(button.getAttribute('aria-expanded')).toBe('true');
    const contentId = button.getAttribute('aria-controls')!;
    expect(contentId).toBeTruthy();
    expect(document.getElementById(contentId)?.textContent).toContain(server.name);

    await act(async () => button.click());
    expect(button.getAttribute('aria-expanded')).toBe('false');
    expect(localStorage.getItem('server-category-primary')).toBe('false');
    expect(document.getElementById(contentId)?.textContent).toBe('');
    expect(document.getElementById(contentId)).not.toBeNull();

    await act(async () => button.click());
    expect(button.getAttribute('aria-expanded')).toBe('true');
    expect(button.getAttribute('aria-controls')).toBe(contentId);
    expect(document.getElementById(contentId)?.textContent).toContain(server.name);
    expect(localStorage.getItem('server-category-primary')).toBe('true');
});

it('restores collapsed categories independently and assigns unique content IDs to repeated instances', async () => {
    localStorage.setItem('server-category-custom', 'false');
    const category = {
        uuid: 'custom',
        name: 'Games',
        color: '#123456',
        description: '',
        priority: 0,
        createdAt: new Date(),
        updatedAt: new Date(),
    };
    await act(async () =>
        root.render(
            <>
                <CategorySection category={category} servers={[server]} showOnlyAdmin={false} />
                <CategorySection category={null} servers={[server]} showOnlyAdmin={false} />
                <CategorySection category={null} servers={[server]} showOnlyAdmin={false} />
            </>,
        ),
    );
    const buttons = Array.from(container.querySelectorAll('button'));
    expect(buttons.map((button) => button.getAttribute('aria-expanded'))).toEqual(['false', 'true', 'true']);
    const ids = buttons.map((button) => button.getAttribute('aria-controls'));
    expect(ids.every((id) => id && document.getElementById(id))).toBe(true);
    expect(new Set(ids).size).toBe(3);
    expect(document.getElementById(ids[0]!)?.textContent).toBe('');
    await act(async () => buttons[0]!.click());
    expect(buttons[0]!.getAttribute('aria-expanded')).toBe('true');
    expect(localStorage.getItem('server-category-custom')).toBe('true');
    expect(localStorage.getItem('server-category-primary')).toBeNull();
});

it('keeps empty categories absent', async () => {
    await act(async () => root.render(<CategorySection category={null} servers={[]} showOnlyAdmin={false} />));
    expect(container.childElementCount).toBe(0);
});
