import { lazy } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { StoreProvider } from 'easy-peasy';
import { store } from '@/state';
import { SiteSettings } from '@/state/settings';
import { ReviactylSettings } from '@/state/reviactyl';
import ProgressBar from '@/reviactyl/elements/ProgressBar';
import { NotFound } from '@/reviactyl/elements/ScreenBlock';
import tw from 'twin.macro';
import GlobalStylesheet from '@/assets/css/GlobalStylesheet';
import AuthenticatedRoute from '@/reviactyl/elements/AuthenticatedRoute';
import { ServerContext } from '@/state/server';
import '@/assets/tailwind.css';
import Spinner from '@/reviactyl/elements/Spinner';
import { ThemeLoader } from '@/reviactyl/ui/ThemeEngine';
import { Invert } from '@/reviactyl/ui/SmartInvert';
import { LocaleLoader } from '@/reviactyl/ui/LanguageSwitcher';

const DashboardRouter = lazy(() => import('@/routers/DashboardRouter'));
const ServerRouter = lazy(() => import('@/routers/ServerRouter'));
const AuthenticationRouter = lazy(() => import('@/routers/AuthenticationRouter'));
const PublicServerStatus = lazy(() => import('@/components/public/PublicServerStatus'));

interface ExtendedWindow extends Window {
    SiteConfiguration?: SiteSettings;
    ReviactylConfiguration?: ReviactylSettings;
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
        updated_at: string;
        created_at: string;
    };
}

// setupInterceptors(history);

function App() {
    const { PanelUser, SiteConfiguration, ReviactylConfiguration } = window as ExtendedWindow;
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
            updatedAt: new Date(PanelUser.updated_at),
        });
    }

    if (!store.getState().settings.data) {
        store.getActions().settings.setSettings(SiteConfiguration!);
    }

    if (!store.getState().reviactyl.data) {
        store.getActions().reviactyl.setReviactyl(ReviactylConfiguration!);
    }

    return (
        <Invert>
            <GlobalStylesheet />
            <StoreProvider store={store}>
                <ThemeLoader />
                <LocaleLoader />
                <ProgressBar />
                <div css={tw`mx-auto w-auto`}>
                    <BrowserRouter
                        future={{
                            v7_startTransition: true,
                            v7_relativeSplatPath: true,
                        }}
                    >
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
                    </BrowserRouter>
                </div>
            </StoreProvider>
        </Invert>
    );
}

export { App };
