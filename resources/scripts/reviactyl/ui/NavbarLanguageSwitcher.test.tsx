import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { getByRole } from '@testing-library/dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import NavbarLanguageSwitcher from './NavbarLanguageSwitcher';

const mocks = vi.hoisted(() => ({
    state: { user: { data: { language: 'en' } }, settings: { data: { locale: 'en' } } },
    updateUserData: vi.fn(),
    updateAccountLanguage: vi.fn(),
    changeLanguage: vi.fn(),
    on: vi.fn(),
    off: vi.fn(),
}));

vi.mock('easy-peasy', () => ({
    useStoreState: (select: (state: typeof mocks.state) => unknown) => select(mocks.state),
    useStoreActions: (select: (actions: unknown) => unknown) =>
        select({ user: { updateUserData: mocks.updateUserData } }),
}));
vi.mock('@/api/account/updateAccountLanguage', () => ({ default: mocks.updateAccountLanguage }));
vi.mock('@/i18n', () => ({
    default: { changeLanguage: mocks.changeLanguage, on: mocks.on, off: mocks.off },
}));
vi.mock('react-i18next', () => ({
    useTranslation: () => ({ t: (key: string) => (key === 'overview.language' ? 'Panel Language' : key) }),
}));

const languages = { en: { name: 'English', flag: 'us' }, fr: { name: 'Français', flag: 'fr' } };

describe('navbar language trigger accessibility', () => {
    let container: HTMLDivElement;
    let root: Root;

    beforeEach(() => {
        vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
        vi.clearAllMocks();
        mocks.state.user.data.language = 'en';
        mocks.state.settings.data.locale = 'en';
        mocks.updateAccountLanguage.mockResolvedValue(undefined);
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({ json: async () => languages }));
        container = document.createElement('div');
        document.body.appendChild(container);
        root = createRoot(container);
    });

    afterEach(async () => {
        await act(async () => root.unmount());
        container.remove();
        vi.unstubAllGlobals();
    });

    const render = async () => act(async () => root.render(<NavbarLanguageSwitcher />));
    const trigger = () => getByRole<HTMLButtonElement>(container, 'button', { name: 'Panel Language: English' });

    it('is named before the language list loads', async () => {
        vi.mocked(fetch).mockReturnValue(new Promise(() => {}));
        await render();
        const button = getByRole(container, 'button', { name: 'Panel Language: en' });
        expect(button.getAttribute('aria-expanded')).toBe('false');
    });

    it('exposes the current language and expanded state through toggle and outside dismissal', async () => {
        await render();
        const button = trigger();
        expect(button.getAttribute('aria-expanded')).toBe('false');
        await act(async () => button.click());
        expect(button.getAttribute('aria-expanded')).toBe('true');
        expect(getByRole(container, 'button', { name: 'Français' })).toBeDefined();
        await act(async () => button.click());
        expect(button.getAttribute('aria-expanded')).toBe('false');
        await act(async () => button.click());
        await act(async () => document.body.dispatchEvent(new MouseEvent('mousedown', { bubbles: true })));
        expect(button.getAttribute('aria-expanded')).toBe('false');
    });

    it('keeps language selection, persistence, and closing behavior', async () => {
        await render();
        await act(async () => trigger().click());
        await act(async () => getByRole<HTMLButtonElement>(container, 'button', { name: 'Français' }).click());
        expect(mocks.changeLanguage).toHaveBeenCalledExactlyOnceWith('fr');
        expect(mocks.updateAccountLanguage).toHaveBeenCalledExactlyOnceWith({ language: 'fr' });
        expect(mocks.updateUserData).toHaveBeenCalledExactlyOnceWith({ language: 'fr' });
        const button = getByRole(container, 'button', { name: 'Panel Language: Français' });
        expect(button.getAttribute('aria-expanded')).toBe('false');
    });

    it('updates the accessible name after external language changes', async () => {
        await render();
        const onLanguageChanged = mocks.on.mock.calls.find(([event]) => event === 'languageChanged')![1];
        await act(async () => onLanguageChanged('fr'));
        expect(getByRole(container, 'button', { name: 'Panel Language: Français' })).toBeDefined();
    });

    it('uses the server language for automatic preferences', async () => {
        mocks.state.user.data.language = 'geo';
        mocks.state.settings.data.locale = 'fr';
        await render();
        expect(getByRole(container, 'button', { name: 'Panel Language: Français' })).toBeDefined();
    });

    it('keeps an accessible name when the language list fails', async () => {
        vi.mocked(fetch).mockRejectedValue(new Error('Offline'));
        await render();
        expect(trigger().getAttribute('aria-expanded')).toBe('false');
    });
});
