import React, { useEffect, useRef, useState } from 'react';
import { FaExclamation, FaFloppyDisk, FaGlobe, FaMemory, FaMicrochip } from "react-icons/fa6";
import { Link } from 'react-router-dom';
import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerStats } from '@/api/server/getServerResourceUsage';
import { bytesToString, ip, mbToBytes } from '@/lib/formatters';
import Spinner from '@/components/elements/Spinner';
import Card from '@/reviactyl/ui/Card';
import Title from '@/reviactyl/ui/Title';
import { StatBlock } from '@/reviactyl/ui/StatBlock';
import { useTranslation } from 'react-i18next';
import Blur from '@/reviactyl/ui/Blur';

// Determines if the current value is in an alarm threshold so we can show it in red rather
// than the more faded default style.
const isAlarmState = (current: number, limit: number): boolean => limit > 0 && current / (limit * 1024 * 1024) >= 0.9;

type Timer = ReturnType<typeof setInterval>;

import ChangeCategoryModal from '@/components/dashboard/ChangeCategoryModal';

export default ({ server, className, onCategoryChanged, showCategory = true }: { server: Server; className?: string; onCategoryChanged?: () => void; showCategory?: boolean }) => {
    const { t } = useTranslation('dashboard/index');
    const interval = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    const [isSuspended, setIsSuspended] = useState(server.status === 'suspended');
    const [stats, setStats] = useState<ServerStats | null>(null);
    const [isCategoryModalVisible, setCategoryModalVisible] = useState(false);

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
            interval.current && clearInterval(interval.current);
        };
    }, [isSuspended]);

    const alarms = { cpu: false, memory: false, disk: false };
    if (stats) {
        alarms.cpu = server.limits.cpu === 0 ? false : stats.cpuUsagePercent >= server.limits.cpu * 0.9;
        alarms.memory = isAlarmState(stats.memoryUsageInBytes, server.limits.memory);
        alarms.disk = server.limits.disk === 0 ? false : isAlarmState(stats.diskUsageInBytes, server.limits.disk);
    }

    const diskLimit = server.limits.disk !== 0 ? bytesToString(mbToBytes(server.limits.disk)) : t('server.unlimited');
    const memoryLimit = server.limits.memory !== 0 ? bytesToString(mbToBytes(server.limits.memory)) : t('server.unlimited');
    const cpuLimit = server.limits.cpu !== 0 ? server.limits.cpu + ' %' : t('server.unlimited');

    // Check if server is in a other state (suspended, transferring, etc.)
    const isSpecialState = isSuspended || server.isTransferring || (server.status && !stats);

    return (
        <React.Fragment>
            {showCategory && (
                <ChangeCategoryModal
                    server={server}
                    visible={isCategoryModalVisible}
                    onDismissed={() => {
                        setCategoryModalVisible(false);
                        onCategoryChanged?.();
                    }}
                />
            )}
            <Link to={`/server/${server.id}`} className={className}>
                <Card className='!p-0'>
                    <div
                        className='rounded-ui bg-center bg-cover bg-no-repeat bg-center relative px-6 pt-6 pb-6 z-10'
                        style={{
                            backgroundImage: `url(${server.eggBanner ? server.eggBanner : '/reviactyl/default-bg.png'})`,
                        }}
                    >
                        <div
                            className={'z-[-1] absolute inset-0 rounded-ui backdrop-blur-sm'}
                            css={
                                'background-image: linear-gradient(0deg, rgb(var(--color-700)) 10%, color-mix(in srgb, rgb(var(--color-700)) 35%, transparent) 55%);'
                            }
                        />
                        <div className='flex items-center justify-between pb-5 gap-x-2'>
                            <div className='flex-1 min-w-0'>
                                <Title className='text-2xl truncate' title={server.name}>{server.name}</Title>
                                {showCategory && (
                                    <div
                                        onClick={(e) => {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            setCategoryModalVisible(true);
                                        }}
                                        className={'inline-block text-[10px] px-2 py-0.5 rounded-full mt-1 border transition hover:brightness-110 cursor-pointer'}
                                        style={server.category ? {
                                            backgroundColor: `${server.category.color || '#3b82f6'}20`,
                                            borderColor: server.category.color || '#3b82f6',
                                            color: server.category.color || '#3b82f6',
                                        } : {
                                            backgroundColor: '#334155',
                                            borderColor: '#475569',
                                            color: '#94a3b8',
                                        }}
                                    >
                                        {server.category ? server.category.name : t('categories.set-category')}
                                    </div>
                                )}
                            </div>
                            <span
                                className={`py-1 px-3 text-xs font-medium rounded-ui
                            ${stats?.status === 'offline'
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
                                    ? t('server.offline')
                                    : stats?.status === 'running'
                                        ? t('server.online')
                                        : stats?.status === 'starting'
                                            ? t('server.starting')
                                            : stats?.status === 'stopping'
                                                ? t('server.stopping')
                                                : ''}
                            </span>
                        </div>
                        <div
                            className={`${isSpecialState
                                ? 'flex justify-center items-center min-h-[100px]'
                                : 'grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4'
                                } mt-4`}
                        >
                            {!stats || isSuspended ? (
                                isSuspended ? (
                                    <React.Fragment>
                                        <StatBlock className='bg-danger/50 backdrop-blur-sm border border-danger/80'>
                                            <p>
                                                {server.status === 'suspended'
                                                    ? t('server.suspended')
                                                    : t('server.connection-error')}
                                            </p>
                                        </StatBlock>
                                    </React.Fragment>
                                ) : server.isTransferring || server.status ? (
                                    <React.Fragment>
                                        <StatBlock className='backdrop-blur-sm bg-yellow-500/50 border border-yellow-500/70'>
                                            <span className='w-4 sm:w-5 text-yellow-500'>
                                                <FaExclamation />
                                            </span>
                                            <p>
                                                {' '}
                                                {server.isTransferring
                                                    ? t('server.transferring')
                                                    : server.status === 'installing'
                                                        ? t('server.installing')
                                                        : server.status === 'restoring_backup'
                                                            ? t('server.restoring-backup')
                                                            : t('server.unavailable')}
                                            </p>
                                        </StatBlock>
                                    </React.Fragment>
                                ) : (
                                    <Spinner size={'small'} />
                                )
                            ) : (
                                <React.Fragment>
                                    <StatBlock className='backdrop-blur-sm bg-gray-500/20 border border-gray-500/50'>
                                        <span className='w-4 sm:w-5 text-gray-300'>
                                            <FaGlobe />
                                        </span>
                                        <Blur className={`text-xs sm:text-sm text-gray-100`}>
                                            {server.allocations
                                                .filter((alloc) => alloc.isDefault)
                                                .map((allocation) => (
                                                    <React.Fragment key={allocation.ip + allocation.port.toString()}>
                                                        {allocation.alias || ip(allocation.ip)}:{allocation.port}
                                                    </React.Fragment>
                                                ))}
                                        </Blur>
                                    </StatBlock>
                                    <StatBlock className='backdrop-blur-sm bg-gray-500/20 border border-gray-500/50'>
                                        <span className='w-4 sm:w-5 text-gray-300'>
                                            <FaMicrochip />
                                        </span>
                                        <p className={alarms.cpu ? 'text-danger-50' : ''}>
                                            {stats.cpuUsagePercent.toFixed(2)}%
                                        </p>
                                        <span className='text-xs sm:text-sm text-gray-300'>/ {cpuLimit}</span>
                                    </StatBlock>
                                    <StatBlock className='backdrop-blur-sm bg-gray-500/20 border border-gray-500/50'>
                                        <span className='w-4 sm:w-5 text-gray-300'>
                                            <FaMemory />
                                        </span>
                                        <p className={alarms.memory ? 'text-danger-50' : ''}>
                                            {bytesToString(stats.memoryUsageInBytes)}
                                        </p>
                                        <span className='text-xs sm:text-sm text-gray-300'>/ {memoryLimit}</span>
                                    </StatBlock>
                                    <StatBlock className='backdrop-blur-sm bg-gray-500/20 border border-gray-500/50'>
                                        <span className='w-4 sm:w-5 text-gray-300'>
                                            <FaFloppyDisk />
                                        </span>
                                        <p className={alarms.disk ? 'text-danger-50' : ''}>
                                            {bytesToString(stats.diskUsageInBytes)}
                                        </p>
                                        <span className='text-xs sm:text-sm text-gray-300'>/ {diskLimit}</span>
                                    </StatBlock>
                                </React.Fragment>
                            )}
                        </div>
                    </div>
                </Card>
            </Link>
        </React.Fragment >
    );
};
