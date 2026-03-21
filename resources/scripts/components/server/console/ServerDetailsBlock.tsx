import { useEffect, useState } from 'react';
import { FaClock, FaCloudArrowDown, FaCloudArrowUp } from 'react-icons/fa6';
import { bytesToString } from '@/lib/formatters';
import { ServerContext } from '@/state/server';
import { SocketEvent, SocketRequest } from '@/components/server/events';
import UptimeDuration from '@/components/server/UptimeDuration';
import StatBlock from '@/components/server/console/StatBlock';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';
import classNames from 'classnames';
import { capitalize } from '@/lib/strings';
import { useTranslation } from 'react-i18next';

type Stats = Record<'memory' | 'cpu' | 'disk' | 'uptime' | 'rx' | 'tx', number>;

const getBackgroundColor = (value: number, max: number | null): string | undefined => {
    const delta = !max ? 0 : value / max;

    if (delta > 0.8) {
        if (delta > 0.9) {
            return 'bg-red-500';
        }
        return 'bg-yellow-500';
    }

    return undefined;
};

const ServerDetailsBlock = ({ className }: { className?: string }) => {
    const { t } = useTranslation('server/console');
    const [stats, setStats] = useState<Stats>({ memory: 0, cpu: 0, disk: 0, uptime: 0, tx: 0, rx: 0 });

    const status = ServerContext.useStoreState((state) => state.status.value);
    const connected = ServerContext.useStoreState((state) => state.socket.connected);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);

    useEffect(() => {
        if (!connected || !instance) {
            return;
        }

        instance.send(SocketRequest.SEND_STATS);
    }, [instance, connected]);

    useWebsocketEvent(SocketEvent.STATS, (data) => {
        let stats: any = {};
        try {
            stats = JSON.parse(data);
        } catch {
            return;
        }

        setStats({
            memory: stats.memory_bytes,
            cpu: stats.cpu_absolute,
            disk: stats.disk_bytes,
            tx: stats.network.tx_bytes,
            rx: stats.network.rx_bytes,
            uptime: stats.uptime || 0,
        });
    });

    return (
        <div className={classNames('grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4', className)}>
            <StatBlock
                icon={FaClock}
                title={t('uptime')}
                color={getBackgroundColor(status === 'running' ? 0 : status !== 'offline' ? 9 : 10, 10)}
            >
                {status === null ? (
                    t('offline')
                ) : stats.uptime > 0 ? (
                    <UptimeDuration uptime={stats.uptime / 1000} />
                ) : (
                    capitalize(status)
                )}
            </StatBlock>
            <StatBlock icon={FaCloudArrowDown} title={t('network-inbound')}>
                {status === 'offline' ? (
                    <span className={'text-gray-400'}>{t('offline')}</span>
                ) : (
                    bytesToString(stats.rx)
                )}
            </StatBlock>
            <StatBlock icon={FaCloudArrowUp} title={t('network-outbound')}>
                {status === 'offline' ? (
                    <span className={'text-gray-400'}>{t('offline')}</span>
                ) : (
                    bytesToString(stats.tx)
                )}
            </StatBlock>
        </div>
    );
};

export default ServerDetailsBlock;
