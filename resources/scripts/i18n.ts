import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import I18NextHttpBackend from 'i18next-http-backend';
import I18NextMultiloadBackendAdapter from 'i18next-multiload-backend-adapter';
import LanguageDetector from 'i18next-browser-languagedetector';

// If we're using HMR use a unique hash per page reload so that we're always
// doing cache busting. Otherwise just use the builder provided hash value in
// the URL to allow cache busting to occur whenever the front-end is rebuilt.
const hash = import.meta.env.DEV ? Date.now().toString(16) : __LOCALE_VERSION__;
const panelWindow = window as Window & { PanelUser?: { language?: string }; SiteConfiguration?: { locale?: string } };

const pathname = window.location.pathname;
const initialNamespaces = pathname.startsWith('/auth')
    ? ['auth', 'dashboard/account', 'server/users']
    : [
          'dashboard/account',
          'server/users',
          'notifications',
          ...(pathname.startsWith('/server/') ? ['server/index', 'server/console'] : ['dashboard/index']),
      ];
if (pathname.startsWith('/server/')) {
    const screen = pathname.split('/')[3] || 'console';
    const namespaces: Record<string, string[]> = {
        console: ['server/console'],
        files: ['server/files', 'server/recycle', 'generic', 'validation'],
        startup: ['server/startup', 'server/recycle'],
        network: ['server/network'],
        metrics: ['server/metrics'],
        databases: ['server/databases', 'generic'],
        schedules: ['server/schedules'],
        backups: ['server/backups'],
        users: ['server/users'],
        settings: ['server/settings'],
        activity: ['server/activity', 'activity'],
    };
    initialNamespaces.push(...(namespaces[screen] ?? []));
}

i18n.use(I18NextMultiloadBackendAdapter)
    .use(initReactI18next)
    .use(LanguageDetector)
    .init({
        debug: process.env.DEBUG === 'true',
        fallbackLng: 'en',
        lng:
            panelWindow.PanelUser?.language && panelWindow.PanelUser.language !== 'geo'
                ? panelWindow.PanelUser.language
                : panelWindow.SiteConfiguration?.locale,
        ns: ['strings', 'routes', ...initialNamespaces],
        defaultNS: 'translation',
        keySeparator: '.',
        backend: {
            backend: I18NextHttpBackend,
            backendOption: {
                loadPath: (lngs: string[], namespaces: string[]) => {
                    const lng = lngs.join('+');
                    const ns = namespaces.join(',');
                    return `/locales/locale.json?locale=${encodeURIComponent(lng)}&namespace=${encodeURIComponent(ns)}`;
                },
                queryStringParams: { hash },
                allowMultiLoading: true,
                multiSeparator: ',',
            },
        } as Record<string, any>,
        interpolation: {
            // Per i18n-react documentation: this is not needed since React is already
            // handling escapes for us.
            escapeValue: false,
        },
    });

export default i18n;
