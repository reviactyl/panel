import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Avatar from '@/reviactyl/ui/Avatar';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import { ExternalLinkIcon } from '@heroicons/react/solid';
import { useTranslation } from 'react-i18next';
import http from '@/api/http';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import Logo from '@/reviactyl/ui/Logo';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { FaArrowRightToBracket, FaEye } from 'react-icons/fa6';
import { useSubuserPreview } from '@/context/SubuserPreviewContext';

interface SidebarProps {
    isOpen?: boolean;
    children?: React.ReactNode;
}

interface NavbarProps {
    children: React.ReactNode;
}

const NavbarContainer = ({ children, className = '' }: React.HTMLAttributes<HTMLDivElement>) => (
    <div
        className={`fixed top-[var(--subuser-preview-offset,0px)] left-0 z-50 h-16 w-full transition duration-300 ${className}`}
    >
        {children}
    </div>
);

export const SideNavigation = ({ children, className = '' }: React.HTMLAttributes<HTMLDivElement>) => (
    <div
        className={`-mt-1 flex flex-col gap-1 pb-4 [&_.label]:mx-2 [&_.label]:flex [&_.label]:items-center [&_.label]:px-3 [&_.label]:pt-2 [&_.label]:pb-1 [&_.label]:text-sm [&_.label]:font-semibold [&_.label]:text-gray-400 [&_a]:mx-2 [&_a]:flex [&_a]:items-center [&_a]:rounded-ui [&_a]:px-5 [&_a]:py-2 [&_a]:text-sm [&_a]:font-medium [&_a]:text-gray-200 [&_a:hover]:bg-gray-700/20 [&_a:focus]:bg-gray-700/20 [&_a:focus]:text-reviactyl [&_a.active]:bg-gray-700/20 [&_a.active]:text-reviactyl ${className}`}
    >
        {children}
    </div>
);

export const SidebarModern = React.forwardRef<HTMLDivElement, SidebarProps>(({ children, isOpen = false }, ref) => {
    const { t } = useTranslation('dashboard/account');
    const { t: tUsers } = useTranslation('server/users');
    const nameFirst = useStoreState((state) => state.user.data?.name_first);
    const nameLast = useStoreState((state) => state.user.data?.name_last);
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const name = useStoreState((state: ApplicationStore) => state.settings.data!.name);
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const sidebarLogout = useStoreState((state) => state.designify.data?.sidebarLogout);
    const { session } = useSubuserPreview();

    const onLogout = () => {
        setIsLoggingOut(true);
        http.post('/auth/logout').finally(() => {
            window.location.href = '/';
        });
    };

    return (
        <div
            ref={ref}
            className={`m-2 w-[225px] self-start flex-col rounded-ui border border-gray-800 bg-gray-900 text-white 2xl:w-64 lg:fixed lg:top-[var(--subuser-preview-offset,0px)] lg:left-0 lg:z-40 lg:flex lg:h-[calc(100dvh-15px-var(--subuser-preview-offset,0px))] lg:overflow-y-auto ${
                isOpen
                    ? 'fixed top-[calc(1rem+var(--subuser-preview-offset,0px))] left-0 z-40 flex h-[calc(100dvh-64px-var(--subuser-preview-offset,0px))] overflow-y-auto'
                    : 'hidden'
            }`}
        >
            <SpinnerOverlay visible={isLoggingOut} />
            <div className='sticky top-0 z-10 border-b border-gray-800 bg-gray-900'>
                <div className='py-3 px-3'>
                    <Logo nostyles />
                </div>
            </div>

            <div className='flex flex-1 flex-col overflow-y-auto'>
                {children ? <SideNavigation>{children}</SideNavigation> : null}
            </div>

            <div className='sticky bottom-0 z-10 border-t border-gray-800 bg-gray-900 p-3'>
                {session ? (
                    <div className='flex min-w-0 items-center gap-3'>
                        <FaEye className='h-5 w-5 shrink-0 text-amber-400' />
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
                                <span className='text-xs tracking-widest uppercase text-white/50'>
                                    {rootAdmin ? t('overview.administrator') : `${name} ${t('overview.user')}`}
                                </span>
                                {rootAdmin && (
                                    // eslint-disable-next-line react/jsx-no-target-blank
                                    <a href={`/admin`} target={'_blank'} className='h-5 w-5 text-white/70'>
                                        <ExternalLinkIcon />
                                    </a>
                                )}
                            </div>
                            <Link to='/account'>
                                <span className='text-sm font-semibold'>
                                    {nameFirst} {nameLast}
                                </span>
                            </Link>
                        </div>
                        {sidebarLogout && (
                            <div onClick={onLogout}>
                                <Tooltip content={t('overview.logout')}>
                                    <FaArrowRightToBracket className='h-10 text-gray-600 hover:text-danger/80 cursor-pointer' />
                                </Tooltip>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
});

export const NavbarModern = ({ children }: NavbarProps) => {
    const [blurred, setBlurred] = useState(false);

    useEffect(() => {
        const handleScroll = () => {
            setBlurred(window.scrollY > 0);
        };
        window.addEventListener('scroll', handleScroll);

        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <NavbarContainer className={`${blurred ? 'shadow' : ''} lg:hidden block`}>
            <div className='w-full mt-1 flex items-center justify-between h-full px-4 sm:px-6 md:px-8 !pr-2'>
                <div className='flex items-center gap-4'></div>
                <div className='flex grow-0 shrink-0 items-center gap-3 order-last justify-end'>{children}</div>
            </div>
        </NavbarContainer>
    );
};
