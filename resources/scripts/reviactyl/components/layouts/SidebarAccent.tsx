import React, { useEffect, useState } from 'react';
import styled, { css } from 'styled-components';
import tw from 'twin.macro';
import { NavLink } from 'react-router-dom';
import Avatar from '@/reviactyl/ui/Avatar';
import { useStoreState } from 'easy-peasy';
import { ApplicationStore } from '@/state';
import http from '@/api/http';
import SpinnerOverlay from '@/reviactyl/elements/SpinnerOverlay';
import SearchContainer from '@/components/dashboard/search/SearchContainer';
import Logo from '@/reviactyl/ui/Logo';
import DropdownMenu, { DropdownButtonRow } from '@/reviactyl/elements/DropdownMenu';
import { FaArrowRightToBracket, FaGears, FaUser } from 'react-icons/fa6';

interface SidebarProps {
    isOpen?: boolean;
    children?: React.ReactNode;
}

interface NavbarProps {
    children: React.ReactNode;
}

const NavbarContainer = styled.div`
    ${tw`fixed top-0 left-0 w-full h-16 z-50 transition duration-300`}
`;

const SidebarContainer = styled.div<{ $isOpen: boolean }>`
    ${tw`w-[225px] 2xl:w-64 self-start bg-gray-900 lg:bg-transparent text-white flex flex-col z-40 transition-transform duration-300 ease-in-out`};

    ${({ $isOpen }) =>
        $isOpen
            ? css`
                  position: fixed;
                  top: 4rem;
                  inset-inline-start: 0;
              `
            : tw`hidden`}

    height: 100dvh;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;

    @media (min-width: 1024px) {
        position: fixed;
        inset-inline-start: 0;
        display: flex;
        height: 100dvh;
        overflow-y: auto;
    }
`;

const SidebarContent = styled.div`
    ${tw`flex flex-col flex-1 overflow-y-auto`}
`;

export const SideNavigation = styled.div`
    ${tw`flex flex-col gap-1 pb-4 -mt-1`};

    & .label {
        ${tw`flex items-center ml-2 mr-2 px-3 pt-2 pb-1 text-sm font-semibold text-gray-400 transition-all duration-300`};
    }
    a {
        ${tw`flex items-center ml-2 mr-2 px-5 py-2 text-sm font-medium text-gray-200 rounded-ui transition-all duration-300`};

        &:hover {
            background-color: rgb(var(--color-700) / 0.2);
        }

        &:focus,
        &.active {
            ${tw`text-reviactyl`};
            background-color: rgb(var(--color-700) / 0.2);
        }
    }
`;

export const SidebarAccent = React.forwardRef<HTMLDivElement, SidebarProps>(({ children, isOpen = false }, ref) => {
    return (
        <SidebarContainer $isOpen={isOpen} ref={ref}>
            <SidebarContent>{children ? <SideNavigation>{children}</SideNavigation> : null}</SidebarContent>
        </SidebarContainer>
    );
});

export const NavbarAccent = ({ children }: NavbarProps) => {
    const [blurred, setBlurred] = useState(false);
    const rootAdmin = useStoreState((state: ApplicationStore) => state.user.data!.rootAdmin);
    const [isLoggingOut, setIsLoggingOut] = useState(false);

    const onTriggerLogout = () => {
        setIsLoggingOut(true);
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };
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
                    <SearchContainer />
                    <DropdownMenu
                        renderToggle={(onClick) => (
                            <button onClick={onClick} className='flex items-center w-[2rem] h-[2rem]'>
                                <Avatar />
                            </button>
                        )}
                    >
                        <NavLink to='/account'>
                            <DropdownButtonRow>
                                <FaUser className='h-4 w-4 inline-flex mr-2' /> Profile
                            </DropdownButtonRow>
                        </NavLink>
                        {rootAdmin && (
                            <a href='/admin' rel='noreferrer'>
                                <DropdownButtonRow>
                                    <FaGears className='h-4 w-4 inline-flex mr-2' /> Admin
                                </DropdownButtonRow>
                            </a>
                        )}
                        <DropdownButtonRow onClick={onTriggerLogout} danger>
                            <FaArrowRightToBracket className='h-4 w-4 inline-flex mr-2' /> Logout
                        </DropdownButtonRow>
                    </DropdownMenu>
                </div>
            </div>
        </NavbarContainer>
    );
};
