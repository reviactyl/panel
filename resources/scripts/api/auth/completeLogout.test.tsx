import { beforeEach, expect, it, vi } from 'vitest';
const mocks = vi.hoisted(() => ({
    reset: vi.fn(),
    navigate: vi.fn(),
    clearPreview: vi.fn(),
    preload: vi.fn(),
    namespaces: vi.fn(),
    unload: vi.fn(),
    clearFlashes: vi.fn(),
    permissions: vi.fn(),
    user: vi.fn(),
}));
vi.mock('@/api/http', () => ({ resetHttpSession: mocks.reset }));
vi.mock('@/lib/subuserPreviewStorage', () => ({ clearPreviewToken: mocks.clearPreview }));
vi.mock('@/routers/loadRouter', () => ({ loadAuthenticationRouter: mocks.preload }));
vi.mock('@/i18n', () => ({
    default: { loadNamespaces: mocks.namespaces, changeLanguage: vi.fn().mockResolvedValue(undefined) },
}));
vi.mock('swr', () => ({ unload: mocks.unload }));
vi.mock('@/state', () => ({
    store: {
        getState: () => ({ settings: { data: { locale: 'en' } } }),
        getActions: () => ({
            flashes: { clearFlashes: mocks.clearFlashes },
            permissions: { setPermissions: mocks.permissions },
            user: { setUserData: mocks.user },
        }),
    },
}));
vi.mock('@/routers/navigation', () => ({ navigateToLogin: mocks.navigate }));
import completeLogout from './completeLogout';
beforeEach(() => {
    vi.resetAllMocks();
    document.head.innerHTML = '<meta name="csrf-token" content="old"><meta name="_token" content="old">';
    (window as any).PanelUser = { uuid: 'signed-in' };
});
it('clears private caches, rotates CSRF metadata and unmounts protected screens before clearing identity', async () => {
    mocks.navigate.mockImplementation(() => {
        expect(mocks.user).not.toHaveBeenCalled();
        expect(mocks.unload).toHaveBeenCalled();
        expect(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')).toBe('rotated');
    });
    await completeLogout('rotated');
    expect(mocks.navigate).toHaveBeenCalledOnce();
    expect(mocks.user).toHaveBeenCalledExactlyOnceWith(undefined);
    expect(mocks.permissions).toHaveBeenCalledWith({});
    expect(mocks.reset).toHaveBeenCalledOnce();
    expect(mocks.clearPreview).toHaveBeenCalledOnce();
    expect((window as any).PanelUser).toBeUndefined();
    expect(document.querySelector('meta[name="_token"]')?.getAttribute('content')).toBe('rotated');
});
it.each(['missing token', 'failed preparation'])('uses the login document fallback for %s', async (reason) => {
    const replace = vi.spyOn(window.location, 'replace').mockImplementation(() => {});
    if (reason === 'failed preparation') mocks.preload.mockRejectedValueOnce(new Error('offline'));
    await completeLogout(reason === 'missing token' ? undefined : 'rotated');
    expect(replace).toHaveBeenCalledWith('/auth/login');
    expect(mocks.navigate).not.toHaveBeenCalled();
    expect(mocks.user).not.toHaveBeenCalled();
    replace.mockRestore();
});
