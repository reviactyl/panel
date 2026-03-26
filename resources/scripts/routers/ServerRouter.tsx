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
import { ExtensionSlot } from '@/extensions/ExtensionSlot';
import { useExtensionRoutes } from '@/extensions/useExtensionRoutes';
import { useExtensions } from '@/extensions/useExtensions';
import { resolveExtensionIcon } from '@/extensions/iconResolver';

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
    const params = useParams<{ id: string }>();
    const serverNestId = ServerContext.useStoreState((state) => state.server.data?.nestId);
    const serverEggId = ServerContext.useStoreState((state) => state.server.data?.eggId);
    const { data: extensionData } = useExtensions();
    const customSidebarButtons = useStoreState((state) => state.reviactyl.data?.sidebarButtons ?? []);
    const normalizedSidebarButtons = (Array.isArray(customSidebarButtons) ? customSidebarButtons : []).filter(
        (button): button is ReviactylSidebarButton =>
            typeof button?.label === 'string' &&
            button.label.trim().length > 0 &&
            typeof button?.url === 'string' &&
            button.url.trim().length > 0
    );

    const serverExtensionRoutes = (Array.isArray(extensionData) ? extensionData : []).flatMap((extension) =>
        (extension.frontend?.routes?.serverRouter ?? [])
            .filter((route) => {
                const routeEggId = route.eggId ?? route.egg_id;
                const routeNestId = route.nestId ?? route.nest_id;
                const routeEggIds = route.eggIds ?? route.egg_ids;
                const routeNestIds = route.nestIds ?? route.nest_ids;

                const hasGuards =
                    routeEggId !== undefined ||
                    routeNestId !== undefined ||
                    Array.isArray(routeEggIds) ||
                    Array.isArray(routeNestIds);

                if (!hasGuards) return true;
                if (routeEggId !== undefined && routeEggId === serverEggId) return true;
                if (routeNestId !== undefined && routeNestId === serverNestId) return true;
                if (Array.isArray(routeEggIds) && serverEggId !== undefined && routeEggIds.includes(serverEggId)) {
                    return true;
                }

                if (Array.isArray(routeNestIds) && serverNestId !== undefined && routeNestIds.includes(serverNestId)) {
                    return true;
                }

                return false;
            })
            .filter((route) => typeof route?.path === 'string' && route.path.trim().length > 0)
            .map((route, index) => ({
                id: `${extension.id}-server-${index}`,
                label:
                    typeof route?.label === 'string' && route.label.trim().length > 0
                        ? route.label
                        : `${extension.name} Route`,
                permission: route?.permission,
                path: route.path,
                icon: resolveExtensionIcon(
                    typeof route?.icon === 'string' && route.icon.trim().length > 0 ? route.icon : undefined
                ),
            }))
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

            {serverExtensionRoutes.length > 0 && (
                <div>
                    <span className='label'>EXTENSIONS</span>
                    {serverExtensionRoutes.map((route) => {
                        const normalizedPath = route.path.replace(/^\/+/, '');
                        const to = route.path.startsWith('/server/')
                            ? route.path
                            : `/server/${params.id ?? ''}/${normalizedPath}`.replace(/\/+/g, '/');

                        const item = (
                            <Navigate id={`ext:${route.id}`} to={to}>
                                <span className='flex items-center'>
                                    {route.icon.component ? <route.icon.component className='w-4 h-4 mr-2' /> : null}
                                    {route.label}
                                </span>
                            </Navigate>
                        );

                        if (route.permission) {
                            return (
                                <Can key={route.id} action={route.permission} matchAny>
                                    {item}
                                </Can>
                            );
                        }

                        return <Fragment key={route.id}>{item}</Fragment>;
                    })}
                </div>
            )}

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

    const injectedRoutes = useExtensionRoutes('serverRouter', {
        eggId: serverEggId,
        nestId: serverNestId,
    });

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
                                            <ExtensionSlot
                                                name='server:router:above'
                                                context={{
                                                    eggId: serverEggId,
                                                    nestId: serverNestId,
                                                }}
                                            />
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

                                                {injectedRoutes.map(({ path, element, permission }) => (
                                                    <Route
                                                        key={`extension:${path}`}
                                                        path={path}
                                                        element={
                                                            <PermissionRoute permission={permission}>
                                                                {element}
                                                            </PermissionRoute>
                                                        }
                                                    />
                                                ))}

                                                <Route path='*' element={<NotFound />} />
                                            </Routes>

                                            <ExtensionSlot
                                                name='server:router:below'
                                                context={{
                                                    eggId: serverEggId,
                                                    nestId: serverNestId,
                                                }}
                                            />
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
