import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import tw from 'twin.macro';
import { httpErrorToHuman } from '@/api/http';
import axios from 'axios';
import PageContentBlock from '@/reviactyl/elements/PageContentBlock';
import ContentBox from '@/reviactyl/elements/ContentBox';
import { useStoreActions } from 'easy-peasy';
import styled from 'styled-components';
import { useTranslation } from 'react-i18next';

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

import { bytesToString } from '@/lib/formatters';

const StatusIndicator = styled.div<{ status: string }>`
    ${tw`rounded-full w-4 h-4 mr-2`};
    background-color: ${({ status }) =>
        status === 'running' ? '#10b981' : status === 'offline' ? '#ef4444' : '#f59e0b'};
`;

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
            <div css={tw`w-full max-w-3xl mx-auto`}>
                {error && <div css={tw`mb-4 p-4 bg-red-600 rounded text-white`}>{error}</div>}

                {loading ? (
                    <ContentBox>
                        <div css={tw`flex justify-center items-center p-8`}>
                            <p css={tw`text-gray-400`}>{t('loading-server-status')}</p>
                        </div>
                    </ContentBox>
                ) : status ? (
                    <ContentBox css={tw`relative overflow-hidden`}>
                        <div css={tw`p-6`}>
                            <h1 css={tw`text-3xl font-bold mb-2 flex items-center`}>
                                <StatusIndicator status={status.status} />
                                {status.name}
                            </h1>
                            <p css={tw`text-gray-300 mb-6 whitespace-pre-wrap`}>
                                {status.description || t('no-description')}
                            </p>

                            <div css={tw`grid grid-cols-1 md:grid-cols-2 gap-4`}>
                                <div css={tw`bg-gray-900 p-4 rounded-lg`}>
                                    <h3 css={tw`text-gray-400 text-sm uppercase tracking-wide mb-1`}>{t('status')}</h3>
                                    <p css={tw`text-2xl font-bold capitalize`}>{status.status}</p>
                                </div>
                            </div>

                            {status.utilization && (
                                <div css={tw`mt-6 pt-6 border-t border-gray-800`}>
                                    <h2 css={tw`text-xl font-bold mb-4`}>{t('resource-usage')}</h2>
                                    <div css={tw`grid grid-cols-1 md:grid-cols-3 gap-4`}>
                                        <div css={tw`bg-gray-900 p-3 rounded`}>
                                            <div css={tw`text-gray-400 text-xs uppercase mb-1`}>{t('cpu')}</div>
                                            <div css={tw`text-lg font-mono`}>
                                                {status.utilization.cpu_absolute.toFixed(2)}%
                                            </div>
                                        </div>
                                        <div css={tw`bg-gray-900 p-3 rounded`}>
                                            <div css={tw`text-gray-400 text-xs uppercase mb-1`}>{t('memory')}</div>
                                            <div css={tw`text-lg font-mono`}>
                                                {bytesToString(status.utilization.memory_bytes)}
                                            </div>
                                        </div>
                                        <div css={tw`bg-gray-900 p-3 rounded`}>
                                            <div css={tw`text-gray-400 text-xs uppercase mb-1`}>{t('disk')}</div>
                                            <div css={tw`text-lg font-mono`}>
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
                            <div css={tw`flex justify-center items-center p-8`}>
                                <p css={tw`text-gray-400`}>{t('server-not-found')}</p>
                            </div>
                        </ContentBox>
                    )
                )}
            </div>
        </PageContentBlock>
    );
};
