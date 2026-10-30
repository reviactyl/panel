import { useEffect, type ReactNode } from 'react';
import { useLocation } from 'react-router-dom';

import { useStoreState } from '@/state/hooks';

function AuthenticatedRoute({ children }: { children?: ReactNode }) {
    const isAuthenticated = useStoreState((state) => !!state.user.data?.uuid);

    const location = useLocation();

    useEffect(() => {
        if (!isAuthenticated) {
            window.location.replace(location.pathname + location.search + location.hash);
        }
    }, [isAuthenticated, location.pathname, location.search, location.hash]);

    if (isAuthenticated) {
        return <>{children}</>;
    }

    return null;
}

export default AuthenticatedRoute;
