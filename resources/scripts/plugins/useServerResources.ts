import { useEffect, useRef, useState } from 'react';
import type { Server } from '@/api/server/getServer';
import getServerResourceUsage, { type ServerStats } from '@/api/server/getServerResourceUsage';

export default function useServerResources(server: Server) {
    const ref = useRef<HTMLAnchorElement>(null);
    const [stats, setStats] = useState<ServerStats | null>(null);
    const isSuspended = stats?.isSuspended || server.status === 'suspended';

    useEffect(() => {
        setStats(null);
    }, [server.uuid]);

    useEffect(() => {
        if (isSuspended || server.isNodeUnderMaintenance) return;
        let active = true;
        let visible = false;
        let pending = false;
        let lastUpdated = 0;
        let timer: ReturnType<typeof setTimeout> | undefined;
        const refresh = async () => {
            clearTimeout(timer);
            if (!active || pending || !visible || document.hidden) return;
            const remaining = 30000 - (Date.now() - lastUpdated);
            if (remaining > 0) {
                timer = setTimeout(refresh, remaining);
                return;
            }
            pending = true;
            try {
                const data = await getServerResourceUsage(server.uuid);
                if (active) setStats(data);
            } catch (error) {
                if (active) console.error(error);
            } finally {
                pending = false;
                lastUpdated = Date.now();
                if (active && visible && !document.hidden) timer = setTimeout(refresh, 30000);
            }
        };
        const observer = new IntersectionObserver(
            ([entry]) => {
                visible = entry?.isIntersecting ?? false;
                if (visible) void refresh();
                else clearTimeout(timer);
            },
            { rootMargin: '200px' },
        );
        if (ref.current) observer.observe(ref.current);
        document.addEventListener('visibilitychange', refresh);
        return () => {
            active = false;
            clearTimeout(timer);
            observer.disconnect();
            document.removeEventListener('visibilitychange', refresh);
        };
    }, [server.uuid, isSuspended, server.isNodeUnderMaintenance]);

    return { ref, stats, isSuspended };
}
