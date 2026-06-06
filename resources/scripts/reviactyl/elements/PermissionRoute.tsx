import type { ReactNode } from 'react';

import { ServerError } from '@/reviactyl/elements/ScreenBlock';
import { usePermissions } from '@/plugins/usePermissions';

interface Props {
    children?: ReactNode;

    permission?: string | string[] | null;
}

function PermissionRoute({ children, permission }: Props) {
    if (permission === undefined || permission === null) {
        return <>{children}</>;
    }

    const can = usePermissions(permission);

    if (can.filter((p) => p).length > 0) {
        return <>{children}</>;
    }

    return <ServerError title='Access Denied' message='You do not have permission to access this page.' />;
}

export default PermissionRoute;
