import React, { useState } from 'react';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Line } from 'react-chartjs-2';
import { ServerContext } from '@/state/server';
import useSWR from 'swr';
import Spinner from '@/components/elements/Spinner';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import { format } from 'date-fns';
import http from '@/api/http';
import FlashMessageRender from '@/components/FlashMessageRender';
import { ServerError } from '@/components/elements/ScreenBlock';
import tw from 'twin.macro';
import { useTranslation } from 'react-i18next';
import Select from '@/components/elements/Select';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

interface StatPoint {
    timestamp: string;
    cpu_absolute: number;
    memory_bytes: number;
    disk_bytes: number;
    network_rx_bytes: number;
    network_tx_bytes: number;
}

interface StatsResponse {
    data: StatPoint[];
}

type TimeRange = '1' | '3' | '7';

export default () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [days, setDays] = useState<TimeRange>('1');
    const { t } = useTranslation('server/metrics');

    const { data, error, isValidating } = useSWR<StatsResponse>(
        [uuid, '/resources/history', days],
        async (uuid, url, days) => {
            const { data } = await http.get(`/api/client/servers/${uuid}${url}`, {
                params: { days: Number(days) },
            });
            return data;
        }
    );

    if (error) {
        return <ServerError message={t('error')} />;
    }

    if (!data && isValidating) {
        return <Spinner centered />;
    }

    const stats = data?.data || [];

    // Dynamic time format based on selected range
    const timeFormat = days === '1' ? 'HH:00' : 'MM/dd HH:00';

    const chartOptions = {
        responsive: true,
        plugins: {
            legend: {
                position: 'top' as const,
                labels: {
                    color: '#9ca3af',
                },
            },
            title: {
                display: false,
            },
        },
        scales: {
            y: {
                grid: {
                    color: '#374151',
                },
                ticks: {
                    color: '#9ca3af',
                },
            },
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    color: '#9ca3af',
                    maxTicksLimit: 10,
                },
            },
        },
    };

    const cpuData = {
        labels: stats.map((s) => format(new Date(s.timestamp), timeFormat)),
        datasets: [
            {
                label: t('charts.cpu.label'),
                data: stats.map((s) => s.cpu_absolute),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                tension: 0.2,
            },
        ],
    };

    const memoryData = {
        labels: stats.map((s) => format(new Date(s.timestamp), timeFormat)),
        datasets: [
            {
                label: t('charts.memory.label'),
                data: stats.map((s) => Number((s.memory_bytes / 1024 / 1024).toFixed(2))),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.5)',
                tension: 0.2,
            },
        ],
    };

    const diskData = {
        labels: stats.map((s) => format(new Date(s.timestamp), timeFormat)),
        datasets: [
            {
                label: t('charts.disk.label'),
                data: stats.map((s) => Number((s.disk_bytes / 1024 / 1024).toFixed(2))),
                borderColor: 'rgb(245, 158, 11)',
                backgroundColor: 'rgba(245, 158, 11, 0.5)',
                tension: 0.2,
            },
        ],
    };

    const networkData = {
        labels: stats.map((s) => format(new Date(s.timestamp), timeFormat)),
        datasets: [
            {
                label: t('charts.network.rx_label'),
                data: stats.map((s) => Number((s.network_rx_bytes / 1024 / 1024).toFixed(2))),
                borderColor: 'rgb(139, 92, 246)',
                backgroundColor: 'rgba(139, 92, 246, 0.5)',
                tension: 0.2,
            },
            {
                label: t('charts.network.tx_label'),
                data: stats.map((s) => Number((s.network_tx_bytes / 1024 / 1024).toFixed(2))),
                borderColor: 'rgb(236, 72, 153)',
                backgroundColor: 'rgba(236, 72, 153, 0.5)',
                tension: 0.2,
            },
        ],
    };

    return (
        <ServerContentBlock title={t('title')}>
            <FlashMessageRender byKey={'server:metrics'} />
            <div css={tw`mb-4 flex justify-end space-x-2`}>
                <Select
                    value={days}
                    onChange={(e) => setDays(e.target.value as TimeRange)}
                    className={'!w-auto'}
                >
                    <option value={1}>{t('time_range.last_24_hours')}</option>
                    <option value={3}>{t('time_range.last_3_days')}</option>
                    <option value={7}>{t('time_range.last_7_days')}</option>
                </Select>
            </div>

            <div css={tw`grid grid-cols-1 md:grid-cols-2 gap-4`}>
                <div css={tw`bg-gray-700 p-4 rounded-ui border border-gray-600`}>
                    <h3 css={tw`text-gray-200 mb-2 font-semibold`}>{t('charts.cpu.title')}</h3>
                    <div css={tw`h-64`}>
                        <Line options={{ ...chartOptions, maintainAspectRatio: false }} data={cpuData} />
                    </div>
                </div>
                <div css={tw`bg-gray-700 p-4 rounded-ui border border-gray-600`}>
                    <h3 css={tw`text-gray-200 mb-2 font-semibold`}>{t('charts.memory.title')}</h3>
                    <div css={tw`h-64`}>
                        <Line options={{ ...chartOptions, maintainAspectRatio: false }} data={memoryData} />
                    </div>
                </div>
                <div css={tw`bg-gray-700 p-4 rounded-ui border border-gray-600`}>
                    <h3 css={tw`text-gray-200 mb-2 font-semibold`}>{t('charts.disk.title')}</h3>
                    <div css={tw`h-64`}>
                        <Line options={{ ...chartOptions, maintainAspectRatio: false }} data={diskData} />
                    </div>
                </div>
                <div css={tw`bg-gray-700 p-4 rounded-ui border border-gray-600`}>
                    <h3 css={tw`text-gray-200 mb-2 font-semibold`}>{t('charts.network.title')}</h3>
                    <div css={tw`h-64`}>
                        <Line options={{ ...chartOptions, maintainAspectRatio: false }} data={networkData} />
                    </div>
                </div>
            </div>
        </ServerContentBlock>
    );
};