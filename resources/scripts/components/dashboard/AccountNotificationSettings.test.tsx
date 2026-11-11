import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { SWRConfig } from 'swr';
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
    vi.mocked(http.put).mockRejectedValueOnce(new Error('Offline')).mockResolvedValueOnce({});
    await render();
    const input = container.querySelector<HTMLInputElement>('input')!;
    await act(async () => input.click());
    expect(input.checked).toBe(true);
    expect(container.querySelector('[role="alert"]')).not.toBeNull();
    await act(async () => input.click());
    expect(input.checked).toBe(false);
    expect(container.querySelector('[role="alert"]')).toBeNull();
    expect(http.put).toHaveBeenLastCalledWith('/api/client/account/notifications', {
        category: 'backups',
        database: false,
        email: false,
    });
});
