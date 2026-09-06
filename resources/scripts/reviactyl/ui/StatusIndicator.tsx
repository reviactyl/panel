import React, { useEffect, useRef, useState } from 'react';
import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerStats } from '@/api/server/getServerResourceUsage';

// Determines if the current value is in an alarm threshold so we can show it in red rather
// than the more faded default style.
const isAlarmState = (current: number, limit: number): boolean => limit > 0 && current / (limit * 1024 * 1024) >= 0.9;

type Timer = ReturnType<typeof setInterval>;

export default ({ server }: { server: Server }) => {
    const interval = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    const [isSuspended, setIsSuspended] = useState(server.status === 'suspended');
    const [stats, setStats] = useState<ServerStats | null>(null);

    const getStats = () =>
        getServerResourceUsage(server.uuid)
            .then((data) => setStats(data))
            .catch((error) => console.error(error));

    useEffect(() => {
        setIsSuspended(stats?.isSuspended || server.status === 'suspended');
    }, [stats?.isSuspended, server.status]);

    useEffect(() => {
        // Don't waste a HTTP request if there is nothing important to show to the user because
        // the server is suspended.
        if (isSuspended) return;

        getStats().then(() => {
            interval.current = setInterval(() => getStats(), 30000);
        });

        return () => {
            void (interval.current && clearInterval(interval.current));
        };
    }, [isSuspended]);

    const alarms = { cpu: false, memory: false, disk: false };
    if (stats) {
        alarms.cpu = server.limits.cpu === 0 ? false : stats.cpuUsagePercent >= server.limits.cpu * 0.9;
        alarms.memory = isAlarmState(stats.memoryUsageInBytes, server.limits.memory);
        alarms.disk = server.limits.disk === 0 ? false : isAlarmState(stats.diskUsageInBytes, server.limits.disk);
    }
    return (
        <>
            <span
                className={`py-1 px-3 text-xs font-medium rounded-ui
                        ${
                            stats?.status === 'offline'
                                ? 'bg-danger/20 text-danger border border-danger/30'
                                : stats?.status === 'running'
                                  ? 'bg-success/20 text-success border border-success/30'
                                  : stats?.status === 'starting'
                                    ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30'
                                    : stats?.status === 'stopping'
                                      ? 'bg-orange-500/20 text-orange-400 border border-orange-500/30'
                                      : ''
                        }
                    `}
            >
                {stats?.status === 'offline'
                    ? 'Offline'
                    : stats?.status === 'running'
                      ? 'Online'
                      : stats?.status === 'starting'
                        ? 'Starting'
                        : stats?.status === 'stopping'
                          ? 'Stopping'
                          : ''}
            </span>
        </>
    );
};
