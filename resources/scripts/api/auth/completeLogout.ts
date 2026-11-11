import { unload } from 'swr';
import { resetHttpSession } from '@/api/http';
import { store } from '@/state';
import i18n from '@/i18n';
import { loadAuthenticationRouter } from '@/routers/loadRouter';
import { clearPreviewToken } from '@/lib/subuserPreviewStorage';
import { navigateToLogin } from '@/routers/navigation';

export const prepareLogout = () =>
    Promise.all([loadAuthenticationRouter(), i18n.loadNamespaces(['auth', 'dashboard/account'])]);

export default async function completeLogout(token: string | void, preparation?: Promise<unknown>): Promise<void> {
    delete (window as Window & { PanelUser?: unknown }).PanelUser;
    clearPreviewToken();
    if (!token) {
        resetHttpSession();
        window.location.replace('/auth/login');
        return;
    }
    try {
        await Promise.all([
            preparation ?? prepareLogout(),
            i18n.changeLanguage(store.getState().settings.data?.locale || 'en'),
            unload({ revalidate: false }),
        ]);
    } catch {
        resetHttpSession();
        window.location.replace('/auth/login');
        return;
    }
    resetHttpSession();
    document.querySelectorAll('meta[name="csrf-token"], meta[name="_token"]').forEach((meta) => {
        meta.setAttribute('content', token);
    });
    store.getActions().flashes.clearFlashes();
    store.getActions().permissions.setPermissions({});
    // Unmount protected screens before clearing the identity their selectors require.
    await navigateToLogin();
    store.getActions().user.setUserData(undefined);
}
