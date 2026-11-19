import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { createStore, StoreProvider } from 'easy-peasy';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import flashes from '@/state/flashes';
import user from '@/state/user';
import updateAccountAvatar from '@/api/account/updateAccountAvatar';
import logout from '@/api/auth/logout';
import AccountOverviewContainer from './AccountOverviewContainer';

vi.mock('@/api/auth/logout', () => ({ default: vi.fn() }));
vi.mock('@/api/account/updateAccountAvatar', () => ({ default: vi.fn() }));
vi.mock('@/api/auth/completeLogout', () => ({
    default: async () => window.location.replace('/auth/login'),
    prepareLogout: vi.fn().mockResolvedValue(undefined),
}));
vi.mock('react-router-dom', () => ({ useLocation: () => ({ state: null }), useNavigate: () => vi.fn() }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/components/dashboard/AccountContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => <main>{children}</main>,
}));
vi.mock('@/reviactyl/ui/Card', () => ({
    default: ({ children }: { children: ReactNode }) => <section>{children}</section>,
}));
vi.mock('@/components/MessageBox', () => ({
    default: ({ children }: { children: ReactNode }) => <div role='alert'>{children}</div>,
}));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({
    default: ({ visible }: { visible: boolean }) => (visible ? <div role='status'>Loading</div> : null),
}));
vi.mock('@/reviactyl/elements/TitledGreyBox', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/Avatar', () => ({ default: () => null, ANIMATED_AVATAR_STYLES: ['loops', 'waves'] }));
vi.mock('@/components/dashboard/forms/SocialLoginsContainer', () => ({ default: () => null }));
vi.mock('@/components/dashboard/forms/UpdatePasswordForm', () => ({ default: () => null }));
vi.mock('@/components/dashboard/forms/UpdateEmailAddressForm', () => ({ default: () => null }));
vi.mock('@/components/dashboard/forms/ConfigureTwoFactorForm', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/LanguageSwitcher', () => ({ default: () => null }));
vi.mock('@/reviactyl/ui/Theme', () => ({ ThemeToggle: () => null }));
vi.mock('@/reviactyl/ui/FileEditorSwitcher', () => ({ default: () => null }));
vi.mock('@/components/dashboard/ArchiveFormatSwitcher', () => ({ default: () => null }));
vi.mock('@/extensions/ExtensionSlot', () => ({ ExtensionSlot: () => null }));

const deferred = () => {
    let resolve!: () => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<void>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
};

const makeStore = () =>
    createStore({
        flashes,
        user: {
            ...user,
            data: {
                uuid: 'demo',
                username: 'demo',
                name_first: 'Demo',
                name_last: 'User',
                email: 'demo@example.com',
                language: 'en',
                fileEditor: 'codemirror',
                avatarStyle: 'gravatar',
                avatarAnimated: false,
                archiveFormat: 'tar.gz' as const,
                rootAdmin: false,
                adminAccess: false,
                rolePermissions: [],
                useTotp: false,
                createdAt: new Date(),
                updatedAt: new Date(),
            },
        },
        settings: { data: { name: 'Panel' } },
    });

describe('account overview', () => {
    let container: HTMLDivElement;
    let root: Root;
    let store: ReturnType<typeof makeStore>;

    beforeEach(async () => {
        vi.stubGlobal('IS_REACT_ACT_ENVIRONMENT', true);
        vi.mocked(logout).mockReset();
        vi.mocked(updateAccountAvatar).mockReset();
        window.location.href = 'http://localhost/account';
        store = makeStore();
        container = document.createElement('div');
        document.body.appendChild(container);
        root = createRoot(container);
        await act(async () =>
            root.render(
                <StoreProvider store={store}>
                    <AccountOverviewContainer />
                </StoreProvider>,
            ),
        );
    });

    afterEach(async () => {
        await act(async () => root.unmount());
        container.remove();
        vi.unstubAllGlobals();
    });

    const clickLogout = async () => {
        await act(async () => container.querySelector('button')!.click());
    };

    it('shows failed avatar saves in the Profile Picture card, restores the selection, and allows retry', async () => {
        const request = deferred();
        vi.mocked(updateAccountAvatar).mockReturnValueOnce(request.promise).mockResolvedValueOnce(undefined);
        const profilePicture = Array.from(container.querySelectorAll('div')).find(
            (element) => element.firstElementChild?.textContent === 'overview.profile-picture',
        )!;
        const initials = () =>
            profilePicture.querySelector<HTMLButtonElement>('button[aria-label="avatar.styles.initials"]')!;
        await act(async () => initials().click());
        expect(initials().disabled).toBe(true);
        expect(store.getState().user.data.avatarStyle).toBe('initials');

        await act(async () => request.reject(new Error('Avatar save unavailable')));
        expect(profilePicture.textContent).toContain('Avatar save unavailable');
        expect(container.textContent.match(/Avatar save unavailable/g)).toHaveLength(1);
        expect(store.getState().user.data.avatarStyle).toBe('gravatar');
        expect(initials().getAttribute('aria-pressed')).toBe('false');
        expect(initials().disabled).toBe(false);

        await act(async () => initials().click());
        expect(profilePicture.textContent).not.toContain('Avatar save unavailable');
        expect(store.getState().user.data.avatarStyle).toBe('initials');
        expect(initials().getAttribute('aria-pressed')).toBe('true');
        expect(updateAccountAvatar).toHaveBeenCalledTimes(2);
    });

    it.each([
        [false, true, 'overview.administrator'],
        [true, true, 'overview.administrator'],
        [false, false, 'Panel overview.user'],
    ])(
        'labels rootAdmin=%s adminAccess=%s consistently with admin navigation',
        async (rootAdmin, adminAccess, label) => {
            await act(async () => store.getActions().user.updateUserData({ rootAdmin, adminAccess }));
            const adminLink = container.querySelector('a[href="/admin"]');
            expect(container.textContent).toContain(label);
            expect(Boolean(adminLink)).toBe(adminAccess);
        },
    );

    it('keeps the account page and other messages after a network failure, then allows a successful retry', async () => {
        const first = deferred();
        vi.mocked(logout).mockReturnValueOnce(first.promise);
        await act(async () =>
            store.getActions().flashes.addFlash({
                key: 'account:email',
                type: 'success',
                message: 'Email saved',
            }),
        );
        await clickLogout();
        expect(container.querySelector('[role="status"]')).not.toBeNull();
        expect(window.location.pathname).toBe('/account');

        await act(async () => first.reject(new Error('Network Error')));
        expect(window.location.pathname).toBe('/account');
        expect(container.textContent).toContain('Network Error');
        expect(container.textContent).toContain('Email saved');
        expect(container.querySelector('[role="status"]')).toBeNull();
        expect(container.querySelector('button')!.disabled).toBe(false);

        const retry = deferred();
        vi.mocked(logout).mockReturnValueOnce(retry.promise);
        await clickLogout();
        expect(logout).toHaveBeenCalledTimes(2);
        expect(container.textContent).not.toContain('Network Error');
        expect(container.textContent).toContain('Email saved');
        expect(container.querySelector('[role="status"]')).not.toBeNull();
        expect(container.querySelector('button')!.disabled).toBe(true);
        expect(window.location.pathname).toBe('/account');
        await act(async () => retry.resolve());
        expect(window.location.pathname).toBe('/auth/login');
    });

    it('redirects only once the logout request succeeds', async () => {
        const request = deferred();
        vi.mocked(logout).mockReturnValueOnce(request.promise);
        await clickLogout();
        expect(window.location.pathname).toBe('/account');
        await act(async () => request.resolve());
        expect(window.location.pathname).toBe('/auth/login');
        expect(container.querySelector('[role="alert"]')).toBeNull();
    });

    it('shows a rejected server response without redirecting and replaces only the logout error on retry', async () => {
        vi.mocked(logout).mockRejectedValue(
            Object.assign(new Error('Request failed with status code 503'), {
                response: { data: { errors: [{ detail: 'Logout unavailable' }] } },
            }),
        );
        await clickLogout();
        await clickLogout();
        expect(logout).toHaveBeenCalledTimes(2);
        expect(window.location.pathname).toBe('/account');
        expect(container.querySelectorAll('[role="alert"]')).toHaveLength(1);
        expect(container.textContent).toContain('Logout unavailable');
        expect(container.querySelector('[role="status"]')).toBeNull();
    });
});
