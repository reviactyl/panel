import { act, type ComponentProps, type ReactNode } from 'react';
import { createStore, StoreProvider } from 'easy-peasy';
import { createRoot, type Root } from 'react-dom/client';
import { fireEvent, getByLabelText, getByRole, queryByRole } from '@testing-library/dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { SWRConfig, useSWRConfig } from 'swr';
import flashes, { type FlashStore } from '@/state/flashes';
import { CreatePasskeysForm, ListPasskeysForm } from './PasskeysForm';

const mocks = vi.hoisted(() => ({
    get: vi.fn(),
    remove: vi.fn(),
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/api/account/passkeys', () => ({
    getAccountPasskeys: mocks.get,
    deleteAccountPasskey: mocks.remove,
    registerAccountPasskey: vi.fn(),
}));
vi.mock('framer-motion', async () => {
    const actual = await vi.importActual<typeof import('framer-motion')>('framer-motion');
    const { forwardRef } = await import('react');
    const Div = forwardRef<HTMLDivElement, ComponentProps<typeof actual.motion.div>>(
        (
            { initial: _initial, animate: _animate, exit: _exit, variants: _variants, onUpdate: _onUpdate, ...props },
            ref,
        ) => <div {...(props as ComponentProps<'div'>)} ref={ref} />,
    );
    return {
        ...actual,
        motion: { ...actual.motion, div: Div },
        AnimatePresence: ({ children }: { children: ReactNode }) => children,
    };
});

const passkey = { id: 'key-id', name: 'Laptop', authenticator: null, createdAt: new Date(), updatedAt: new Date() };
const deferred = () => {
    let resolve!: () => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<void>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
};
let container: HTMLDivElement;
let root: Root;
let revalidate: () => Promise<unknown>;
let store: ReturnType<typeof createStore<{ flashes: FlashStore }>>;
const Harness = ({ children }: { children: ReactNode }) => {
    const { mutate } = useSWRConfig();
    revalidate = () => mutate('/api/client/account/passkeys');
    return children;
};
const render = async (children: ReactNode = <ListPasskeysForm />) => {
    await act(async () =>
        root.render(
            <SWRConfig value={{ provider: () => new Map(), dedupingInterval: 0, shouldRetryOnError: false }}>
                <StoreProvider store={store}>
                    <Harness>{children}</Harness>
                </StoreProvider>
            </SWRConfig>,
        ),
    );
};
beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.resetAllMocks();
    store = createStore({ flashes });
    mocks.get.mockResolvedValue([passkey]);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

describe('passkey deletion', () => {
    it.each(['Wrong password', 'Network Error'])(
        'keeps the dialog and password after %s, then allows retry',
        async (message) => {
            const request = deferred();
            mocks.remove.mockReturnValueOnce(request.promise).mockResolvedValueOnce(undefined);
            await render();
            await act(async () => getByRole(container, 'button', { name: 'passkeys.remove: Laptop' }).click());
            const dialog = getByRole(document.body, 'dialog');
            const password = getByLabelText(dialog, 'passkeys.password') as HTMLInputElement;
            await act(async () => fireEvent.change(password, { target: { value: 'incorrect' } }));
            const confirm = getByRole(dialog, 'button', { name: 'passkeys.remove' }) as HTMLButtonElement;
            await act(async () => confirm.click());
            expect(queryByRole(document.body, 'dialog')).not.toBeNull();
            expect(confirm.disabled).toBe(true);
            expect((getByRole(dialog, 'button', { name: /Cancel|strings:cancel/ }) as HTMLButtonElement).disabled).toBe(
                true,
            );
            expect(password.disabled).toBe(true);
            await act(async () => confirm.click());
            expect(mocks.remove).toHaveBeenCalledTimes(1);
            const error = new Error(message);
            await act(async () => request.reject(error));
            expect(getByRole(document.body, 'dialog').textContent).toContain(message);
            expect(getByRole(document.body, 'dialog').textContent).toContain('Laptop');
            expect(password.value).toBe('incorrect');
            expect(confirm.disabled).toBe(false);
            expect(store.getState().flashes.items).toEqual(
                expect.arrayContaining([expect.objectContaining({ key: 'account:passkeys:delete', message })]),
            );
            await act(async () => fireEvent.change(password, { target: { value: 'correct' } }));
            await act(async () => confirm.click());
            expect(mocks.remove).toHaveBeenLastCalledWith('key-id', 'correct');
            expect(queryByRole(document.body, 'dialog')).toBeNull();
        },
    );
});

it.each([
    ['list', ListPasskeysForm],
    ['registration', CreatePasskeysForm],
] as const)('clears a failed list load after successful revalidation in %s', async (_, Form) => {
    const error = new Error('Load failed');
    mocks.get.mockRejectedValueOnce(error).mockResolvedValue([passkey]);
    await render(<Form />);
    expect(store.getState().flashes.items).toEqual(
        expect.arrayContaining([expect.objectContaining({ key: 'account:passkeys', message: 'Load failed' })]),
    );
    await act(async () => {
        await revalidate();
    });
    expect(store.getState().flashes.items).toHaveLength(0);
});
