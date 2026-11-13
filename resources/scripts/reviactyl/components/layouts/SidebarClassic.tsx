import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Avatar from '@/reviactyl/ui/Avatar';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { ExternalLinkIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';
import useLogout from '@/plugins/useLogout';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import SearchContainer from '@/components/dashboard/search/SearchContainer';
import Logo from '@/reviactyl/ui/Logo';
import { FaArrowRightToBracket, FaEye } from 'react-icons/fa6';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';

interface SidebarProps {
    isOpen?: boolean;
    footer?: React.ReactNode;
    children?: React.ReactNode;
}

interface NavbarProps {
    children: React.ReactNode;
}

const NavbarContainer = ({ children, className = '' }: React.HTMLAttributes<HTMLDivElement>) => (
    <div
        className={`fixed top-(--subuser-preview-offset,0px) left-0 z-50 h-16 w-full transition duration-300 ${className}`}
    >
        {children}
    </div>
);

export const SideNavigation = ({ children, className = '' }: React.HTMLAttributes<HTMLDivElement>) => (
    <div
        className={`-mt-1 flex flex-col gap-1 pb-4 [&_.label]:mx-2 [&_.label]:flex [&_.label]:items-center [&_.label]:px-3 [&_.label]:pt-2 [&_.label]:pb-1 [&_.label]:text-sm [&_.label]:font-semibold [&_.label]:text-gray-400 [&_a]:mx-2 [&_a]:flex [&_a]:items-center [&_a]:rounded-ui [&_a]:px-5 [&_a]:py-2 [&_a]:text-sm [&_a]:font-medium [&_a]:text-gray-200 [&_a:hover]:bg-gray-700/20 [&_a:focus]:bg-primary-500/10 [&_a:focus]:text-accent [&_a.active]:bg-primary-500/10 [&_a.active]:text-accent ${className}`}
    >
        {children}
    </div>
);

export const SidebarClassic = React.forwardRef<HTMLDivElement, SidebarProps>(
    ({ children, footer, isOpen = false }, ref) => {
        const { t } = useTranslation('dashboard/account');
        const { t: tUsers } = useTranslation('server/users');
        const nameFirst = useStoreState((state) => state.user.data?.name_first);
        const nameLast = useStoreState((state) => state.user.data?.name_last);
        const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
        const adminAccess = useStoreState((state) => state.user.data!.adminAccess);
        const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
        const { isLoggingOut, onLogout } = useLogout();
        const sidebarLogout = useStoreState((state) => state.designify.data?.sidebarLogout);
        const { session } = useSubuserPreview();

        return (
            <div
                ref={ref}
                className={`m-2 w-56.25 self-start flex-col rounded-ui border border-gray-800 bg-gray-900 text-gray-100 2xl:w-64 lg:fixed lg:top-[calc(64px+var(--subuser-preview-offset,0px))] lg:left-0 lg:z-40 lg:flex lg:h-[calc(100dvh-100px-var(--subuser-preview-offset,0px))] lg:overflow-y-auto ${
                    isOpen
                        ? 'fixed top-[calc(4rem+var(--subuser-preview-offset,0px))] left-0 z-40 flex h-[calc(100dvh-64px-var(--subuser-preview-offset,0px))] overflow-y-auto'
                        : 'hidden'
                }`}
            >
                <SpinnerOverlay visible={isLoggingOut} />
                <div className='flex flex-1 flex-col overflow-y-auto'>
                    {children ? <SideNavigation>{children}</SideNavigation> : null}
                </div>
                {footer && <div className='shrink-0 border-t border-gray-800 px-3 py-2'>{footer}</div>}
                <div className='shrink-0 border-t border-gray-800 bg-gray-900 p-4'>
                    {session ? (
                        <div className='flex min-w-0 items-center gap-3'>
                            <span className='flex rounded-full items-center justify-center bg-amber-900/50 border-amber-800/50 h-10 w-10'>
                                <FaEye className='h-5 w-5 shrink-0 text-amber-700 dark:text-amber-400' />
                            </span>
                            <div className='min-w-0'>
                                <p className='text-xs font-semibold text-gray-100'>{tUsers('preview.title')}</p>
                                <p className='truncate text-xs text-gray-400'>{session.subuserEmail}</p>
                            </div>
                        </div>
                    ) : (
                        <div className='flex items-center gap-3'>
                            <Link to='/account'>
                                <Avatar className='w-10' />
                            </Link>
                            <div className='flex flex-col'>
                                <div className='flex items-center gap-x-1'>
                                    <span className='text-xs tracking-widest uppercase text-muted'>
                                        {rootAdmin ? t('overview.administrator') : `${name} ${t('overview.user')}`}
                                    </span>
                                    {adminAccess && (
                                        // eslint-disable-next-line react/jsx-no-target-blank
                                        <a href={`/admin`} target={'_blank'} className='h-5 w-5 text-gray-400'>
                                            <ExternalLinkIcon />
                                        </a>
                                    )}
                                </div>
                                <Link to='/account'>
                                    <span className='text-sm font-semibold text-gray-400'>
                                        {nameFirst} {nameLast}
                                    </span>
                                </Link>
                            </div>
                            {sidebarLogout && (
                                <button
                                    type='button'
                                    onClick={onLogout}
                                    aria-label={t('overview.logout')}
                                    className='rounded-ui focus-visible:outline-solid focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-reviactyl'
                                >
                                    <Tooltip content={t('overview.logout')}>
                                        <FaArrowRightToBracket className='h-10 text-muted hover:text-danger-text cursor-pointer' />
                                    </Tooltip>
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        );
    },
);

export const NavbarClassic = ({ children }: NavbarProps) => {
    const [blurred, setBlurred] = useState(false);
    const { session } = useSubuserPreview();

    useEffect(() => {
        const handleScroll = () => {
            setBlurred(window.scrollY > 0);
        };
        window.addEventListener('scroll', handleScroll);

        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <NavbarContainer className={`${blurred ? 'backdrop-blur-lg shadow' : ''}`}>
            <div className='w-full mt-1 flex items-center justify-between h-full px-4 sm:px-6 md:px-8'>
                <div className='flex items-center gap-4'>
                    {children} <Logo />
                </div>
                <div className='flex grow-0 shrink-0 items-center gap-3 order-last justify-end'>
                    {!session && <SearchContainer />}
                </div>
            </div>
        </NavbarContainer>
    );
};
