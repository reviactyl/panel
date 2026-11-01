import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import AccountContentBlock from './AccountContentBlock';

const state = vi.hoisted(() => ({
    settings: { data: { name: 'Panel' } },
    designify: { data: {} as { headerStyle?: 'classic' | 'modern' } },
}));
vi.mock('easy-peasy', () => ({ useStoreState: (select: (value: typeof state) => unknown) => select(state) }));
vi.mock('@/reviactyl/elements/PageContentBlock', () => ({
    default: ({ title, inset, children }: { title: string; inset?: boolean; children: ReactNode }) => (
        <main data-title={title} data-inset={!!inset}>
            {children}
        </main>
    ),
}));
vi.mock('@/reviactyl/ui/ContentBlock', () => ({
    default: ({ title, children }: { title: string; children: ReactNode }) => (
        <main>
            <h2>{title}</h2>
            {children}
        </main>
    ),
}));

describe('account header styles', () => {
    let container: HTMLDivElement;
    let root: Root;
    beforeEach(() => {
        state.designify.data = {};
        container = document.createElement('div');
        root = createRoot(container);
    });
    afterEach(async () => {
        await act(async () => root.unmount());
    });

    it.each([undefined, 'classic'] as const)('keeps the old plain account pages for %s', async (headerStyle) => {
        state.designify.data = { headerStyle };
        await act(async () =>
            root.render(
                <AccountContentBlock title='API'>
                    <button>Create key</button>
                </AccountContentBlock>,
            ),
        );
        expect(container.querySelector('header')).toBeNull();
        expect(container.querySelector('main')?.dataset.inset).toBe('false');
        expect(container.querySelector('button')?.textContent).toBe('Create key');
    });

    it('retains the classic overview heading', async () => {
        await act(async () =>
            root.render(
                <AccountContentBlock classicHeading title='Overview'>
                    Settings
                </AccountContentBlock>,
            ),
        );
        expect(container.querySelector('h2')?.textContent).toBe('Overview');
    });

    it('switches to modern and back without reloading', async () => {
        state.designify.data.headerStyle = 'modern';
        await act(async () => root.render(<AccountContentBlock title='API'>Keys</AccountContentBlock>));
        expect(container.querySelector('header h1')?.textContent).toBe('API');
        expect(container.querySelector('main')?.dataset.inset).toBe('true');
        expect(container.querySelector('main')?.dataset.title).toBe('API | Panel');
        state.designify.data.headerStyle = 'classic';
        await act(async () => root.render(<AccountContentBlock title='API'>Keys</AccountContentBlock>));
        expect(container.querySelector('header')).toBeNull();
        expect(container.textContent).toBe('Keys');
    });
});
