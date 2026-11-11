import type { NavigateFunction } from 'react-router-dom';
import { unload } from 'swr';
import { resetHttpSession } from '@/api/http';
import type { LoginResponse } from './login';
import { store } from '@/state';
import type { PanelUser } from '@/state/user';
import { rawUserToUserData } from '@/lib/user';
import i18n from '@/i18n';
import { preloadAppRoute } from '@/routers/loadRouter';

export default async function completeLogin(response: LoginResponse, navigate: NavigateFunction): Promise<void> {
    if (!response.complete) return;
    const destination = new URL(response.intended || '/', window.location.href);
    const internal =
        destination.origin === window.location.origin &&
        /^\/(?:$|account(?:\/|$)|server\/|passkey(?:\/|$)|status\/)/.test(destination.pathname);
    if (!response.user?.uuid || !internal) {
        window.location.assign(destination.href);
        return;
    }

    const language =
        response.user.language === 'geo' ? store.getState().settings.data?.locale || 'en' : response.user.language;
    try {
        await Promise.all([
            unload({ revalidate: false }),
            preloadAppRoute(destination.pathname),
            i18n.changeLanguage(language),
            i18n.loadNamespaces(['dashboard/account', 'dashboard/index', 'routes', 'notifications', 'server/users']),
        ]);
    } catch {
        window.location.assign(destination.href);
        return;
    }
    resetHttpSession();
    (window as Window & { PanelUser?: PanelUser }).PanelUser = response.user;
    if (response.csrfToken) {
        document.querySelectorAll('meta[name="csrf-token"], meta[name="_token"]').forEach((meta) => {
            meta.setAttribute('content', response.csrfToken!);
        });
    }
    store.getActions().user.setUserData(rawUserToUserData(response.user));
    navigate(destination.pathname + destination.search + destination.hash, { replace: true });
}
