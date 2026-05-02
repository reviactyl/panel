import React from 'react';
import { NavbarModern, SidebarModern } from '@/reviactyl/components/layouts/SidebarModern';
import { NavbarClassic, SidebarClassic } from '@/reviactyl/components/layouts/SidebarClassic';
import { NavbarCompact, SidebarCompact } from '@/reviactyl/components/layouts/SidebarCompact';
import { NavbarAccent, SidebarAccent } from '@/reviactyl/components/layouts/SidebarAccent';
import { ApplicationStore } from '@/state';
import { useStoreState } from 'easy-peasy';

type Props = React.ComponentProps<typeof SidebarModern> & React.ComponentProps<typeof SidebarClassic>;

export const Sidebar: React.FC<Props> = ({ ...props }) => {
    const layoutType = useStoreState((state: ApplicationStore) => state.designify.data!.layoutType);
    return (
        <>
            {layoutType === 'modern' ? (
                <SidebarModern {...props} />
            ) : layoutType === 'compact' ? (
                <SidebarCompact {...props} />
            ) : layoutType === 'accent' ? (
                <SidebarAccent {...props} />
            ) : (
                <SidebarClassic {...props} />
            )}
        </>
    );
};

export const Navbar = ({ children }: { children: React.ReactNode }) => {
    const layoutType = useStoreState((state: ApplicationStore) => state.designify.data!.layoutType);
    return (
        <>
            {layoutType === 'modern' ? (
                <NavbarModern>{children}</NavbarModern>
            ) : layoutType === 'compact' ? (
                <NavbarCompact>{children}</NavbarCompact>
            ) : layoutType === 'accent' ? (
                <NavbarAccent>{children}</NavbarAccent>
            ) : (
                <NavbarClassic>{children}</NavbarClassic>
            )}
        </>
    );
};
