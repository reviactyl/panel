import useSWR from 'swr';
import http from '@/api/http';
import type { ExtensionRegistryRecord } from '@/extensions/types';

interface ExtensionsApiResponse {
    object: string;
    data: ExtensionRegistryRecord[];
}

export const useExtensions = () => {
    return useSWR<ExtensionRegistryRecord[]>(
        '/api/client/extensions',
        async () => {
            const { data } = await http.get<ExtensionsApiResponse>('/api/client/extensions');

            return Array.isArray(data?.data) ? data.data : [];
        },
        {
            revalidateOnFocus: true,
            revalidateOnReconnect: false,
            dedupingInterval: 3_000,
            refreshInterval: 3_000,
            refreshWhenHidden: false,
        }
    );
};
