import React, { memo, useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerPowerState, ServerStats } from '@/api/server/getServerResourceUsage';
import { bytesToString, ip, mbToBytes } from '@/lib/formatters';
import tw from 'twin.macro';
import GreyRowBox from '@/reviactyl/elements/GreyRowBox';
import Spinner from '@/reviactyl/elements/Spinner';
import styled from 'styled-components';
import isEqual from 'react-fast-compare';
import { FaFloppyDisk, FaMemory, FaMicrochip } from 'react-icons/fa6';
import { useTranslation } from 'react-i18next';
import ChangeCategoryModal from '@/components/dashboard/ChangeCategoryModal';
import Blur from '@/reviactyl/ui/Blur';
import Title from '@/reviactyl/ui/Title';

const isAlarmState = (current: number, limit: number): boolean => limit > 0 && current / (limit * 1024 * 1024) >= 0.9;

const Icon = memo(
    styled.div<{ $alarm: boolean }>`
        ${(props) => (props.$alarm ? tw`text-danger` : tw`text-gray-200`)};
    `,
    isEqual
);

const IconDescription = styled.p<{ $alarm: boolean }>`
    ${tw`text-xs ml-2`};
    ${(props) => (props.$alarm ? tw`text-white` : tw`text-gray-400`)};
`;

const StatusIndicatorBox = styled(GreyRowBox)<{ $status: ServerPowerState | undefined }>`
    ${tw`grid grid-cols-12 gap-4 relative overflow-hidden bg-gray-900 border border-gray-800 rounded-ui`};

    & .status-bar {
        ${tw`w-2 absolute right-0 z-20 rounded-full m-1 opacity-50 transition-all duration-150`};
        height: calc(100% - 0.5rem);

        ${({ $status }) =>
            !$status || $status === 'offline'
                ? tw`bg-danger`
                : $status === 'running'
                ? tw`bg-success`
                : tw`bg-yellow-500`};
    }

    &:hover .status-bar {
        ${tw`opacity-75`};
    }
`;

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

    const diskLimit = server.limits.disk !== 0 ? bytesToString(mbToBytes(server.limits.disk)) : t('server.unlimited');
    const memoryLimit = server.limits.memory !== 0 ? bytesToString(mbToBytes(server.limits.memory)) : t('server.unlimited');
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
            <StatusIndicatorBox as={Link} to={`/server/${server.id}`} className={className} $status={stats?.status}>
                <div css={tw`flex items-center col-span-12 sm:col-span-5 lg:col-span-6`}>
                    <img src={server.eggImage ? server.eggImage : '/reviactyl/icon.png'} className='h-10 w-10 mr-4' />
                    <div>
                        <Title css={tw`text-lg break-words`}>{server.name}</Title>
                        <div css={tw`flex items-center gap-2 flex-wrap`}>
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
                                <p css={tw`text-sm text-gray-300 break-words m-0 truncate`}>{server.description}</p>
                            )}
                        </div>
                    </div>
                </div>
                <div css={tw`flex-1 ml-4 lg:block lg:col-span-2 hidden`}>
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
                <div css={tw`hidden col-span-7 lg:col-span-4 sm:flex items-baseline justify-center`}>
                    {!stats || isSuspended ? (
                        isSuspended ? (
                            <div css={tw`flex-1 text-center`}>
                                <span css={tw`bg-red-500 rounded px-2 py-1 text-red-100 text-xs`}>
                                    {server.status === 'suspended'
                                        ? t('server.suspended')
                                        : t('server.connection-error')}
                                </span>
                            </div>
                        ) : server.isTransferring || server.status ? (
                            <div css={tw`flex-1 text-center`}>
                                <span css={tw`bg-gray-600 rounded-ui px-2 py-1 text-gray-100 text-xs`}>
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
                            <div css={tw`flex-1 ml-4 sm:block hidden`}>
                                <div css={tw`flex justify-center`}>
                                    <Icon $alarm={alarms.cpu}>
                                        <FaMicrochip />
                                    </Icon>
                                    <IconDescription $alarm={alarms.cpu}>
                                        {stats.cpuUsagePercent.toFixed(2)} %
                                    </IconDescription>
                                </div>
                                <p css={tw`text-xs text-gray-400 font-semibold text-center mt-1`}>of {cpuLimit}</p>
                            </div>
                            <div css={tw`flex-1 ml-4 sm:block hidden`}>
                                <div css={tw`flex justify-center`}>
                                    <Icon $alarm={alarms.memory}>
                                        <FaMemory />
                                    </Icon>
                                    <IconDescription $alarm={alarms.memory}>
                                        {bytesToString(stats.memoryUsageInBytes)}
                                    </IconDescription>
                                </div>
                                <p css={tw`text-xs text-gray-400 font-semibold text-center mt-1`}>of {memoryLimit}</p>
                            </div>
                            <div css={tw`flex-1 ml-4 sm:block hidden`}>
                                <div css={tw`flex justify-center`}>
                                    <Icon $alarm={alarms.disk}>
                                        <FaFloppyDisk />
                                    </Icon>
                                    <IconDescription $alarm={alarms.disk}>
                                        {bytesToString(stats.diskUsageInBytes)}
                                    </IconDescription>
                                </div>
                                <p css={tw`text-xs text-gray-400 font-semibold text-center mt-1`}>of {diskLimit}</p>
                            </div>
                        </React.Fragment>
                    )}
                </div>
                <div className={'status-bar'} />
            </StatusIndicatorBox>
        </React.Fragment>
    );
};
