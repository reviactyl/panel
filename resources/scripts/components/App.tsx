import { lazy, useEffect } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { StoreProvider } from 'easy-peasy';
import { store } from '@/state';
import { SiteSettings } from '@/state/settings';
import { DesignifySettings } from '@/state/designify';
import ProgressBar from '@/reviactyl/elements/ProgressBar';
import { NotFound } from '@/reviactyl/elements/ScreenBlock';
import AuthenticatedRoute from '@/reviactyl/elements/AuthenticatedRoute';
import { ServerContext } from '@/state/server';
import '@/assets/tailwind.css';
import Spinner from '@/reviactyl/elements/Spinner';
import { Theme } from '@/reviactyl/ui/Theme';
import { LocaleLoader } from '@/reviactyl/ui/LanguageSwitcher';
import { SubuserPreviewProvider } from '@/context/SubuserPreviewContext';
import { SubuserPreviewFrame } from '@/components/subuser-preview/SubuserPreviewFrame';

const DashboardRouter = lazy(() => import('@/routers/DashboardRouter'));
const ServerRouter = lazy(() => import('@/routers/ServerRouter'));
const AuthenticationRouter = lazy(() => import('@/routers/AuthenticationRouter'));
const PublicServerStatus = lazy(() => import('@/components/public/PublicServerStatus'));

interface ExtendedWindow extends Window {
    SiteConfiguration?: SiteSettings;
    PanelConfiguration?: DesignifySettings;
    PanelUser?: {
        uuid: string;
        username: string;
        name_first: string;
        name_last: string;
        email: string;
        root_admin: boolean;
        use_totp: boolean;
        language: string;
        editor: string;
        avatar_style: string;
        avatar_animated: boolean;
        updated_at: string;
        created_at: string;
    };
}

const previewColorKeys = ['colorPrimary', 'colorSuccess', 'colorDanger', 'colorSecondary'] as const;

const previewPaletteSteps = ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'] as const;

const toRgbChannels = (hex: string): string | null => {
    const match = hex.match(/^#([\da-f]{3}|[\da-f]{6})$/i);
    if (!match) return null;

    const matchedHex = match[1];
    if (!matchedHex) return null;

    const value = matchedHex.length === 3 ? matchedHex.replace(/(.)/g, '$1$1') : matchedHex;
    const number = Number.parseInt(value, 16);

    return `${(number >> 16) & 255} ${(number >> 8) & 255} ${number & 255}`;
};

const DesignifyPreviewBridge = () => {
    useEffect(() => {
        if (window.self === window.top) return;

        const handlePreviewUpdate = (event: MessageEvent) => {
            if (event.origin !== window.location.origin || event.source !== window.parent) return;
            if (event.data?.type !== 'reviactyl:designify-preview' || !event.data.settings) return;

            const settings = event.data.settings as DesignifySettings & Record<string, unknown>;
            const existingSettings = (window.PanelConfiguration ?? {}) as DesignifySettings & Record<string, unknown>;
            const nextSettings = { ...existingSettings, ...settings } as DesignifySettings & Record<string, unknown>;

            window.PanelConfiguration = nextSettings;
            store.getActions().designify.setDesignify(nextSettings);

            const root = document.documentElement;
            previewColorKeys.forEach((key) => {
                const value = nextSettings[key];
                const channels = typeof value === 'string' ? toRgbChannels(value) : null;
                const property = `--color-${key.replace('color', '').toLowerCase()}`;
                if (channels) {
                    root.style.setProperty(property, channels);
                } else {
                    root.style.removeProperty(property);
                }
            });

            let paletteStyle = document.querySelector<HTMLStyleElement>('style[data-designify-preview-palette]');
            if (!paletteStyle) {
                paletteStyle = document.createElement('style');
                paletteStyle.dataset.designifyPreviewPalette = '';
                document.head.append(paletteStyle);
            }

            const paletteDeclarations = (suffix: '' | 'L') =>
                previewPaletteSteps
                    .map((step) => {
                        const value = nextSettings[`color${step}${suffix}`];
                        const channels = typeof value === 'string' ? toRgbChannels(value) : null;

                        return channels ? `--color-${step}: ${channels}` : null;
                    })
                    .filter(Boolean)
                    .join(';');

            paletteStyle.textContent = `:root { ${paletteDeclarations('L')} } .dark { ${paletteDeclarations('')} }`;

            if (nextSettings.background === 'none') {
                root.style.setProperty('--background', 'none');
            } else if (typeof nextSettings.background === 'string' && nextSettings.background.length > 0) {
                root.style.setProperty('--background', `url(${JSON.stringify(nextSettings.background)})`);
            } else {
                root.style.removeProperty('--background');
            }
            if (typeof nextSettings.radius === 'string' && CSS.supports('border-radius', nextSettings.radius)) {
                root.style.setProperty('--radius', nextSettings.radius);
            } else {
                root.style.removeProperty('--radius');
            }
            if (typeof nextSettings.fontFamily === 'string' && nextSettings.fontFamily.length > 0) {
                root.style.setProperty(
                    '--font-family',
                    `"${nextSettings.fontFamily.replaceAll('+', ' ')}", sans-serif`,
                );
            } else {
                root.style.removeProperty('--font-family');
            }
        };

        window.addEventListener('message', handlePreviewUpdate);
        window.parent.postMessage({ type: 'reviactyl:designify-preview-ready' }, window.location.origin);

        return () => window.removeEventListener('message', handlePreviewUpdate);
    }, []);

    return null;
};

/**
 * Renders the application shell, global providers, and route tree.
 *
 * @returns The configured application interface.
 */

function App() {
    const { PanelUser, SiteConfiguration, PanelConfiguration } = window as ExtendedWindow;
    if (PanelUser && !store.getState().user.data) {
        store.getActions().user.setUserData({
            uuid: PanelUser.uuid,
            username: PanelUser.username,
            name_first: PanelUser.name_first,
            name_last: PanelUser.name_last,
            email: PanelUser.email,
            language: PanelUser.language,
            rootAdmin: PanelUser.root_admin,
            useTotp: PanelUser.use_totp,
            createdAt: new Date(PanelUser.created_at),
            fileEditor: PanelUser.editor,
            avatarStyle: PanelUser.avatar_style || 'gravatar',
            avatarAnimated: PanelUser.avatar_animated ?? true,
            updatedAt: new Date(PanelUser.updated_at),
        });
    }

    if (!store.getState().settings.data) {
        store.getActions().settings.setSettings(SiteConfiguration!);
    }

    if (!store.getState().designify.data) {
        store.getActions().designify.setDesignify(PanelConfiguration!);
    }

    return (
        <Theme>
            <StoreProvider store={store}>
                <DesignifyPreviewBridge />
                <LocaleLoader />
                <ProgressBar />
                <div className='mx-auto w-auto'>
                    <BrowserRouter>
                        <SubuserPreviewProvider>
                            <SubuserPreviewFrame>
                                <Routes>
                                    <Route
                                        path='/auth/*'
                                        element={
                                            <Spinner.Suspense>
                                                <AuthenticationRouter />
                                            </Spinner.Suspense>
                                        }
                                    />
                                    <Route
                                        path='/server/:id/*'
                                        element={
                                            <AuthenticatedRoute>
                                                <Spinner.Suspense>
                                                    <ServerContext.Provider>
                                                        <ServerRouter />
                                                    </ServerContext.Provider>
                                                </Spinner.Suspense>
                                            </AuthenticatedRoute>
                                        }
                                    />
                                    <Route
                                        path='/status/:id/*'
                                        element={
                                            <Spinner.Suspense>
                                                <PublicServerStatus />
                                            </Spinner.Suspense>
                                        }
                                    />
                                    <Route
                                        path='/*'
                                        element={
                                            <AuthenticatedRoute>
                                                <Spinner.Suspense>
                                                    <DashboardRouter />
                                                </Spinner.Suspense>
                                            </AuthenticatedRoute>
                                        }
                                    />
                                    <Route path='*' element={<NotFound />} />
                                </Routes>
                            </SubuserPreviewFrame>
                        </SubuserPreviewProvider>
                    </BrowserRouter>
                </div>
            </StoreProvider>
        </Theme>
    );
}

export { App };
