import React, { useEffect, useState } from 'react';
import { NavLink } from 'react-router-dom';
import Avatar from '@/reviactyl/ui/Avatar';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import useLogout from '@/plugins/useLogout';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import SearchContainer from '@/components/dashboard/search/SearchContainer';
import Logo from '@/reviactyl/ui/Logo';
import DropdownMenu, { DropdownButtonRow } from '@/reviactyl/elements/DropdownMenu';
import { FaArrowRightToBracket, FaGears, FaUser } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
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
        className={`-mt-1 flex flex-col gap-1 pb-4 [&_.label]:mx-2 [&_.label]:flex [&_.label]:items-center [&_.label]:px-3 [&_.label]:pt-2 [&_.label]:pb-1 [&_.label]:text-sm [&_.label]:font-semibold [&_.label]:text-gray-400 [&_.label]:transition-all [&_.label]:duration-300 [&_a]:mx-2 [&_a]:flex [&_a]:items-center [&_a]:rounded-ui [&_a]:px-5 [&_a]:py-2 [&_a]:text-sm [&_a]:font-medium [&_a]:text-gray-200 [&_a]:transition-all [&_a]:duration-300 [&_a:hover]:bg-gray-700/20 [&_a:focus]:bg-primary-500/10 [&_a:focus]:text-accent [&_a.active]:bg-primary-500/10 [&_a.active]:text-accent ${className}`}
    >
        {children}
    </div>
);

export const SidebarAccent = React.forwardRef<HTMLDivElement, SidebarProps>(
    ({ children, footer, isOpen = false }, ref) => {
        return (
            <div
                ref={ref}
                className={`w-56.25 self-start flex-col bg-gray-900 text-gray-100 transition-transform duration-300 ease-in-out 2xl:w-64 lg:fixed lg:top-[calc(4rem+var(--subuser-preview-offset,0px))] lg:left-0 lg:z-40 lg:flex lg:h-[calc(100dvh-4rem-var(--subuser-preview-offset,0px))] lg:overflow-y-auto lg:bg-transparent ${
                    isOpen
                        ? 'fixed top-[calc(4rem+var(--subuser-preview-offset,0px))] left-0 z-40 flex h-[calc(100dvh-4rem-var(--subuser-preview-offset,0px))] overflow-y-auto'
                        : 'hidden'
                }`}
            >
                <div className='flex flex-1 flex-col overflow-y-auto'>
                    {children ? <SideNavigation>{children}</SideNavigation> : null}
                </div>
                {footer && <div className='shrink-0 border-t border-gray-800 px-3 py-2'>{footer}</div>}
            </div>
        );
    },
);

export const NavbarAccent = ({ children }: NavbarProps) => {
    const { t } = useTranslation('dashboard/account');
    const [blurred, setBlurred] = useState(false);
    const adminAccess = useStoreState((state: ApplicationStore) => state.user.data!.adminAccess);
    const { isLoggingOut, onLogout } = useLogout();
    const { session } = useSubuserPreview();

    useEffect(() => {
        const handleScroll = () => {
            setBlurred(window.scrollY > 0);
        };
        window.addEventListener('scroll', handleScroll);

        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    return (
        <NavbarContainer className={`${blurred ? 'shadow' : ''} bg-gray-900 border-b border-gray-700/50`}>
            <SpinnerOverlay visible={isLoggingOut} />
            <div className='w-full flex items-center justify-between h-full px-4 sm:px-6 md:px-8'>
                <div className='flex items-center gap-4'>
                    {children} <Logo />
                </div>
                <div className='flex grow-0 shrink-0 items-center gap-3 order-last justify-end'>
                    {!session && <SearchContainer />}
                    {!session && (
                        <DropdownMenu
                            renderToggle={(onClick) => (
                                <button onClick={onClick} className='flex items-center w-8 h-8'>
                                    <Avatar />
                                </button>
                            )}
                        >
                            <NavLink to='/account'>
                                <DropdownButtonRow>
                                    <FaUser className='h-4 w-4 inline-flex mr-2' /> {t('overview.profile')}
                                </DropdownButtonRow>
                            </NavLink>
                            {adminAccess && (
                                <a href='/admin' rel='noreferrer'>
                                    <DropdownButtonRow>
                                        <FaGears className='h-4 w-4 inline-flex mr-2' /> {t('overview.admin')}
                                    </DropdownButtonRow>
                                </a>
                            )}
                            <DropdownButtonRow onClick={onLogout} danger>
                                <FaArrowRightToBracket className='h-4 w-4 inline-flex mr-2' /> {t('overview.logout')}
                            </DropdownButtonRow>
                        </DropdownMenu>
                    )}
                </div>
            </div>
        </NavbarContainer>
    );
};
