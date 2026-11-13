import preloadable from '@/plugins/preloadable';
import i18n from '@/i18n';
import type { ComponentType } from 'react';

const router = (load: () => Promise<{ default: ComponentType }>, namespaces: string[]) =>
    preloadable(() => Promise.all([load(), i18n.loadNamespaces(namespaces)]).then(([module]) => module));

export const DashboardRouter = router(
    () => import('./DashboardRouter'),
    ['dashboard/account', 'dashboard/index', 'routes', 'notifications', 'server/users'],
);
export const ServerRouter = router(
    () => import('./ServerRouter'),
    ['dashboard/account', 'server/index', 'server/console', 'routes', 'notifications', 'server/users'],
);
export const AuthenticationRouter = router(
    () => import('./AuthenticationRouter'),
    ['auth', 'dashboard/account', 'server/users'],
);
export const loadDashboardRouter = DashboardRouter.preload;
export const loadServerRouter = ServerRouter.preload;
export const loadAuthenticationRouter = AuthenticationRouter.preload;

export const preloadAppRoute = (pathname: string) => {
    if (pathname.startsWith('/server/')) return loadServerRouter();
    if (pathname.startsWith('/auth/')) return loadAuthenticationRouter();
    return loadDashboardRouter();
};
