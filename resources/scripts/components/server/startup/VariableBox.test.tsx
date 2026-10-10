import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { ServerEggVariable } from '@/api/server/types';
import VariableBox from './VariableBox';

const mocks = vi.hoisted(() => ({ update: vi.fn(), mutate: vi.fn(), canEdit: true }));
vi.mock('@/api/server/updateStartupVariable', () => ({ default: mocks.update }));
vi.mock('@/api/swr/getServerStartup', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/state/server', () => ({ ServerContext: { useStoreState: () => 'server-uuid' } }));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [mocks.canEdit] }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: vi.fn(), clearAndAddHttpError: vi.fn() }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/InputSpinner', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));

let container: HTMLDivElement;
let root: Root;
const variable: ServerEggVariable = {
    name: 'Boolean variable',
    description: '',
    envVariable: 'BOOLEAN_VALUE',
    defaultValue: 'false',
    serverValue: null,
    isEditable: true,
    rules: ['required', 'in:true,false'],
};

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    mocks.update.mockReset().mockResolvedValue([variable, 'startup']);
    mocks.mutate.mockReset();
    mocks.canEdit = true;
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.useRealTimers();
});
const render = async (overrides: Partial<ServerEggVariable>) => {
    await act(async () => root.render(<VariableBox variable={{ ...variable, ...overrides }} />));
    return container.querySelector<HTMLInputElement>('input[type="checkbox"]')!;
};
const toggle = async (input: HTMLInputElement) => {
    await act(async () => input.click());
    await act(async () => vi.advanceTimersByTimeAsync(500));
};

describe('startup boolean switch representation', () => {
    it.each([
        ['in:true,false', 'true', 'false'],
        ['in:false,true', 'true', 'false'],
        ['string|in:true,false', 'true', 'false'],
        ['string|in:false,true', 'true', 'false'],
        ['in:0,1', '1', '0'],
        ['in:1,0', '1', '0'],
        ['string|in:0,1', '1', '0'],
        ['string|in:1,0', '1', '0'],
        ['boolean', '1', '0'],
        ['string|boolean', '1', '0'],
    ])('displays and submits the allowed values for %s', async (rules, enabled, disabled) => {
        const input = await render({ rules: rules.split('|'), defaultValue: disabled, serverValue: enabled });
        expect(input.checked).toBe(true);
        await toggle(input);
        expect(mocks.update).toHaveBeenLastCalledWith('server-uuid', 'BOOLEAN_VALUE', disabled);
        await toggle(input);
        expect(mocks.update).toHaveBeenLastCalledWith('server-uuid', 'BOOLEAN_VALUE', enabled);
    });

    it.each([
        ['in:true,false', 'true', true],
        ['in:false,true', 'false', false],
        ['string|in:0,1', '1', true],
        ['boolean', '0', false],
    ])('uses the default when no server value exists (%s, %s)', async (rules, defaultValue, checked) => {
        const input = await render({ rules: rules.split('|'), defaultValue, serverValue: null });
        expect(input.checked).toBe(checked);
        expect(mocks.update).not.toHaveBeenCalled();
    });

    it('preserves an empty server value instead of falling back to an enabled default', async () => {
        const input = await render({ defaultValue: 'true', serverValue: '' });
        expect(input.checked).toBe(false);
    });

    it.each(['permission', 'variable'])('prevents editing when the %s is read-only', async (restriction) => {
        mocks.canEdit = restriction !== 'permission';
        const input = await render({ isEditable: restriction !== 'variable', serverValue: 'true' });
        expect(input.checked).toBe(true);
        expect(input.disabled).toBe(true);
        await toggle(input);
        expect(mocks.update).not.toHaveBeenCalled();
    });
});
