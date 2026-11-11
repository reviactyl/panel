import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { ExtensionLayout } from './ExtensionLayout';
import type { ExtensionRegistryRecord, ExtensionLayoutDefinition } from './types';

const state = vi.hoisted(() => ({ data: [] as ExtensionRegistryRecord[], runtime: vi.fn() }));
vi.mock('./loadExtensionRuntime', () => ({ loadExtensionRuntime: state.runtime }));
vi.mock('./useExtensions', () => ({ useExtensions: () => state }));
vi.mock('/extensions/test/layout.js', () => ({
    default: ({ label, defaultLayout }: { label: string; defaultLayout: ReactNode }) => (
        <section>
            {label}
            {defaultLayout}
        </section>
    ),
    Named: ({ children }: { children: ReactNode }) => <nav>{children}</nav>,
    Broken: () => {
        throw new Error('render failed');
    },
}));
vi.mock('/extensions/test/loading.js', () => new Promise(() => {}));
vi.mock('/extensions/test/failed.js', () => {
    throw new Error('import failed');
});

let container: HTMLDivElement;
let root: Root;
const fallback = <button>Built in</button>;
const register = (overrides: Partial<ExtensionLayoutDefinition> = {}) => {
    state.data = [
        {
            id: 'test',
            name: 'Test',
            version: '1',
            frontend: { layouts: [{ name: 'dashboard:sidebar', module: 'layout.js', ...overrides }] },
        },
    ];
};
const render = async (props: Partial<React.ComponentProps<typeof ExtensionLayout>> = {}) => {
    await act(async () => {
        root.render(<ExtensionLayout name='dashboard:sidebar' fallback={fallback} {...props} />);
    });
};

beforeEach(() => {
    (globalThis as typeof globalThis & { IS_REACT_ACT_ENVIRONMENT: boolean }).IS_REACT_ACT_ENVIRONMENT = true;
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
    state.data = [];
    state.runtime.mockReset().mockResolvedValue(undefined);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.restoreAllMocks();
});

describe('extension layouts', () => {
    it('keeps the built in layout without a matching extension', async () => {
        await render();
        expect(container.textContent).toBe('Built in');
        register({ name: 'server:details' });
        await render();
        expect(container.querySelector('section')).toBeNull();
    });

    it('passes props and the default layout, and restores it when disabled', async () => {
        register();
        await render({ componentProps: { label: 'Injected' } });
        expect(container.querySelector('section')?.textContent).toBe('InjectedBuilt in');
        await render({ disabled: true });
        expect(container.querySelector('section')).toBeNull();
        expect(container.textContent).toBe('Built in');
        state.data = [];
        await render();
        expect(container.textContent).toBe('Built in');
    });

    it('waits for the shared runtime before rendering extension modules', async () => {
        let ready!: () => void;
        state.runtime.mockReturnValueOnce(
            new Promise<void>((resolve) => {
                ready = resolve;
            }),
        );
        register();
        await render({ componentProps: { label: 'Injected' } });
        expect(container.textContent).toBe('Built in');
        await act(async () => ready());
        expect(container.querySelector('section')?.textContent).toBe('InjectedBuilt in');
    });

    it('keeps navigation available while a module is loading', async () => {
        register({ module: 'loading.js' });
        await render();
        expect(container.querySelector('button')?.textContent).toBe('Built in');
    });

    it('supports named exports and sidebar navigation children', async () => {
        register({ export: 'Named' });
        await render({ componentProps: { children: <a href='/'>Home</a> } });
        expect(container.querySelector('nav a')?.textContent).toBe('Home');
        expect(container.querySelector('button')).toBeNull();
    });

    it.each([{ egg_id: 7 }, { eggIds: [7] }, { nestId: 9 }, { nest_ids: [9] }])(
        'only injects matching server contexts: %j',
        async (guard) => {
            register(guard);
            await render({ context: { eggId: 2, nestId: 3 } });
            expect(container.querySelector('section')).toBeNull();
            await render({ context: { eggId: 7, nestId: 9 } });
            expect(container.querySelector('section')).not.toBeNull();
        },
    );

    it('selects the lowest order and preserves declaration order on ties', async () => {
        register();
        state.data[0]!.frontend!.layouts!.push({
            name: 'dashboard:sidebar',
            module: 'layout.js',
            export: 'Named',
            order: -1,
        });
        await render();
        expect(container.querySelector('nav')).not.toBeNull();
        state.data[0]!.frontend!.layouts![1]!.order = 0;
        await render();
        expect(container.querySelector('section')).not.toBeNull();
    });

    it.each([{ export: 'Missing' }, { export: 'Broken' }, { module: 'failed.js' }])(
        'falls back after module or render failures and recovers on replacement: %j',
        async (failure) => {
            vi.spyOn(console, 'error').mockImplementation(() => {});
            register(failure);
            await render();
            expect(container.textContent).toBe('Built in');
            register({ export: 'Named' });
            await render();
            expect(container.querySelector('nav')).not.toBeNull();
        },
    );
});
