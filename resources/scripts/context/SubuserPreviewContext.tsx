import axios from 'axios';
import { createContext, PropsWithChildren, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import {
    clearPreviewToken,
    getPreviewToken,
    getSubuserPreviewStatus,
    heartbeatSubuserPreview,
    startSubuserPreview,
    stopSubuserPreview,
    storePreviewToken,
    SubuserPreviewSession,
    SubuserPreviewStatus,
} from '@/api/subuserPreview';

interface ContextValue {
    loading: boolean;
    session: SubuserPreviewSession | null;
    start: (serverUuid: string, subuserUuid: string, replace?: boolean) => Promise<SubuserPreviewStatus>;
    exit: () => Promise<void>;
}

const Context = createContext<ContextValue | null>(null);
const PREVIEW_HEARTBEAT_INTERVAL = 10_000;

export const SubuserPreviewProvider = ({ children }: PropsWithChildren) => {
    const [loading, setLoading] = useState(() => getPreviewToken() !== null);
    const [session, setSession] = useState<SubuserPreviewSession | null>(null);

    const clear = useCallback(() => {
        clearPreviewToken();
        setSession(null);
    }, []);

    const expire = useCallback(() => {
        clear();
        window.location.assign('/');
    }, [clear]);

    useEffect(() => {
        if (!getPreviewToken()) {
            setLoading(false);
            return;
        }

        getSubuserPreviewStatus()
            .then((status) => {
                if (status.active && status.ownedByTab && status.session) {
                    setSession(status.session);
                } else {
                    clear();
                }
            })
            .catch(clear)
            .finally(() => setLoading(false));
    }, [clear]);

    useEffect(() => {
        document.documentElement.style.setProperty('--subuser-preview-offset', session ? '64px' : '0px');

        return () => document.documentElement.style.setProperty('--subuser-preview-offset', '0px');
    }, [session]);

    useEffect(() => {
        if (!session) return;

        const interval = window.setInterval(() => {
            heartbeatSubuserPreview()
                .then((status) => status.session && setSession(status.session))
                .catch((error) => {
                    if (axios.isAxiosError(error) && [401, 403, 409].includes(error.response?.status ?? 0)) {
                        expire();
                    }
                });
        }, PREVIEW_HEARTBEAT_INTERVAL);

        return () => window.clearInterval(interval);
    }, [expire, session?.uuid]);

    const start = useCallback(async (serverUuid: string, subuserUuid: string, replace = false) => {
        const status = await startSubuserPreview(serverUuid, subuserUuid, replace);

        if (status.token && status.session) {
            storePreviewToken(status.token);
            setSession(status.session);
        }

        return status;
    }, []);

    const exit = useCallback(async () => {
        try {
            await stopSubuserPreview();
        } finally {
            clear();
        }
    }, [clear]);

    const value = useMemo(() => ({ loading, session, start, exit }), [exit, loading, session, start]);

    return <Context.Provider value={value}>{children}</Context.Provider>;
};

export const useSubuserPreview = (): ContextValue => {
    const value = useContext(Context);
    if (!value) throw new Error('useSubuserPreview must be used within SubuserPreviewProvider.');

    return value;
};
