import { useEffect, useRef } from 'react';
import { ServerContext } from '@/state/server';
import { SocketEvent } from '@/components/server/events';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';
import { Line } from 'react-chartjs-2';
import { useChart, useChartTickLabel } from '@/components/server/console/chart';
import { hexToRgba } from '@/lib/helpers';
import { bytesToString } from '@/lib/formatters';
import { CloudDownloadIcon, CloudUploadIcon } from '@heroicons/react/solid';
import { theme } from 'twin.macro';
import ChartBlock from '@/components/server/console/ChartBlock';
import Tooltip from '@/reviactyl/elements/tooltip/Tooltip';
import { useTranslation } from 'react-i18next';

export default () => {
    const { t } = useTranslation('server/console');
    const status = ServerContext.useStoreState((state) => state.status.value);
    const limits = ServerContext.useStoreState((state) => state.server.data!.limits);
    const previous = useRef<Record<'tx' | 'rx', number>>({ tx: -1, rx: -1 });

    const cpu = useChartTickLabel(t('cpu'), limits.cpu, '%', 2);
    const memory = useChartTickLabel(t('memory'), limits.memory, 'MiB');
    const network = useChart(t('network'), {
        sets: 2,
        options: {
            scales: {
                y: {
                    ticks: {
                        color: `rgb(${getComputedStyle(document.documentElement)
                            .getPropertyValue('--color-400')
                            .trim()})`,
                        callback(value) {
                            return bytesToString(typeof value === 'string' ? parseInt(value, 10) : value);
                        },
                    },
                    grid: {
                        color: `rgb(${getComputedStyle(document.documentElement)
                            .getPropertyValue('--color-600')
                            .trim()})`,
                    },
                },
            },
        },
        callback(opts, index) {
            return {
                ...opts,
                label: !index ? t('network-in') : t('network-out'),
                borderColor: !index ? theme('colors.blue.400') : theme('colors.amber.400'),
                backgroundColor: hexToRgba(!index ? theme('colors.blue.700') : theme('colors.amber.700'), 0.5),
            };
        },
    });

    useEffect(() => {
        if (status === 'offline') {
            cpu.clear();
            memory.clear();
            network.clear();
        }
    }, [status]);

    useWebsocketEvent(SocketEvent.STATS, (data: string) => {
        let values: any = {};
        try {
            values = JSON.parse(data);
        } catch {
            return;
        }
        cpu.push(values.cpu_absolute);
        memory.push(Math.floor(values.memory_bytes / 1024 / 1024));
        network.push([
            previous.current.tx < 0 ? 0 : Math.max(0, values.network.tx_bytes - previous.current.tx),
            previous.current.rx < 0 ? 0 : Math.max(0, values.network.rx_bytes - previous.current.rx),
        ]);

        previous.current = { tx: values.network.tx_bytes, rx: values.network.rx_bytes };
    });

    return (
        <>
            <ChartBlock title={t('cpu-load')}>
                <Line {...cpu.props} />
            </ChartBlock>
            <ChartBlock title={t('memory')}>
                <Line {...memory.props} />
            </ChartBlock>
            <ChartBlock
                title={t('network')}
                legend={
                    <>
                        <Tooltip arrow content={t('inbound')}>
                            <CloudDownloadIcon className={'mr-2 w-4 h-4 text-blue-400'} />
                        </Tooltip>
                        <Tooltip arrow content={t('outbound')}>
                            <CloudUploadIcon className={'w-4 h-4 text-amber-400'} />
                        </Tooltip>
                    </>
                }
            >
                <Line {...network.props} />
            </ChartBlock>
        </>
    );
};
