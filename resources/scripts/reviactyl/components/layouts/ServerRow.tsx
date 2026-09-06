import React, { memo, useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerStats } from '@/api/server/getServerResourceUsage';
import { bytesToString, ip, mbToBytes } from '@/lib/formatters';
import Spinner from '@/reviactyl/elements/Spinner';
import { FaFloppyDisk, FaMemory, FaMicrochip } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import ChangeCategoryModal from '@/components/dashboard/ChangeCategoryModal';
import Blur from '@/reviactyl/ui/Blur';
import Title from '@/reviactyl/ui/Title';

const isAlarmState = (current: number, limit: number): boolean => limit > 0 && current / (limit * 1024 * 1024) >= 0.9;

const Icon = memo(({ alarm, children }: { alarm: boolean; children: React.ReactNode }) => (
    <div className={alarm ? 'text-danger' : 'text-gray-200'}>{children}</div>
));

const IconDescription = ({ alarm, children }: { alarm: boolean; children: React.ReactNode }) => (
    <p className={`ml-2 text-xs ${alarm ? 'text-white' : 'text-gray-400'}`}>{children}</p>
);

type Timer = ReturnType<typeof setInterval>;

export default ({
    server,
    className,
    onCategoryChanged,
    showCategory = true,
}: {
    server: Server;
    className?: string;
    onCategoryChanged?: () => void;
    showCategory?: boolean;
}) => {
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
        // the server is suspended or the node is under maintenance.
        if (isSuspended || server.isNodeUnderMaintenance) return;

        getStats().then(() => {
            interval.current = setInterval(() => getStats(), 30000);
        });

        return () => {
            void (interval.current && clearInterval(interval.current));
        };
    }, [isSuspended, server.isNodeUnderMaintenance]);

    const alarms = { cpu: false, memory: false, disk: false };
    if (stats) {
        alarms.cpu = server.limits.cpu === 0 ? false : stats.cpuUsagePercent >= server.limits.cpu * 0.9;
        alarms.memory = isAlarmState(stats.memoryUsageInBytes, server.limits.memory);
        alarms.disk = server.limits.disk === 0 ? false : isAlarmState(stats.diskUsageInBytes, server.limits.disk);
    }

    const diskLimit = server.limits.disk !== 0 ? bytesToString(mbToBytes(server.limits.disk)) : t('server.unlimited');
    const memoryLimit =
        server.limits.memory !== 0 ? bytesToString(mbToBytes(server.limits.memory)) : t('server.unlimited');
    const cpuLimit = server.limits.cpu !== 0 ? server.limits.cpu + ' %' : t('server.unlimited');

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
            <Link
                to={`/server/${server.id}`}
                className={`group relative grid grid-cols-12 gap-4 overflow-hidden rounded-ui border border-gray-800 bg-gray-900 p-4 text-gray-200 no-underline ${
                    className || ''
                }`}
            >
                <div className='col-span-12 flex items-center sm:col-span-5 lg:col-span-6'>
                    <img src={server.eggImage ? server.eggImage : '/reviactyl/icon.png'} className='h-10 w-10 mr-4' />
                    <div>
                        <Title className='text-lg break-words'>{server.name}</Title>
                        <div className='flex flex-wrap items-center gap-2'>
                            {showCategory && (
                                <div
                                    onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        setCategoryModalVisible(true);
                                    }}
                                    className='inline-block text-[8px] px-2 py-[2px] rounded-full border transition hover:brightness-110 cursor-pointer'
                                    style={
                                        server.category
                                            ? {
                                                  backgroundColor: `${server.category.color || '#3b82f6'}20`,
                                                  borderColor: server.category.color || '#3b82f6',
                                                  color: server.category.color || '#3b82f6',
                                              }
                                            : {
                                                  backgroundColor: '#334155',
                                                  borderColor: '#475569',
                                                  color: '#94a3b8',
                                              }
                                    }
                                >
                                    {server.category ? server.category.name : t('categories.set-category')}
                                </div>
                            )}

                            {!!server.description && (
                                <p className='m-0 truncate break-words text-sm text-gray-300'>{server.description}</p>
                            )}
                        </div>
                    </div>
                </div>
                <div className='ml-4 hidden flex-1 lg:col-span-2 lg:block'>
                    <div className={'flex justify-center items-center gap-1 text-center'}>
                        <Blur className={`text-sm font-semibold text-gray-400`}>
                            {server.allocations
                                .filter((alloc) => alloc.isDefault)
                                .map((allocation) => (
                                    <React.Fragment key={allocation.ip + allocation.port.toString()}>
                                        {allocation.alias || ip(allocation.ip)}:{allocation.port}
                                    </React.Fragment>
                                ))}
                        </Blur>
                    </div>
                </div>
                <div className='col-span-7 hidden items-baseline justify-center sm:flex lg:col-span-4'>
                    {!stats || isSuspended || server.isNodeUnderMaintenance ? (
                        isSuspended ? (
                            <div className='flex-1 text-center'>
                                <span
                                    className={`text-danger font-medium bg-danger/20 backdrop-blur-sm-xs border border-danger/80 rounded-ui px-2 py-1 text-xs`}
                                >
                                    {server.status === 'suspended'
                                        ? t('server.suspended')
                                        : t('server.connection-error')}
                                </span>
                            </div>
                        ) : server.isNodeUnderMaintenance ? (
                            <div className='flex-1 text-center'>
                                <span
                                    className={`text-yellow-400 font-medium backdrop-blur-sm-xs bg-yellow-500/50 border border-yellow-500/70 rounded-ui px-2 py-1 text-xs`}
                                >
                                    {t('server.maintenance')}
                                </span>
                            </div>
                        ) : server.isTransferring || server.status ? (
                            <div className='flex-1 text-center'>
                                <span
                                    className={`text-yellow-400 font-medium backdrop-blur-sm-xs bg-gray-600/50 border border-gray-500/70 rounded-ui px-2 py-1 text-xs`}
                                >
                                    {server.isTransferring
                                        ? t('server.transferring')
                                        : server.status === 'installing'
                                        ? t('server.installing')
                                        : server.status === 'restoring_backup'
                                        ? t('server.restoring-backup')
                                        : t('server.unavailable')}
                                </span>
                            </div>
                        ) : (
                            <Spinner size={'small'} />
                        )
                    ) : (
                        <React.Fragment>
                            <div className='ml-4 hidden flex-1 sm:block'>
                                <div className='flex justify-center'>
                                    <Icon alarm={alarms.cpu}>
                                        <FaMicrochip />
                                    </Icon>
                                    <IconDescription alarm={alarms.cpu}>
                                        {stats.cpuUsagePercent.toFixed(2)} %
                                    </IconDescription>
                                </div>
                                <p className='mt-1 text-center text-xs font-semibold text-gray-400'>of {cpuLimit}</p>
                            </div>
                            <div className='ml-4 hidden flex-1 sm:block'>
                                <div className='flex justify-center'>
                                    <Icon alarm={alarms.memory}>
                                        <FaMemory />
                                    </Icon>
                                    <IconDescription alarm={alarms.memory}>
                                        {bytesToString(stats.memoryUsageInBytes)}
                                    </IconDescription>
                                </div>
                                <p className='mt-1 text-center text-xs font-semibold text-gray-400'>of {memoryLimit}</p>
                            </div>
                            <div className='ml-4 hidden flex-1 sm:block'>
                                <div className='flex justify-center'>
                                    <Icon alarm={alarms.disk}>
                                        <FaFloppyDisk />
                                    </Icon>
                                    <IconDescription alarm={alarms.disk}>
                                        {bytesToString(stats.diskUsageInBytes)}
                                    </IconDescription>
                                </div>
                                <p className='mt-1 text-center text-xs font-semibold text-gray-400'>of {diskLimit}</p>
                            </div>
                        </React.Fragment>
                    )}
                </div>
                <div
                    className={`absolute right-0 z-20 m-1 h-[calc(100%-0.5rem)] w-2 rounded-full opacity-50 transition-all duration-150 group-hover:opacity-75 ${
                        !stats?.status || stats.status === 'offline'
                            ? 'bg-danger'
                            : stats.status === 'running'
                            ? 'bg-success'
                            : 'bg-yellow-500'
                    }`}
                />
            </Link>
        </React.Fragment>
    );
};
