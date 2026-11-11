import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import useSWR, { mutate } from 'swr';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import completeLogin from './completeLogin';
import type { PanelUser } from '@/state/user';

const mocks = vi.hoisted(() => ({
    resetHttpSession: vi.fn(),
    setUserData: vi.fn(),
    preload: vi.fn(),
    changeLanguage: vi.fn(),
    namespaces: vi.fn(),
}));
vi.mock('@/api/http', () => ({ default: {}, resetHttpSession: mocks.resetHttpSession }));
vi.mock('@/state', () => ({
    store: {
        getState: () => ({ settings: { data: { locale: 'en' } } }),
        getActions: () => ({ user: { setUserData: mocks.setUserData } }),
    },
}));
vi.mock('@/i18n', () => ({ default: { changeLanguage: mocks.changeLanguage, loadNamespaces: mocks.namespaces } }));
vi.mock('@/routers/loadRouter', () => ({ preloadAppRoute: mocks.preload }));
const user = {
    uuid: 'signed-in-user',
    username: 'user',
    name_first: 'Demo',
    name_last: 'User',
    email: 'demo@example.test',
    root_admin: false,
    admin: true,
    permissions: ['read:nodes'],
    use_totp: true,
    language: 'fr',
    editor: 'cm',
    created_at: '2026-11-01',
    updated_at: '2026-11-02',
} as PanelUser;
let root: Root;
let element: HTMLDivElement;
const Reader = () => {
    const { data } = useSWR('login-test-guest-cache', null);
    return <p>{data || 'empty'}</p>;
};
beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.preload.mockResolvedValue(undefined);
    mocks.changeLanguage.mockResolvedValue(undefined);
    mocks.namespaces.mockResolvedValue(undefined);
    element = document.createElement('div');
    root = createRoot(element);
    await mutate('login-test-guest-cache', 'guest state', false);
    await act(async () => root.render(<Reader />));
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.restoreAllMocks();
});
describe('login completion', () => {
    it('fetches fresh private data when the same user signs in again inside the deduplication window', async () => {
        const fetcher = vi.fn().mockResolvedValue('server list');
        const Servers = () => {
            const { data } = useSWR('login-session-server-list', fetcher, { dedupingInterval: 60000 });
            return <p>{data || 'loading'}</p>;
        };
        await act(async () => root.render(<Servers />));
        expect(element.textContent).toBe('server list');
        await act(async () => root.render(<p>Guest</p>));
        await act(async () => completeLogin({ complete: true, user }, vi.fn()));
        await act(async () => root.render(<Servers />));
        expect(fetcher).toHaveBeenCalledTimes(2);
        expect(element.textContent).toBe('server list');
    });
    it('discards a previous session response that arrives after the new session fetch', async () => {
        let finishPrevious!: (data: string) => void;
        const fetcher = vi
            .fn()
            .mockImplementationOnce(() => new Promise<string>((resolve) => (finishPrevious = resolve)))
            .mockResolvedValue('current session');
        const Servers = () => {
            const { data } = useSWR('login-session-pending-list', fetcher);
            return <p>{data || 'loading'}</p>;
        };
        await act(async () => root.render(<Servers />));
        await act(async () => root.render(<p>Guest</p>));
        await act(async () => completeLogin({ complete: true, user }, vi.fn()));
        await act(async () => root.render(<Servers />));
        expect(element.textContent).toBe('current session');
        await act(async () => finishPrevious('previous session'));
        expect(element.textContent).toBe('current session');
    });
    it('clears guest cache before navigating with the authenticated identity and permissions', async () => {
        const navigate = vi.fn(() => expect(mocks.setUserData).toHaveBeenCalledOnce());
        expect(element.textContent).toBe('guest state');
        document.head.innerHTML = '<meta name="csrf-token" content="old"><meta name="_token" content="old">';
        await act(async () =>
            completeLogin(
                {
                    complete: true,
                    intended: '/server/abc/files?tab=one#/world',
                    user,
                    csrfToken: 'rotated-after-login',
                },
                navigate,
            ),
        );
        expect(navigate).toHaveBeenCalledWith('/server/abc/files?tab=one#/world', { replace: true });
        await act(async () => root.render(<Reader key='authenticated' />));
        expect(mocks.resetHttpSession).toHaveBeenCalledOnce();
        expect(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')).toBe('rotated-after-login');
        expect(document.querySelector('meta[name="_token"]')?.getAttribute('content')).toBe('rotated-after-login');
        expect(element.textContent).toBe('empty');
        expect(mocks.setUserData).toHaveBeenCalledWith(
            expect.objectContaining({
                uuid: user.uuid,
                rootAdmin: false,
                adminAccess: true,
                rolePermissions: ['read:nodes'],
                useTotp: true,
                fileEditor: 'cm',
                createdAt: new Date(user.created_at),
            }),
        );
        expect(navigate).toHaveBeenCalledWith('/server/abc/files?tab=one#/world', { replace: true });
        expect(mocks.changeLanguage).toHaveBeenCalledWith('fr');
    });
    it('does not authenticate or navigate while a checkpoint is still required', async () => {
        const navigate = vi.fn();
        await completeLogin({ complete: false, user }, navigate);
        expect(mocks.setUserData).not.toHaveBeenCalled();
        expect(navigate).not.toHaveBeenCalled();
        expect(element.textContent).toBe('guest state');
    });
    it.each(['/admin', 'https://example.test/account', '/api/client', '/auth/login'])(
        'keeps document navigation for %s',
        async (intended) => {
            const assign = vi.spyOn(window.location, 'assign').mockImplementation(() => {});
            const navigate = vi.fn();
            await completeLogin({ complete: true, intended, user }, navigate);
            expect(assign).toHaveBeenCalledWith(new URL(intended, window.location.href).href);
            expect(mocks.setUserData).not.toHaveBeenCalled();
            expect(navigate).not.toHaveBeenCalled();
        },
    );
    it('falls back to a document load when the response has no user payload', async () => {
        const assign = vi.spyOn(window.location, 'assign').mockImplementation(() => {});
        await completeLogin({ complete: true, intended: '/' }, vi.fn());
        expect(assign).toHaveBeenCalledOnce();
        expect(mocks.setUserData).not.toHaveBeenCalled();
    });
    it('does not partially authenticate if preparing the destination fails', async () => {
        const assign = vi.spyOn(window.location, 'assign').mockImplementation(() => {});
        mocks.preload.mockRejectedValueOnce(new Error('offline'));
        const navigate = vi.fn();
        await act(async () => completeLogin({ complete: true, user }, navigate));
        expect(assign).toHaveBeenCalledOnce();
        expect(mocks.setUserData).not.toHaveBeenCalled();
        expect(navigate).not.toHaveBeenCalled();
    });
});
