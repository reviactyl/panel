import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { httpErrorToHuman } from '@/api/http';
import axios from 'axios';
import PageContentBlock from '@/reviactyl/elements/PageContentBlock';
import ContentBox from '@/reviactyl/elements/ContentBox';
import { useStoreActions } from 'easy-peasy';
import { useTranslation } from 'react-i18next';
import { bytesToString } from '@/lib/formatters';

interface Utilization {
    memory_bytes: number;
    cpu_absolute: number;
    disk_bytes: number;
}

interface ServerStatus {
    name: string;
    description: string;
    status: string;
    utilization?: Utilization;
}

const StatusIndicator = ({ status }: { status: string }) => (
    <div
        className='rounded-full w-4 h-4 mr-2'
        style={{
            backgroundColor: status === 'running' ? '#10b981' : status === 'offline' ? '#ef4444' : '#f59e0b',
        }}
    />
);

export default () => {
    const { id } = useParams<{ id: string }>();
    const { t } = useTranslation('strings');
    const [status, setStatus] = useState<ServerStatus | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const { clearFlashes } = useStoreActions((actions) => actions.flashes);

    useEffect(() => {
        clearFlashes();
        setLoading(true);
        setError(null);

        axios
            .get(`/api/public/servers/${id}`)
            .then((response) => {
                setStatus(response.data);
                setLoading(false);
            })
            .catch((error) => {
                console.error(error);
                setError(httpErrorToHuman(error));
                setLoading(false);
            });
    }, [id]);

    return (
        <PageContentBlock title={t('server-status')}>
            <div className='w-full max-w-3xl mx-auto'>
                {error && <div className='mb-4 p-4 bg-red-600 rounded text-white'>{error}</div>}

                {loading ? (
                    <ContentBox>
                        <div className='flex justify-center items-center p-8'>
                            <p className='text-gray-400'>{t('loading-server-status')}</p>
                        </div>
                    </ContentBox>
                ) : status ? (
                    <ContentBox className='relative overflow-hidden'>
                        <div className='p-6'>
                            <h1 className='text-3xl font-bold mb-2 flex items-center'>
                                <StatusIndicator status={status.status} />
                                {status.name}
                            </h1>

                            <p className='text-gray-300 mb-6 whitespace-pre-wrap'>
                                {status.description || t('no-description')}
                            </p>

                            <div className='grid grid-cols-1 md:grid-cols-2 gap-4'>
                                <div className='bg-gray-900 p-4 rounded-lg'>
                                    <h3 className='text-gray-400 text-sm uppercase tracking-wide mb-1'>
                                        {t('status')}
                                    </h3>

                                    <p className='text-2xl font-bold capitalize'>{status.status}</p>
                                </div>
                            </div>

                            {status.utilization && (
                                <div className='mt-6 pt-6 border-t border-gray-800'>
                                    <h2 className='text-xl font-bold mb-4'>{t('resource-usage')}</h2>

                                    <div className='grid grid-cols-1 md:grid-cols-3 gap-4'>
                                        <div className='bg-gray-900 p-3 rounded'>
                                            <div className='text-gray-400 text-xs uppercase mb-1'>{t('cpu')}</div>

                                            <div className='text-lg font-mono'>
                                                {status.utilization.cpu_absolute.toFixed(2)}%
                                            </div>
                                        </div>

                                        <div className='bg-gray-900 p-3 rounded'>
                                            <div className='text-gray-400 text-xs uppercase mb-1'>{t('memory')}</div>

                                            <div className='text-lg font-mono'>
                                                {bytesToString(status.utilization.memory_bytes)}
                                            </div>
                                        </div>

                                        <div className='bg-gray-900 p-3 rounded'>
                                            <div className='text-gray-400 text-xs uppercase mb-1'>{t('disk')}</div>

                                            <div className='text-lg font-mono'>
                                                {bytesToString(status.utilization.disk_bytes)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </ContentBox>
                ) : (
                    !error && (
                        <ContentBox>
                            <div className='flex justify-center items-center p-8'>
                                <p className='text-gray-400'>{t('server-not-found')}</p>
                            </div>
                        </ContentBox>
                    )
                )}
            </div>
        </PageContentBlock>
    );
};
