import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { SWRConfig } from 'swr';
import { fireEvent } from '@testing-library/dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import http from '@/api/http';
import flashes from '@/state/flashes';
import AccountSSHContainer from './AccountSSHContainer';

vi.mock('@/api/http', () => ({
    default: { get: vi.fn(), post: vi.fn() },
    httpErrorToHuman: (error: Error) => error.message,
}));
vi.mock('@/plugins/useSWRKey', () => ({ useUserSWRKey: () => 'test-ssh-keys' }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/dashboard/AccountContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/ContentBox', () => ({
    default: ({ children }: { children: ReactNode }) => <section>{children}</section>,
}));
vi.mock('@/components/MessageBox', () => ({
    default: ({ children }: { children: ReactNode }) => <div role='alert'>{children}</div>,
}));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({
    default: ({ visible }: { visible: boolean }) => (visible ? <div data-testid='loading' /> : null),
}));
vi.mock('@/reviactyl/elements/dialog', () => ({ Dialog: { Confirm: () => null } }));

const key = {
    name: 'Recovered SSH key',
    fingerprint: 'SHA256:test',
    public_key: 'ssh-ed25519 test',
    created_at: '2026-11-19T12:00:00Z',
};
const list = (keys = [key]) => ({ data: { data: keys.map((attributes) => ({ attributes })) } });
const deferred = <T,>() => {
    let resolve!: (value: T) => void;
    const promise = new Promise<T>((done) => (resolve = done));
    return { promise, resolve };
};

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.resetAllMocks();
    vi.spyOn(console, 'error').mockImplementation(() => undefined);
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
});

const render = async (retryOnError = false) => {
    const store = createStore({ flashes });
    await act(async () =>
        root.render(
            <StoreProvider store={store}>
                <SWRConfig
                    value={{
                        provider: () => new Map(),
                        dedupingInterval: 0,
                        shouldRetryOnError: retryOnError,
                        errorRetryCount: retryOnError ? 1 : 0,
                        errorRetryInterval: 0,
                    }}
                >
                    <AccountSSHContainer />
                </SWRConfig>
            </StoreProvider>,
        ),
    );
    return store;
};
const button = (text: string) =>
    Array.from(container.querySelectorAll('button')).find((element) => element.textContent === text);
const fillForm = async () => {
    await act(async () => {
        fireEvent.change(container.querySelector('input[name="name"]')!, { target: { value: key.name } });
        fireEvent.change(container.querySelector('textarea')!, { target: { value: key.public_key } });
    });
};

describe('SSH key submission and list recovery', () => {
    it('blocks repeated Save activation during a pending request and resets after success', async () => {
        vi.mocked(http.get).mockResolvedValue(list([]));
        const request = deferred<{ data: { attributes: typeof key } }>();
        vi.mocked(http.post).mockReturnValueOnce(request.promise);
        await render();
        await fillForm();
        const save = button('ssh.create.save')!;
        expect(save.getAttribute('type')).toBe('submit');
        await act(async () => save.click());
        expect(save.disabled).toBe(true);
        await act(async () => {
            save.click();
            save.click();
        });
        expect(http.post).toHaveBeenCalledTimes(1);
        expect(http.post).toHaveBeenCalledWith('/api/client/account/ssh-keys', {
            name: key.name,
            public_key: key.public_key,
        });
        await act(async () => request.resolve({ data: { attributes: key } }));
        expect(save.disabled).toBe(false);
        expect(container.querySelector<HTMLInputElement>('input[name="name"]')!.value).toBe('');
        expect(container.textContent).toContain(key.name);
    });

    it('preserves inputs and enables another save after failure', async () => {
        vi.mocked(http.get).mockResolvedValue(list([]));
        vi.mocked(http.post).mockRejectedValueOnce(new Error('Save failed'));
        await render();
        await fillForm();
        await act(async () => button('ssh.create.save')!.click());
        expect(button('ssh.create.save')!.disabled).toBe(false);
        expect(container.querySelector<HTMLInputElement>('input[name="name"]')!.value).toBe(key.name);
        expect(container.querySelector('[role="alert"]')?.textContent).toContain('Save failed');
        vi.mocked(http.post).mockResolvedValueOnce({ data: { attributes: key } });
        await act(async () => button('ssh.create.save')!.click());
        expect(http.post).toHaveBeenCalledTimes(2);
        expect(container.textContent).toContain(key.name);
    });

    it('retries a failed initial load and displays the recovered list', async () => {
        vi.mocked(http.get).mockRejectedValueOnce(new Error('List failed'));
        const store = await render();
        expect(container.querySelector('[data-testid="loading"]')).toBeNull();
        expect(container.querySelector('[role="alert"]')?.textContent).toContain('List failed');
        expect(container.textContent).not.toContain('ssh.not-exist');
        const retry = button('api.retry')!;
        expect(retry).toBeDefined();
        const request = deferred<ReturnType<typeof list>>();
        vi.mocked(http.get).mockReturnValueOnce(request.promise);
        await act(async () => retry.click());
        expect(retry.disabled).toBe(true);
        await act(async () => retry.click());
        expect(http.get).toHaveBeenCalledTimes(2);
        await act(async () => store.getActions().flashes.addError({ key: 'account', message: 'Creation failed' }));
        await act(async () => request.resolve(list()));
        expect(container.textContent).toContain(key.name);
        expect(container.textContent).toContain(key.fingerprint);
        expect(container.textContent).not.toContain('List failed');
        expect(container.textContent).toContain('Creation failed');
        expect(button('api.retry')).toBeUndefined();
    });

    it('allows another retry after failure and handles an empty recovered list', async () => {
        vi.mocked(http.get).mockRejectedValueOnce(new Error('Initial failure')).mockResolvedValueOnce(list([]));
        await render();
        await act(async () => button('api.retry')!.click());
        expect(http.get).toHaveBeenCalledTimes(2);
        expect(container.textContent).toContain('ssh.not-exist');
        expect(button('api.retry')).toBeUndefined();
        expect(container.querySelector('[role="alert"]')).toBeNull();
    });

    it('clears the error after an automatic revalidation succeeds', async () => {
        const recovery = deferred<ReturnType<typeof list>>();
        vi.mocked(http.get).mockRejectedValueOnce(new Error('Temporary failure')).mockReturnValueOnce(recovery.promise);
        await render(true);
        expect(button('api.retry')).toBeDefined();
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 0));
        });
        expect(http.get).toHaveBeenCalledTimes(2);
        await act(async () => recovery.resolve(list([])));
        expect(button('api.retry')).toBeUndefined();
        expect(container.textContent).toContain('ssh.not-exist');
        expect(container.querySelector('[role="alert"]')).toBeNull();
    });

    it('reloads the full list when Save finishes before the initial load', async () => {
        const initial = deferred<ReturnType<typeof list>>();
        const existing = { ...key, name: 'Existing SSH key', fingerprint: 'SHA256:existing' };
        vi.mocked(http.get)
            .mockReturnValueOnce(initial.promise)
            .mockResolvedValueOnce(list([existing, key]));
        vi.mocked(http.post).mockResolvedValueOnce({ data: { attributes: key } });
        await render();
        await fillForm();
        await act(async () => button('ssh.create.save')!.click());
        expect(http.get).toHaveBeenCalledTimes(2);
        await act(async () => initial.resolve(list([existing])));
        expect(container.textContent).toContain(existing.name);
        expect(container.textContent).toContain(key.name);
    });
});
