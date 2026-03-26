import { Fragment, useEffect, useState } from 'react';
import { Route, Routes, useParams, useLocation } from 'react-router-dom';
import Navigate from '@/reviactyl/components/Navigate';

import TransferListener from '@/components/server/TransferListener';
import Navbar from '@/reviactyl/ui/Navbar';
import WebsocketHandler from '@/components/server/WebsocketHandler';
import { ServerContext } from '@/state/server';
import Can from '@/components/elements/Can';
import Spinner from '@/components/elements/Spinner';
import { NotFound, ServerError } from '@/components/elements/ScreenBlock';
import { httpErrorToHuman } from '@/api/http';
import { useStoreState } from 'easy-peasy';
import InstallListener from '@/components/server/InstallListener';
import ErrorBoundary from '@/components/elements/ErrorBoundary';
import ConflictStateRenderer from '@/components/server/ConflictStateRenderer';
import PermissionRoute from '@/components/elements/PermissionRoute';
import routes from '@/routers/routes';
import Sidebar from '@/reviactyl/ui/Sidebar';
import { XIcon, MenuIcon, ExternalLinkIcon } from '@heroicons/react/solid';
import { LogoContainer } from '@/reviactyl/ui/LogoContainer';
import tw from 'twin.macro';
import { RouterContainer } from '@/reviactyl/ui/RouterContainer';
import { ContentContainer } from '@/reviactyl/ui/ContentContainer';
import TopServerDetails from '@/components/server/TopServerDetails';
import { ApplicationStore } from '@/state';
import Announcement from '@/reviactyl/ui/Announcement';
import MaintenanceAlert from '@/reviactyl/ui/MaintenanceAlert';
import Maintenance from '@/reviactyl/ui/Maintenance';
import { useTranslation } from 'react-i18next';
import { ReviactylSidebarButton } from '@/state/reviactyl';

interface NavItemProps {
    route: any;
}

const NavItem = ({ route }: NavItemProps) => {
    const { t } = useTranslation('routes');
    const params = useParams<{ id: string }>();

    const nestId = ServerContext.useStoreState((state) => state.server.data?.nestId);
    const eggId = ServerContext.useStoreState((state) => state.server.data?.eggId);

    const allowed =
        (route.nestIds && route.nestIds.includes(nestId ?? 0)) ||
        (route.eggIds && route.eggIds.includes(eggId ?? 0)) ||
        (route.nestId && route.nestId === nestId) ||
        (route.eggId && route.eggId === eggId) ||
        (!route.eggIds && !route.nestIds && !route.nestId && !route.eggId);

    if (!allowed) return null;

    return (
        <Navigate
            id={route.name}
            to={`/server/${params.id}/${(route.path ?? route.route).replace(/\/\*$/, '')}`}
            end={route.end ?? false}
        >
            <span className='flex items-center'>
                {route.icon && <route.icon className='w-5 mr-1' />}
                {route.name ? t(route.name) : null}
            </span>
        </Navigate>
    );
};

const ServerNavigation = () => {
    const { t } = useTranslation('server/index');
    const customSidebarButtons = useStoreState((state) => state.reviactyl.data?.sidebarButtons ?? []);
    const normalizedSidebarButtons = (Array.isArray(customSidebarButtons) ? customSidebarButtons : []).filter(
        (button): button is ReviactylSidebarButton =>
            typeof button?.label === 'string' &&
            button.label.trim().length > 0 &&
            typeof button?.url === 'string' &&
            button.url.trim().length > 0
    );

    return (
        <>
            {[
                { label: t('control'), routes: routes.server.control },
                { label: t('management'), routes: routes.server.management },
                { label: t('administration'), routes: routes.server.administration },
            ].map(({ label, routes }) => (
                <div key={label}>
                    <span className='label'>{label}</span>

                    {routes
                        .filter((route) => !!route.name)
                        .map((route) => (
                            <Fragment key={route.path ?? route.route}>
                                {route.permission ? (
                                    <Can action={route.permission} matchAny>
                                        <NavItem route={route} />
                                    </Can>
                                ) : (
                                    <NavItem route={route} />
                                )}
                            </Fragment>
                        ))}
                </div>
            ))}

            {normalizedSidebarButtons.length > 0 && (
                <div>
                    <span className='label'>MORE</span>
                    {normalizedSidebarButtons.map((button, index) => (
                        <a
                            key={`${button.url}-${index}`}
                            href={button.url}
                            target={button.newTab === true ? '_blank' : undefined}
                            rel={button.newTab === true ? 'noopener noreferrer' : undefined}
                        >
                            <span className='flex items-center'>
                                <ExternalLinkIcon className='w-4 h-4 mr-2' />
                                {button.label}
                            </span>
                        </a>
                    ))}
                </div>
            )}
        </>
    );
};

export default function ServerRouter() {
    const params = useParams<{ id: string }>();
    const location = useLocation();

    const isUnderMaintenance = useStoreState((state) => state.reviactyl.data?.isUnderMaintenance);
    const rootAdmin = useStoreState((state) => state.user.data?.rootAdmin);

    const [error, setError] = useState('');
    const [isSidebarOpen, setSidebarOpen] = useState(false);

    const id = ServerContext.useStoreState((state) => state.server.data?.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data?.uuid);
    const inConflictState = ServerContext.useStoreState((state) => state.server.inConflictState);

    const serverNestId = ServerContext.useStoreState((state) => state.server.data?.nestId);
    const serverEggId = ServerContext.useStoreState((state) => state.server.data?.eggId);

    const getServer = ServerContext.useStoreActions((actions) => actions.server.getServer);
    const clearServerState = ServerContext.useStoreActions((actions) => actions.clearServerState);

    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.logo);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);

    useEffect(() => () => clearServerState(), []);

    useEffect(() => {
        if (!params.id) return;

        setError('');

        getServer(params.id).catch((error) => {
            console.error(error);
            setError(httpErrorToHuman(error));
        });

        return () => clearServerState();
    }, [params.id]);

    const allRoutes = [...routes.server.control, ...routes.server.management, ...routes.server.administration];

    const routeAllowed = (route: any) =>
        (route.nestIds && route.nestIds.includes(serverNestId ?? 0)) ||
        (route.eggIds && route.eggIds.includes(serverEggId ?? 0)) ||
        (route.nestId && route.nestId === serverNestId) ||
        (route.eggId && route.eggId === serverEggId) ||
        (!route.eggIds && !route.nestIds && !route.nestId && !route.eggId);

    return (
        <Fragment>
            {isUnderMaintenance && !rootAdmin ? (
                <Maintenance />
            ) : (
                <RouterContainer>
                    {!uuid || !id ? (
                        error ? (
                            <ServerError message={error} />
                        ) : (
                            <Spinner size='large' centered />
                        )
                    ) : (
                        <>
                            <Navbar>
                                <div className='lg:hidden'>
                                    <button
                                        onClick={() => setSidebarOpen(!isSidebarOpen)}
                                        className='text-gray-500 bg-gray-700 p-2 rounded-ui'
                                    >
                                        {isSidebarOpen ? (
                                            <XIcon className='w-6 h-6' />
                                        ) : (
                                            <MenuIcon className='w-6 h-6' />
                                        )}
                                    </button>
                                </div>

                                <LogoContainer>
                                    <img
                                        src={logo}
                                        alt={name}
                                        onClick={() => (window.location.href = '/')}
                                        css={tw`h-[3rem] mt-5 cursor-pointer`}
                                    />
                                </LogoContainer>
                            </Navbar>

                            <ContentContainer>
                                {isSidebarOpen && (
                                    <div
                                        onClick={() => setSidebarOpen(false)}
                                        className='fixed inset-0 z-30 bg-gray-800/40 backdrop-blur-sm lg:hidden'
                                    />
                                )}

                                <Sidebar isOpen={isSidebarOpen}>
                                    <ServerNavigation />
                                </Sidebar>

                                <div className='w-full flex-1 overflow-y-auto'>
                                    <InstallListener />
                                    <TransferListener />
                                    <WebsocketHandler />

                                    {inConflictState &&
                                    (!rootAdmin || !location.pathname.endsWith(`/server/${id}/`)) ? (
                                        <ConflictStateRenderer />
                                    ) : (
                                        <ErrorBoundary>
                                            <TopServerDetails />
                                            <Announcement />
                                            <MaintenanceAlert />

                                            <Routes location={location}>
                                                {allRoutes
                                                    .filter(routeAllowed)
                                                    .map(({ route, permission, component: Component }) => (
                                                        <Route
                                                            key={route}
                                                            path={route}
                                                            element={
                                                                <PermissionRoute permission={permission}>
                                                                    <Spinner.Suspense>
                                                                        <Component />
                                                                    </Spinner.Suspense>
                                                                </PermissionRoute>
                                                            }
                                                        />
                                                    ))}

                                                <Route path='*' element={<NotFound />} />
                                            </Routes>
                                        </ErrorBoundary>
                                    )}
                                </div>
                            </ContentContainer>
                        </>
                    )}
                </RouterContainer>
            )}
        </Fragment>
    );
}
