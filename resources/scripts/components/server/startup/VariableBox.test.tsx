import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import type { ServerEggVariable } from '@/api/server/types';
import VariableBox from './VariableBox';

const mocks = vi.hoisted(() => ({
    update: vi.fn(),
    mutate: vi.fn(),
    canEdit: true,
    clearFlashes: vi.fn(),
    addError: vi.fn(),
}));
vi.mock('@/api/server/updateStartupVariable', () => ({ default: mocks.update }));
vi.mock('@/api/swr/getServerStartup', () => ({ default: () => ({ mutate: mocks.mutate }) }));
vi.mock('@/state/server', () => ({ ServerContext: { useStoreState: () => 'server-uuid' } }));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [mocks.canEdit] }));
vi.mock('@/plugins/useFlash', () => ({
    default: () => ({ clearFlashes: mocks.clearFlashes, clearAndAddHttpError: mocks.addError }),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/InputSpinner', () => ({
    default: ({ children, visible }: { children: ReactNode; visible: boolean }) => (
        <div data-loading={visible}>{children}</div>
    ),
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
    isSecret: false,
    hasValue: true,
    rules: ['required', 'in:true,false'],
};

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    mocks.update.mockReset().mockResolvedValue([variable, 'startup']);
    mocks.mutate.mockReset();
    mocks.canEdit = true;
    mocks.addError.mockReset();
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

const deferred = () => {
    let resolve!: (value: [ServerEggVariable, string]) => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<[ServerEggVariable, string]>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
};

describe('startup save response ordering', () => {
    it('keeps the latest variable and invocation when responses arrive in reverse order', async () => {
        const older = deferred();
        const newer = deferred();
        mocks.update.mockReset().mockReturnValueOnce(older.promise).mockReturnValueOnce(newer.promise);
        let cached = { variables: [variable], invocation: 'initial' };
        mocks.mutate.mockImplementation((updater) => {
            cached = updater ? updater(cached) : { ...cached, invocation: 'newer' };
        });
        const input = await render({ serverValue: 'false' });
        await toggle(input);
        await toggle(input);
        await act(async () => newer.resolve([{ ...variable, serverValue: 'false' }, 'newer']));
        await act(async () => older.resolve([{ ...variable, serverValue: 'true' }, 'older']));
        expect(input.checked).toBe(false);
        expect(cached.variables[0]?.serverValue).toBe('false');
        expect(cached.invocation).toBe('newer');
    });

    it('refreshes the shared command when different variables respond in reverse order', async () => {
        const first = deferred();
        const second = deferred();
        const otherVariable = { ...variable, envVariable: 'OTHER_VALUE', serverValue: 'false' };
        let cached = { variables: [{ ...variable, serverValue: 'false' }, otherVariable], invocation: 'false false' };
        mocks.update.mockReset().mockReturnValueOnce(first.promise).mockReturnValueOnce(second.promise);
        mocks.mutate.mockImplementation((updater) => {
            cached = updater ? updater(cached) : { ...cached, invocation: 'true true' };
        });
        await act(async () =>
            root.render(
                <>
                    <VariableBox variable={{ ...variable, serverValue: 'false' }} />
                    <VariableBox variable={otherVariable} />
                </>,
            ),
        );
        const inputs = container.querySelectorAll<HTMLInputElement>('input[type="checkbox"]');
        await toggle(inputs[0]!);
        await toggle(inputs[1]!);
        await act(async () => second.resolve([{ ...otherVariable, serverValue: 'true' }, 'true true']));
        await act(async () => first.resolve([{ ...variable, serverValue: 'true' }, 'true false']));
        expect(cached.variables.map((v) => v.serverValue)).toEqual(['true', 'true']);
        expect(cached.invocation).toBe('true true');
        expect(mocks.mutate.mock.calls.filter((args) => args.length === 0)).toHaveLength(2);
    });

    it('does not clear loading or show errors for an obsolete save', async () => {
        const older = deferred();
        const newer = deferred();
        mocks.update.mockReset().mockReturnValueOnce(older.promise).mockReturnValueOnce(newer.promise);
        const input = await render({ serverValue: 'false' });
        await toggle(input);
        await toggle(input);
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => undefined);
        await act(async () => older.reject(new Error('obsolete')));
        expect(mocks.addError).not.toHaveBeenCalled();
        expect(container.querySelector('[data-loading]')?.getAttribute('data-loading')).toBe('true');
        await act(async () => newer.resolve([variable, 'newer']));
        expect(container.querySelector('[data-loading]')?.getAttribute('data-loading')).toBe('false');
        consoleError.mockRestore();
    });

    it('invalidates a response as soon as another edit is waiting for debounce', async () => {
        const older = deferred();
        mocks.update.mockReset().mockReturnValueOnce(older.promise).mockResolvedValue([variable, 'newer']);
        const input = await render({ serverValue: 'false' });
        await toggle(input);
        await act(async () => input.click());
        await act(async () => older.resolve([{ ...variable, serverValue: 'true' }, 'older']));
        expect(mocks.mutate).not.toHaveBeenCalled();
        await act(async () => vi.advanceTimersByTimeAsync(500));
        expect(mocks.update).toHaveBeenCalledTimes(2);
    });

    it('coalesces edits across rerenders and reports the current save error', async () => {
        const pending = deferred();
        mocks.update.mockReset().mockReturnValue(pending.promise);
        const input = await render({ serverValue: 'false' });
        await act(async () => input.click());
        await act(async () => vi.advanceTimersByTimeAsync(250));
        await render({ serverValue: 'false', description: 'Updated description' });
        await act(async () => input.click());
        await act(async () => vi.advanceTimersByTimeAsync(250));
        expect(mocks.update).not.toHaveBeenCalled();
        await act(async () => vi.advanceTimersByTimeAsync(250));
        expect(mocks.update).toHaveBeenCalledTimes(1);
        expect(mocks.update).toHaveBeenCalledWith('server-uuid', 'BOOLEAN_VALUE', 'false');
        const error = new Error('current');
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => undefined);
        await act(async () => pending.reject(error));
        expect(mocks.addError).toHaveBeenCalledWith({ error, key: 'server:startup:BOOLEAN_VALUE' });
        expect(container.querySelector('[data-loading]')?.getAttribute('data-loading')).toBe('false');
        consoleError.mockRestore();
    });

    it('cancels pending debounced saves on unmount', async () => {
        const input = await render({ serverValue: 'false' });
        await act(async () => input.click());
        await act(async () => root.render(null));
        await act(async () => vi.advanceTimersByTimeAsync(500));
        expect(mocks.update).not.toHaveBeenCalled();
    });
});

describe('secret variables', () => {
    it('masks saved secrets and clears them without sending the placeholder', async () => {
        await render({ isSecret: true, hasValue: true, defaultValue: '', serverValue: null });
        const input = container.querySelector<HTMLInputElement>('input')!;
        expect(input.type).toBe('password');
        expect(input.value).toBe('');
        expect(input.placeholder).toBe('••••••••••••');
        expect(container.textContent).toContain('sensitive');
        expect(mocks.update).not.toHaveBeenCalled();
        await act(async () => container.querySelector<HTMLButtonElement>('button')!.click());
        await act(async () => vi.advanceTimersByTimeAsync(500));
        expect(mocks.update).toHaveBeenCalledWith('server-uuid', 'BOOLEAN_VALUE', '');
    });

    it('keeps a partially typed secret after autosave so typing can continue', async () => {
        await render({ isSecret: true, defaultValue: '', serverValue: null });
        const input = container.querySelector<HTMLInputElement>('input')!;
        const setValue = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!;
        const type = async (value: string) => {
            await act(async () => {
                setValue.call(input, value);
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
            await act(async () => vi.advanceTimersByTimeAsync(500));
        };
        await type('first-part');
        expect(input.value).toBe('first-part');
        await type('first-part-complete');
        expect(mocks.update).toHaveBeenLastCalledWith('server-uuid', 'BOOLEAN_VALUE', 'first-part-complete');
    });

    it.each(['permission', 'variable'])('prevents secret edits without %s access', async (restriction) => {
        mocks.canEdit = restriction !== 'permission';
        await render({ isSecret: true, isEditable: restriction !== 'variable' });
        expect(container.querySelector<HTMLInputElement>('input')!.readOnly).toBe(true);
        const clear = container.querySelector<HTMLButtonElement>('button')!;
        expect(clear.disabled).toBe(true);
        await act(async () => clear.click());
        await act(async () => vi.advanceTimersByTimeAsync(500));
        expect(mocks.update).not.toHaveBeenCalled();
    });
});
