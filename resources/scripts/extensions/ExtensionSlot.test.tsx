import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { ExtensionSlotDefinition } from '@/extensions/types';
import { ExtensionSlot } from './ExtensionSlot';

const mocks = vi.hoisted(() => ({ slots: [] as ExtensionSlotDefinition[] }));

vi.mock('@/extensions/useExtensions', () => ({
    useExtensions: () => ({ data: [{ id: 'guard-test', frontend: { slots: mocks.slots } }] }),
}));
vi.mock('@/extensions/ExtensionModule', () => ({
    ExtensionModule: ({ modulePath }: { modulePath: string }) => <div>{modulePath}</div>,
}));

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.slots = [];
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
});

const renderSlot = async (guard: Partial<ExtensionSlotDefinition>, eggId?: number, nestId?: number) => {
    mocks.slots = [{ name: 'server:router:above', module: 'restricted-content', ...guard }];
    await act(async () => root.render(<ExtensionSlot name='server:router:above' context={{ eggId, nestId }} />));
};

describe('extension slot egg guards', () => {
    it.each([{ eggId: 1 }, { eggIds: [1, 3] }, { egg_id: 1 }, { egg_ids: [1, 3] }])(
        'renders only for matching eggs with %j',
        async (guard) => {
            await renderSlot(guard, 1);
            expect(container.textContent).toBe('restricted-content');

            await renderSlot(guard, 2);
            expect(container.textContent).toBe('');

            await renderSlot(guard);
            expect(container.textContent).toBe('');
        },
    );

    it.each([{ eggIds: [] }, { egg_ids: [] }])('hides slots with an empty egg list %j', async (guard) => {
        await renderSlot(guard, 1);
        expect(container.textContent).toBe('');
    });

    it.each([
        { eggId: 1, egg_id: 2 },
        { eggIds: [1], egg_ids: [2] },
    ])('uses camelCase precedence like extension routes with %j', async (guard) => {
        await renderSlot(guard, 1);
        expect(container.textContent).toBe('restricted-content');
        await renderSlot(guard, 2);
        expect(container.textContent).toBe('');
    });

    it('keeps unrestricted slots visible without a server context', async () => {
        await renderSlot({});
        expect(container.textContent).toBe('restricted-content');
    });

    it.each([{ nest_id: 4 }, { nest_ids: [4] }])('preserves alternative snake_case nest matches %j', async (guard) => {
        await renderSlot({ egg_id: 1, ...guard }, 2, 4);
        expect(container.textContent).toBe('restricted-content');
        await renderSlot({ egg_id: 1, ...guard }, 2, 5);
        expect(container.textContent).toBe('');
    });
});
