import { useContext, useEffect, useMemo, useRef, useState } from 'react';
import type { Server } from '@/api/server/getServer';
import loadServerResources from '@/api/server/loadServerResources';
import { ServerResourcesContext, type ResourceEntry } from '@/context/ServerResourcesContext';

export default function useServerResources(server: Server) {
    const ref = useRef<HTMLAnchorElement>(null);
    const sharedCache = useContext(ServerResourcesContext);
    const localCache = useMemo(() => new Map<string, ResourceEntry>(), []);
    const cache = sharedCache ?? localCache;
    const entry = useMemo(() => {
        let entry = cache.get(server.uuid);
        if (!entry) {
            entry = { stats: null };
            cache.set(server.uuid, entry);
        }
        return entry;
    }, [cache, server.uuid]);
    const [snapshot, setSnapshot] = useState({ entry, stats: entry.stats });
    const stats = snapshot.entry === entry ? snapshot.stats : entry.stats;
    const isSuspended = stats?.isSuspended || server.status === 'suspended';

    useEffect(() => {
        setSnapshot({ entry, stats: entry.stats });
    }, [entry]);

    useEffect(() => {
        if (isSuspended || server.isNodeUnderMaintenance) return;
        let active = true;
        let visible = false;
        let pending = false;
        let timer: ReturnType<typeof setTimeout> | undefined;
        const refresh = async () => {
            clearTimeout(timer);
            if (!active || pending || !visible || document.hidden) return;
            const remaining = entry.lastUpdated === undefined ? 0 : 30000 - (Date.now() - entry.lastUpdated);
            if (!entry.pending && remaining > 0) {
                timer = setTimeout(refresh, remaining);
                return;
            }
            pending = true;
            try {
                entry.pending ??= loadServerResources(server.uuid)
                    .then((data) => {
                        entry.stats = data.isSuspended ? null : data;
                        return data;
                    })
                    .finally(() => {
                        entry.pending = undefined;
                        entry.lastUpdated = Date.now();
                    });
                const data = await entry.pending;
                if (active) setSnapshot({ entry, stats: data });
            } catch (error) {
                if (active) console.error(error);
            } finally {
                pending = false;
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
    }, [entry, server.uuid, isSuspended, server.isNodeUnderMaintenance]);

    return { ref, stats, isSuspended };
}
