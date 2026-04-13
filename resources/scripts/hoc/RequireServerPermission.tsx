import React from 'react';
import Can from '@/reviactyl/elements/Can';
import { ServerError } from '@/reviactyl/elements/ScreenBlock';

export interface RequireServerPermissionProps {
    permissions: string | string[];
    children?: React.ReactNode;
}

const RequireServerPermission = ({ children, permissions }: RequireServerPermissionProps) => {
    return (
        <Can
            action={permissions}
            renderOnError={
                <ServerError title={'Access Denied'} message={'You do not have permission to access this page.'} />
            }
        >
            {children}
        </Can>
    );
};

export default RequireServerPermission;
