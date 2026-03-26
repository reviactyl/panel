import { useState } from 'react';
import { Suspense } from 'react';
import { useRoutes } from 'react-router-dom';
import Navigate from '@/reviactyl/components/Navigate';
import DashboardContainer from '@/components/dashboard/DashboardContainer';
import { NotFound } from '@/components/elements/ScreenBlock';
import Spinner from '@/components/elements/Spinner';
import routes from '@/routers/routes';
import { RouterContainer } from '@/reviactyl/ui/RouterContainer';
import Navbar from '@/reviactyl/ui/Navbar';
import { LogoContainer } from '@/reviactyl/ui/LogoContainer';
import { XIcon, MenuIcon, ExternalLinkIcon } from '@heroicons/react/solid';
import tw from 'twin.macro';
import { ContentContainer } from '@/reviactyl/ui/ContentContainer';
import { motion } from 'framer-motion';
import Sidebar from '@/reviactyl/ui/Sidebar';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';
import Announcement from '@/reviactyl/ui/Announcement';
import MaintenanceAlert from '@/reviactyl/ui/MaintenanceAlert';
import QuickLinks from '@/reviactyl/ui/QuickLinks';
import Maintenance from '@/reviactyl/ui/Maintenance';
import { useTranslation } from 'react-i18next';
import { FaHouse } from 'react-icons/fa6';
import { ReviactylSidebarButton } from '@/state/reviactyl';

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
            <div>
                <Navigate id='index.dashboard' to='/' end className='mt-2'>
                    <span className='flex items-center'>
                        <FaHouse className='w-5 mr-1' /> {t('index.dashboard')}
                    </span>
                </Navigate>

                <div className='mt-2'>
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
        </>
    );
};

function DashboardRouter() {
    const [isSidebarOpen, setSidebarOpen] = useState(false);
    const logo = useStoreState((state: ApplicationStore) => state.settings.data!.logo);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const isUnderMaintenance = useStoreState((state) => state.reviactyl.data?.isUnderMaintenance);
    const rootAdmin = useStoreState((state) => state.user.data?.rootAdmin);
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
                                className='text-gray-500 bg-gray-700 p-2 rounded-ui'
                            >
                                {isSidebarOpen ? <XIcon className='w-6 h-6' /> : <MenuIcon className='w-6 h-6' />}
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
                                className='fixed inset-0 z-30 bg-gray-800/40 backdrop-blur-sm transition-all duration-300 ease-in-out lg:hidden'
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
                                                <Announcement />
                                                <MaintenanceAlert />
                                                <QuickLinks />
                                                <DashboardContainer />
                                            </>
                                        ),
                                    },
                                    ...routes.account.map(({ route, component: Component }) => ({
                                        path: `/account/${route}`.replace('//', '/'),
                                        element: <Component />,
                                    })),
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
