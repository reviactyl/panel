import { execFileSync } from 'node:child_process';
import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createInstance } from 'i18next';
import { I18nextProvider } from 'react-i18next';
import { format, parse } from 'date-fns';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
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

vi.mock('@/api/account/getSocialLogins', () => ({
    default: () =>
        Promise.resolve([
            { id: 1, provider: 'github', createdAt: new Date(2026, 0, 23), updatedAt: new Date(2026, 0, 23) },
        ]),
}));
vi.mock('@/reviactyl/elements/dialog', () => ({ Dialog: { Confirm: () => null } }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/TitledGreyBox', () => ({
    default: ({ children }: { children: React.ReactNode }) => <section>{children}</section>,
}));

let container: HTMLDivElement;
let root: Root;
let socialSettings: typeof window.SocialLoginConfiguration;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    socialSettings = window.SocialLoginConfiguration;
    window.SocialLoginConfiguration = { github: true, google: false, discord: false };
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    window.SocialLoginConfiguration = socialSettings;
});

describe('linked social-account dates', () => {
    it.each(Object.entries(accounts))('renders a linked provider in %s', async (locale, account) => {
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
                <I18nextProvider i18n={i18n}>
                    <SocialLoginsContainer />
                </I18nextProvider>,
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
