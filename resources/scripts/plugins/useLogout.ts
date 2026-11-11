import { useRef, useState } from 'react';
import logout from '@/api/auth/logout';
import completeLogout, { prepareLogout } from '@/api/auth/completeLogout';
import { httpErrorToHuman, withHttpSessionTransition } from '@/api/http';
import useFlash from '@/plugins/useFlash';

export default function useLogout(key = 'logout') {
    const [isLoggingOut, setIsLoggingOut] = useState(false);
    const pending = useRef(false);
    const { clearFlashes, addFlash } = useFlash();
    const onLogout = async () => {
        if (pending.current) return;
        pending.current = true;
        setIsLoggingOut(true);
        clearFlashes(key);
        const preparation = prepareLogout();
        void preparation.catch(() => undefined);
        try {
            await withHttpSessionTransition(async () => completeLogout(await logout(), preparation));
        } catch (error) {
            addFlash({ key, type: 'error', message: httpErrorToHuman(error) });
        } finally {
            pending.current = false;
            setIsLoggingOut(false);
        }
    };
    return { isLoggingOut, onLogout };
}
