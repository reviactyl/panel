import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import user from '@/state/user';
import flashes from '@/state/flashes';
import UpdateEmailAddressForm from './UpdateEmailAddressForm';

const mocks = vi.hoisted(() => ({ updateEmail: vi.fn() }));
vi.mock('@/api/account/updateAccountEmail', () => ({ default: mocks.updateEmail }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));

let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof createAccountStore>;
const createAccountStore = () => createStore({ user, flashes });

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.updateEmail.mockReset().mockResolvedValue(undefined);
    store = createAccountStore();
    store.getActions().user.updateUserData({ email: 'before@example.test' });
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
    await act(async () =>
        root.render(
            <StoreProvider store={store}>
                <UpdateEmailAddressForm />
            </StoreProvider>,
        ),
    );
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

const input = (name: string) => container.querySelector<HTMLInputElement>(`input[name="${name}"]`)!;
const fill = async (name: string, value: string) => {
    await act(async () => {
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input(name), value);
        input(name).dispatchEvent(new Event('input', { bubbles: true }));
    });
};
const submit = async () => {
    await act(async () =>
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
    );
};

describe('account email form reset', () => {
    it('keeps the saved email, clears the password, and submits the saved address again', async () => {
        await fill('email', 'after@example.test');
        await fill('password', 'current-password');
        await submit();

        expect(mocks.updateEmail).toHaveBeenCalledWith('after@example.test', 'current-password');
        expect(store.getState().user.data?.email).toBe('after@example.test');
        expect(input('email').value).toBe('after@example.test');
        expect(input('password').value).toBe('');

        await fill('password', 'current-password');
        await submit();

        expect(mocks.updateEmail.mock.calls).toEqual([
            ['after@example.test', 'current-password'],
            ['after@example.test', 'current-password'],
        ]);
        expect(store.getState().user.data?.email).toBe('after@example.test');
        expect(input('email').value).toBe('after@example.test');
        expect(input('password').value).toBe('');
    });

    it('resets a failed update to the most recently saved email and allows a retry', async () => {
        await fill('email', 'after@example.test');
        await fill('password', 'current-password');
        await submit();

        mocks.updateEmail.mockRejectedValueOnce(new Error('Email update failed.'));
        await fill('email', 'failed@example.test');
        await fill('password', 'wrong-password');
        await submit();

        expect(store.getState().user.data?.email).toBe('after@example.test');
        expect(input('email').value).toBe('after@example.test');
        expect(input('password').value).toBe('');
        expect(store.getState().flashes.items).toEqual([
            expect.objectContaining({ key: 'account:email', type: 'error' }),
        ]);

        await fill('email', 'retried@example.test');
        await fill('password', 'current-password');
        await submit();

        expect(mocks.updateEmail).toHaveBeenLastCalledWith('retried@example.test', 'current-password');
        expect(input('email').value).toBe('retried@example.test');
        expect(input('password').value).toBe('');
    });
});
