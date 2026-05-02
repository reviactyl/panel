import { useState } from 'react';
import { Suspense } from 'react';
import { useRoutes } from 'react-router-dom';
import Navigate from '@/reviactyl/components/Navigate';
import DashboardContainer from '@/components/dashboard/DashboardContainer';
import { NotFound } from '@/reviactyl/elements/ScreenBlock';
import Spinner from '@/reviactyl/elements/Spinner';
import routes from '@/routers/routes';
import { RouterContainer } from '@/reviactyl/ui/RouterContainer';
import { XIcon, MenuIcon, ExternalLinkIcon } from '@heroicons/react/solid';
import { ContentContainer } from '@/reviactyl/ui/ContentContainer';
import { motion } from 'framer-motion';
import { Navbar, Sidebar } from '@/reviactyl/components/Layout';
import { useStoreState } from 'easy-peasy';
import Announcement from '@/reviactyl/ui/Announcement';
import MaintenanceAlert from '@/reviactyl/ui/MaintenanceAlert';
import QuickLinks from '@/reviactyl/ui/QuickLinks';
import Maintenance from '@/reviactyl/ui/Maintenance';
import { useTranslation } from 'react-i18next';
import { FaHouse } from 'react-icons/fa6';
import { DesignifySidebarButton } from '@/state/designify';
import { ExtensionSlot } from '@/extensions/ExtensionSlot';
import { useExtensionRoutes } from '@/extensions/useExtensionRoutes';
import { useExtensions } from '@/extensions/useExtensions';
import { resolveExtensionIcon } from '@/extensions/iconResolver';

interface Props {
    route: any;
}

const NavItem = ({ route }: Props) => {
    const { t } = useTranslation('routes');
    const to = (value: string) => {
        return `/account/${value.replace(/^\/+/, '')}`;
    };

    return (
        <Navigate id={route.name} to={to(route.path ?? route.route)} end={route.end}>
            <span className='flex items-center'>
                {route.icon && <route.icon className={`w-5 mr-1`} />} {route.name ? t(route.name as string) : null}
            </span>
        </Navigate>
    );
};

const DashboardNavigation = () => {
    const { t } = useTranslation('routes');
    const customSidebarButtons = useStoreState((state) => state.designify.data?.sidebarButtons ?? []);
    const { data: extensionData } = useExtensions();

    const dashboardExtensionRoutes = (Array.isArray(extensionData) ? extensionData : []).flatMap((extension) =>
        (extension.frontend?.routes?.dashboardRouter ?? [])
            .filter((route) => typeof route?.path === 'string' && route.path.trim().length > 0)
            .map((route, index) => ({
                id: `${extension.id}-dashboard-${index}`,
                label:
                    typeof route?.label === 'string' && route.label.trim().length > 0
                        ? route.label
                        : `${extension.name} Route`,
                path: route.path,
                icon: resolveExtensionIcon(typeof route?.icon === 'string' ? route.icon : undefined),
            }))
    );
    const normalizedSidebarButtons = (Array.isArray(customSidebarButtons) ? customSidebarButtons : []).filter(
        (button): button is DesignifySidebarButton =>
            typeof button?.label === 'string' &&
            button.label.trim().length > 0 &&
            typeof button?.url === 'string' &&
            button.url.trim().length > 0
    );

    return (
        <>
            <div>
                <div className='mt-2'>
                    <span className='label -mb-2'>Dashboard</span>
                    <Navigate id='index.dashboard' to='/' end className='mt-2'>
                        <span className='flex items-center'>
                            <FaHouse className='w-5 mr-1' /> {t('index.dashboard')}
                        </span>
                    </Navigate>
                </div>

                <div className='mt-2'>
                    <span className='label'>Account</span>
                    {routes.account
                        .filter((route) => !!route.name)
                        .map((route) => (
                            <NavItem key={route.name} route={route} />
                        ))}
                </div>
            </div>

            {normalizedSidebarButtons.length > 0 && (
                <div className='mt-2'>
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

            {dashboardExtensionRoutes.length > 0 && (
                <div className='mt-2'>
                    <span className='label'>EXTENSIONS</span>
                    {dashboardExtensionRoutes.map((route) => (
                        <Navigate
                            key={route.id}
                            id={`ext:${route.id}`}
                            to={route.path.startsWith('/') ? route.path : `/${route.path.replace(/^\/+/, '')}`}
                        >
                            <span className='flex items-center'>
                                {route.icon.component ? <route.icon.component className='w-4 h-4 mr-2' /> : null}
                                {route.label}
                            </span>
                        </Navigate>
                    ))}
                </div>
            )}
        </>
    );
};

function DashboardRouter() {
    const [isSidebarOpen, setSidebarOpen] = useState(false);
    const isUnderMaintenance = useStoreState((state) => state.designify.data?.isUnderMaintenance);
    const rootAdmin = useStoreState((state) => state.user.data?.rootAdmin);
    const injectedRoutes = useExtensionRoutes('dashboardRouter');

    return (
        <>
            {isUnderMaintenance && !rootAdmin ? (
                <Maintenance />
            ) : (
                <RouterContainer>
                    <Navbar>
                        <div className='lg:hidden'>
                            <button
                                onClick={() => setSidebarOpen(!isSidebarOpen)}
                                className='text-gray-600 bg-gray-900 p-2 rounded-ui'
                            >
                                {isSidebarOpen ? <XIcon className='w-6 h-6' /> : <MenuIcon className='w-6 h-6' />}
                            </button>
                        </div>
                    </Navbar>
                    <ContentContainer>
                        {isSidebarOpen && (
                            <div
                                onClick={() => setSidebarOpen(false)}
                                className='fixed inset-0 z-30 bg-gray-900/40 backdrop-blur-sm transition-all duration-300 ease-in-out lg:hidden'
                            />
                        )}
                        <motion.div
                            initial={{ opacity: 1 }}
                            animate={{ opacity: 1 }}
                            transition={{ duration: 0.15, ease: 'easeIn' }}
                        >
                            <Sidebar isOpen={isSidebarOpen}>
                                <DashboardNavigation />
                            </Sidebar>
                        </motion.div>
                        <div className='w-full flex-1 overflow-y-auto'>
                            <Suspense fallback={<Spinner centered />}>
                                {useRoutes([
                                    {
                                        path: '',
                                        element: (
                                            <>
                                                <ExtensionSlot name='dashboard:router:above' />
                                                <Announcement />
                                                <MaintenanceAlert />
                                                <QuickLinks />
                                                <DashboardContainer />
                                                <ExtensionSlot name='dashboard:router:below' />
                                            </>
                                        ),
                                    },
                                    ...routes.account.map(({ route, component: Component }) => ({
                                        path: `/account/${route}`.replace('//', '/'),
                                        element: <Component />,
                                    })),
                                    ...injectedRoutes,
                                    { path: '*', element: <NotFound /> },
                                ])}
                            </Suspense>
                        </div>
                    </ContentContainer>
                </RouterContainer>
            )}
        </>
    );
}

export default DashboardRouter;
