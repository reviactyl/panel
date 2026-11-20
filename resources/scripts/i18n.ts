import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import I18NextHttpBackend from 'i18next-http-backend';
import I18NextMultiloadBackendAdapter from 'i18next-multiload-backend-adapter';
import LanguageDetector from 'i18next-browser-languagedetector';
import { namespaceMap, namespacesForPath } from './i18nNamespaces';
import { takePrefetchedLocale, waitForPageData } from '@/api/prefetch';

// If we're using HMR use a unique hash per page reload so that we're always
// doing cache busting. Otherwise just use the builder provided hash value in
// the URL to allow cache busting to occur whenever the front-end is rebuilt.
const hash = import.meta.env.DEV ? Date.now().toString(16) : __LOCALE_VERSION__;
const panelWindow = window as Window & { PanelUser?: { language?: string }; SiteConfiguration?: { locale?: string } };

// Shared with the server (InitialApiResponsesComposer), which sends these translations with the page.
const initialNamespaces = namespacesForPath(window.location.pathname);

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
        ns: [...namespaceMap.always, ...initialNamespaces],
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
                request: (
                    options: { requestOptions?: RequestInit | ((payload: unknown) => RequestInit) },
                    url: string,
                    payload: unknown,
                    callback: (error: unknown, response?: { status: number; data: string }) => void,
                ) => {
                    const network = () =>
                        fetch(
                            url,
                            typeof options.requestOptions === 'function'
                                ? options.requestOptions(payload)
                                : options.requestOptions,
                        ).then(
                            (response) =>
                                response.text().then((data) => callback(null, { status: response.status, data })),
                            (error) => callback(error),
                        );
                    void waitForPageData().then(() => {
                        const embedded = takePrefetchedLocale(url);
                        if (!embedded) return void network();
                        embedded.then(
                            (response) =>
                                response.status === 200
                                    ? callback(null, { status: 200, data: response.body })
                                    : network(),
                            network,
                        );
                    });
                },
            },
        } as Record<string, any>,
        interpolation: {
            // Per i18n-react documentation: this is not needed since React is already
            // handling escapes for us.
            escapeValue: false,
        },
    });

export default i18n;
