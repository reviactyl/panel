import i18n from '@/i18n';
import { loadServerRouter } from './loadRouter';

export default function preloadServerConsole() {
    void Promise.all([
        loadServerRouter(),
        i18n.loadNamespaces(['server/index', 'server/console']),
        import('./routes').then(({ default: routes }) => routes.server.control[0]?.component.preload?.()),
    ]).catch(() => undefined);
}
