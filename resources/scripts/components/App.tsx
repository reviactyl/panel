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
import { refreshSelectedTheme, ThemeLoader } from '@/reviactyl/ui/ThemeEngine';
import { Invert } from '@/reviactyl/ui/SmartInvert';
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

const previewColorKeys = [
    'colorPrimary',
    'colorSuccess',
    'colorDanger',
    'colorSecondary',
    'color50',
    'color100',
    'color200',
    'color300',
    'color400',
    'color500',
    'color600',
    'color700',
    'color800',
    'color900',
    'color950',
] as const;

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

            for (let index = 1; index <= 7; index += 1) {
                const key = `theme${index}`;
                const existingTheme = existingSettings[key];
                const incomingTheme = settings[key];
                if (typeof incomingTheme === 'object' && incomingTheme !== null && !Array.isArray(incomingTheme)) {
                    nextSettings[key] = {
                        ...(typeof existingTheme === 'object' && existingTheme !== null && !Array.isArray(existingTheme)
                            ? existingTheme
                            : {}),
                        ...incomingTheme,
                    };
                }
            }

            window.PanelConfiguration = nextSettings;
            store.getActions().designify.setDesignify(nextSettings);

            const root = document.documentElement;
            previewColorKeys.forEach((key) => {
                const value = settings[key];
                const channels = typeof value === 'string' ? toRgbChannels(value) : null;
                const property = `--color-${key.replace('color', '').toLowerCase()}`;
                if (channels) {
                    root.style.setProperty(property, channels);
                } else {
                    root.style.removeProperty(property);
                }
            });

            if (settings.background === 'none') {
                root.style.setProperty('--background', 'none');
            } else if (typeof settings.background === 'string' && settings.background.length > 0) {
                root.style.setProperty('--background', `url(${JSON.stringify(settings.background)})`);
            } else {
                root.style.removeProperty('--background');
            }
            if (typeof settings.radius === 'string' && CSS.supports('border-radius', settings.radius)) {
                root.style.setProperty('--radius', settings.radius);
            } else {
                root.style.removeProperty('--radius');
            }
            if (typeof settings.fontFamily === 'string' && settings.fontFamily.length > 0) {
                root.style.setProperty('--font-family', `"${settings.fontFamily.replaceAll('+', ' ')}", sans-serif`);
            } else {
                root.style.removeProperty('--font-family');
            }

            refreshSelectedTheme();
            window.dispatchEvent(new Event('reviactyl:designify-config-updated'));
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
        <Invert>
            <StoreProvider store={store}>
                <DesignifyPreviewBridge />
                <ThemeLoader />
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
        </Invert>
    );
}

export { App };
