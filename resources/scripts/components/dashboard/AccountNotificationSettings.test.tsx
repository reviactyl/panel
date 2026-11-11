import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { SWRConfig, useSWRConfig } from 'swr';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import http from '@/api/http';
import AccountNotificationSettings from './AccountNotificationSettings';

vi.mock('@/api/http', () => ({ default: { get: vi.fn(), put: vi.fn() } }));
vi.mock('react-i18next', () => ({
    useTranslation: () => ({ t: (key: string, fallback?: string) => fallback ?? key }),
}));

const settings = () => ({
    categories: [{ category: 'backups', database: true, email: false, emailAvailable: true }],
});

let container: HTMLDivElement;
let root: Root;
let revalidate: () => Promise<unknown>;

const Revalidate = () => {
    const { mutate } = useSWRConfig();
    revalidate = () => mutate('/api/client/account/notifications');
    return null;
};

const deferred = <T,>() => {
    let resolve!: (value: T) => void;
    const promise = new Promise<T>((done) => (resolve = done));
    return { promise, resolve };
};

beforeEach(() => {
    vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
    vi.mocked(http.get).mockReset();
    vi.mocked(http.put).mockReset();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.unstubAllGlobals();
});

const render = async () => {
    await act(async () =>
        root.render(
            <SWRConfig value={{ provider: () => new Map(), dedupingInterval: 0, shouldRetryOnError: false }}>
                <AccountNotificationSettings />
                <Revalidate />
            </SWRConfig>,
        ),
    );
};

it('shows loading and recovers from a failed load with Retry', async () => {
    let reject!: (error: Error) => void;
    vi.mocked(http.get).mockReturnValueOnce(new Promise((_, fail) => (reject = fail)));
    await render();
    expect(container.querySelector('[role="status"]')?.textContent).toContain('Loading');
    await act(async () => reject(new Error('Offline')));
    expect(container.querySelector('[role="alert"]')?.textContent).toContain('could not be loaded');
    vi.mocked(http.get).mockResolvedValueOnce({ data: settings() });
    await act(async () => container.querySelector('button')!.click());
    expect(container.querySelector('[role="alert"]')).toBeNull();
    expect(container.querySelectorAll('input[type="checkbox"]')).toHaveLength(2);
    expect(container.querySelector('details')).toBeNull();
});

it('rolls back a failed save, then saves the account category on retry without server preferences', async () => {
    vi.mocked(http.get).mockResolvedValue({ data: settings() });
    vi.mocked(http.put)
        .mockRejectedValueOnce(new Error('Offline'))
        .mockResolvedValueOnce({ data: { category: 'backups', database: false, email: false } });
    await render();
    const input = container.querySelector<HTMLInputElement>('input')!;
    await act(async () => input.click());
    expect(input.checked).toBe(true);
    expect(container.querySelector('[role="alert"]')).not.toBeNull();
    await act(async () => container.querySelector('button')!.click());
    expect(http.put).toHaveBeenCalledTimes(2);
    expect(input.checked).toBe(false);
    expect(container.querySelector('[role="alert"]')).toBeNull();
    expect(http.put).toHaveBeenLastCalledWith('/api/client/account/notifications', {
        category: 'backups',
        database: false,
        email: false,
    });
});

it.each(['before', 'after'])('keeps the confirmed choice when a stale refresh returns %s the save', async (order) => {
    const save = deferred<{ data: { category: string; database: boolean; email: boolean } }>();
    const refresh = deferred<{ data: ReturnType<typeof settings> }>();
    vi.mocked(http.get).mockResolvedValueOnce({ data: settings() }).mockReturnValueOnce(refresh.promise);
    vi.mocked(http.put).mockReturnValueOnce(save.promise);
    await render();
    const input = container.querySelector<HTMLInputElement>('input')!;
    await act(async () => input.click());
    expect(input.checked).toBe(false);
    expect(input.disabled).toBe(true);
    let revalidation!: Promise<unknown>;
    await act(async () => {
        revalidation = revalidate();
    });
    expect(http.get).toHaveBeenCalledTimes(2);
    const finishRefresh = async () => {
        await act(async () => {
            refresh.resolve({ data: settings() });
            await revalidation;
        });
        expect(input.checked).toBe(false);
    };
    if (order === 'before') await finishRefresh();
    await act(async () => save.resolve({ data: { category: 'backups', database: false, email: false } }));
    expect(input.checked).toBe(false);
    expect(input.disabled).toBe(false);
    if (order === 'after') await finishRefresh();
});
