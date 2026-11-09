import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import getApiKeys, { type ApiKey } from '@/api/account/getApiKeys';
import flashes from '@/state/flashes';
import AccountApiContainer from './AccountApiContainer';

const key: ApiKey = {
    identifier: 'demo-key',
    description: 'Recovered API key',
    allowedIps: [],
    createdAt: null,
    lastUsedAt: null,
};

vi.mock('@/api/account/getApiKeys', () => ({ default: vi.fn() }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/dashboard/AccountContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/elements/ContentBox', () => ({
    default: ({ children }: { children: ReactNode }) => <section>{children}</section>,
}));
vi.mock('@/components/dashboard/forms/CreateApiKeyForm', () => ({
    default: ({ onKeyCreated }: { onKeyCreated: (key: ApiKey) => void }) => (
        <button onClick={() => onKeyCreated(key)}>Create key</button>
    ),
}));
vi.mock('@/components/MessageBox', () => ({
    default: ({ children }: { children: ReactNode }) => <div role='alert'>{children}</div>,
}));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({
    default: ({ visible }: { visible: boolean }) => (visible ? <div data-testid='loading' /> : null),
}));
vi.mock('@/reviactyl/elements/dialog', () => ({ Dialog: { Confirm: () => null } }));

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.resetAllMocks();
    vi.spyOn(console, 'error').mockImplementation(() => undefined);
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    vi.restoreAllMocks();
});

const render = async () => {
    const store = createStore({ flashes });
    await act(async () => {
        root.render(
            <StoreProvider store={store}>
                <AccountApiContainer />
            </StoreProvider>,
        );
    });
    return store;
};

const retry = () =>
    Array.from(container.querySelectorAll('button')).find((button) => button.textContent === 'api.retry');

describe('API key list loading', () => {
    it('keeps a newly created key visible alongside retry after a load failure', async () => {
        vi.mocked(getApiKeys).mockRejectedValueOnce(new Error('List failed'));
        await render();
        await act(async () => container.querySelector<HTMLButtonElement>('button')!.click());
        expect(container.textContent).toContain(key.description);
        expect(container.textContent).toContain(key.identifier);
        expect(retry()).toBeDefined();
        expect(container.querySelectorAll('button')).toHaveLength(3);
    });

    it('preserves creation errors when a pending list retry succeeds', async () => {
        vi.mocked(getApiKeys).mockRejectedValueOnce(new Error('List failed'));
        const store = await render();
        let resolve!: (keys: ApiKey[]) => void;
        vi.mocked(getApiKeys).mockImplementationOnce(() => new Promise((done) => (resolve = done)));
        await act(async () => retry()!.click());
        await act(async () => store.getActions().flashes.addError({ key: 'account', message: 'Creation failed' }));
        await act(async () => resolve([]));
        expect(container.textContent).toContain('Creation failed');
        expect(container.textContent).not.toContain('List failed');
        expect(retry()).toBeUndefined();
    });

    it('clears the initial error spinner, shows the error, and recovers through retry', async () => {
        vi.mocked(getApiKeys).mockRejectedValueOnce(new Error('API keys unavailable'));
        await render();

        expect(container.querySelector('[data-testid="loading"]')).toBeNull();
        expect(container.querySelector('[role="alert"]')?.textContent).toContain('API keys unavailable');
        expect(container.textContent).not.toContain('api.not-exist');
        expect(retry()).toBeDefined();

        let resolve!: (keys: ApiKey[]) => void;
        vi.mocked(getApiKeys).mockImplementationOnce(() => new Promise((done) => (resolve = done)));
        await act(async () => retry()!.click());
        expect(container.querySelector('[data-testid="loading"]')).not.toBeNull();
        expect(retry()?.disabled ?? true).toBe(true);
        expect(getApiKeys).toHaveBeenCalledTimes(2);

        await act(async () => resolve([key]));
        expect(container.querySelector('[data-testid="loading"]')).toBeNull();
        expect(container.querySelector('[role="alert"]')).toBeNull();
        expect(container.textContent).toContain(key.description);
        expect(retry()).toBeUndefined();
    });

    it('settles another failed retry and allows retrying again to an empty list', async () => {
        vi.mocked(getApiKeys)
            .mockRejectedValueOnce(new Error('Initial failure'))
            .mockRejectedValueOnce(new Error('Retry failure'))
            .mockResolvedValueOnce([]);
        await render();
        expect(retry()).toBeDefined();

        await act(async () => retry()!.click());
        expect(container.querySelector('[data-testid="loading"]')).toBeNull();
        expect(container.querySelector('[role="alert"]')?.textContent).toContain('Retry failure');
        expect(retry()).toBeDefined();

        await act(async () => retry()!.click());
        expect(getApiKeys).toHaveBeenCalledTimes(3);
        expect(container.textContent).toContain('api.not-exist');
        expect(container.querySelector('[role="alert"]')).toBeNull();
        expect(retry()).toBeUndefined();
    });

    it('settles a successful initial request without showing retry', async () => {
        vi.mocked(getApiKeys).mockResolvedValueOnce([key]);
        await render();
        expect(container.querySelector('[data-testid="loading"]')).toBeNull();
        expect(container.textContent).toContain(key.description);
        expect(container.querySelector('[role="alert"]')).toBeNull();
        expect(retry()).toBeUndefined();
        expect(getApiKeys).toHaveBeenCalledTimes(1);
    });
});
