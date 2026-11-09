import { execFileSync } from 'node:child_process';
import { createInstance } from 'i18next';
import { I18nextProvider } from 'react-i18next';
import { format, parse } from 'date-fns';
import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { getByRole, queryByRole } from '@testing-library/dom';
import flashes from '@/state/flashes';
import SocialLoginsContainer from './SocialLoginsContainer';

const accounts: Record<string, { overview: { social: { date_format: string; status: { connected: string } } } }> =
    JSON.parse(
        execFileSync(
            'php',
            [
                '-r',
                'foreach (glob("lang/*", GLOB_ONLYDIR) as $path) { $accounts[basename($path)] = require "$path/dashboard/account.php"; } echo json_encode($accounts, JSON_THROW_ON_ERROR);',
            ],
            { encoding: 'utf8' },
        ),
    );

const mocks = vi.hoisted(() => ({ load: vi.fn(), unlink: vi.fn() }));
vi.mock('@/api/account/getSocialLogins', () => ({ default: mocks.load }));
vi.mock('@/api/account/unlinkSocialLogin', () => ({ default: mocks.unlink }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/dialog', () => ({
    Dialog: {
        Confirm: ({ open, onConfirmed }: { open: boolean; onConfirmed: () => void }) =>
            open ? <button onClick={onConfirmed}>Confirm unlink</button> : null,
    },
}));

const login = { id: 1, provider: 'google', createdAt: new Date('2026-01-01'), updatedAt: new Date('2026-01-01') };
let container: HTMLDivElement;
let root: Root;
let store: ReturnType<typeof createFlashStore>;
const createFlashStore = () => createStore({ flashes });
const mount = async () => {
    const i18n = createInstance();
    await i18n.init({
        lng: 'en',
        resources: {
            en: {
                'dashboard/account': { overview: { social: { date_format: 'yyyy-MM-dd' } } },
                notifications: { retry: 'Retry' },
            },
        },
        defaultNS: 'dashboard/account',
        interpolation: { escapeValue: false },
    });
    await act(async () => {
        root.render(
            <StoreProvider store={store}>
                <I18nextProvider i18n={i18n}>
                    <SocialLoginsContainer />
                </I18nextProvider>
            </StoreProvider>,
        );
    });
};
const click = async (name: string) => {
    await act(async () => getByRole(container, 'button', { name }).click());
};
const unrelatedFlash = { key: 'account:email', type: 'error' as const, message: 'Email update failed.' };

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    window.SocialLoginConfiguration = { google: true, discord: true, github: false };
    mocks.load.mockReset().mockResolvedValue([login]);
    mocks.unlink.mockReset().mockResolvedValue(undefined);
    vi.spyOn(console, 'error').mockImplementation(() => undefined);
    store = createFlashStore();
    store.getActions().flashes.addFlash(unrelatedFlash);
    container = document.createElement('div');
    document.body.append(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    delete window.SocialLoginConfiguration;
    vi.restoreAllMocks();
});

describe('social login failures', () => {
    it('does not load or change account errors when social login is disabled', async () => {
        window.SocialLoginConfiguration = { google: false, discord: false, github: false };
        await mount();

        expect(mocks.load).not.toHaveBeenCalled();
        expect(container.textContent).toBe('');
        expect(store.getState().flashes.items).toEqual([unrelatedFlash]);
    });

    it('shows a rejected load and retries without displaying unknown connections as disconnected', async () => {
        mocks.load.mockRejectedValueOnce(new Error('Could not load social connections.'));
        await mount();

        expect(getByRole(container, 'alert').textContent).toContain('Could not load social connections.');
        expect(container.querySelector('a[href="/auth/login/google"]')).toBeNull();
        expect(container.textContent).not.toContain('overview.social.status.not-connected');
        expect(store.getState().flashes.items).toContainEqual(unrelatedFlash);

        await click('Retry');

        expect(mocks.load).toHaveBeenCalledTimes(2);
        expect(queryByRole(container, 'alert')).toBeNull();
        expect(container.textContent).toContain('overview.social.status.connected');
        expect(container.querySelector('a[href="/auth/login/discord"]')).not.toBeNull();
        expect(store.getState().flashes.items).toEqual([unrelatedFlash]);
    });

    it('replaces repeated load errors and recovers when the service returns', async () => {
        mocks.load
            .mockRejectedValueOnce(new Error('First load failed.'))
            .mockRejectedValueOnce(new Error('Retry failed.'));
        await mount();
        await click('Retry');

        expect(getByRole(container, 'alert').textContent).toContain('Retry failed.');
        expect(container.textContent).not.toContain('First load failed.');
        expect(store.getState().flashes.items).toContainEqual(unrelatedFlash);

        await click('Retry');
        expect(queryByRole(container, 'alert')).toBeNull();
        expect(mocks.load).toHaveBeenCalledTimes(3);
    });

    it('preserves a linked provider after rejected unlink and allows a successful retry', async () => {
        mocks.unlink.mockRejectedValueOnce({
            response: { data: { errors: [{ detail: 'Could not unlink Google.' }] } },
        });
        await mount();
        await click('overview.social.actions.unlink');
        await click('Confirm unlink');

        expect(getByRole(container, 'alert').textContent).toContain('Could not unlink Google.');
        expect(container.textContent).toContain('overview.social.status.connected');
        expect(container.querySelector('a[href="/auth/login/google"]')).toBeNull();
        expect(queryByRole(container, 'button', { name: 'Confirm unlink' })).toBeNull();
        expect(store.getState().flashes.items).toContainEqual(unrelatedFlash);

        await click('overview.social.actions.unlink');
        await click('Confirm unlink');

        expect(mocks.unlink.mock.calls).toEqual([['google'], ['google']]);
        expect(queryByRole(container, 'alert')).toBeNull();
        expect(container.querySelector('a[href="/auth/login/google"]')).not.toBeNull();
        expect(store.getState().flashes.items).toEqual([unrelatedFlash]);
    });

    it('keeps successful unlink and link destinations unchanged', async () => {
        await mount();
        expect(container.querySelector('a[href="/auth/login/discord"]')).not.toBeNull();
        await click('overview.social.actions.unlink');
        await click('Confirm unlink');

        expect(mocks.unlink).toHaveBeenCalledWith('google');
        expect(container.querySelector('a[href="/auth/login/google"]')).not.toBeNull();
        expect(container.textContent).not.toContain('overview.social.status.connected');
        expect(store.getState().flashes.items).toEqual([unrelatedFlash]);
    });
});

describe('linked social-account dates', () => {
    it.each(Object.entries(accounts))('renders a linked provider in %s', async (locale, account) => {
        mocks.load.mockResolvedValue([{ ...login, provider: 'github', updatedAt: new Date(2026, 0, 23) }]);
        window.SocialLoginConfiguration = { github: true, google: false, discord: false };
        const i18n = createInstance();
        const translation = structuredClone(account);
        translation.overview.social.status.connected = translation.overview.social.status.connected.replace(
            ':date',
            '{{date}}',
        );
        await i18n.init({
            lng: locale,
            resources: { [locale]: { 'dashboard/account': translation } },
            interpolation: { escapeValue: false },
        });
        await act(async () =>
            root.render(
                <StoreProvider store={store}>
                    <I18nextProvider i18n={i18n}>
                        <SocialLoginsContainer />
                    </I18nextProvider>
                </StoreProvider>,
            ),
        );

        const pattern = account.overview.social.date_format;
        const date = format(new Date(2026, 0, 23), pattern);
        const parsed = parse(date, pattern, new Date(2000, 5, 1));
        expect([parsed.getFullYear(), parsed.getMonth(), parsed.getDate()]).toEqual([2026, 0, 23]);
        expect(container.textContent).toContain('github');
        expect(container.textContent).toContain(date);
        expect(container.textContent).toContain(i18n.t('dashboard/account:overview.social.status.connected', { date }));
        expect(container.querySelectorAll('button')).toHaveLength(1);
    });
});
