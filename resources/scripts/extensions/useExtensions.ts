import useSWR from 'swr';
import http from '@/api/http';
import type { ExtensionRegistryRecord } from '@/extensions/types';
import { useStoreState } from '@/state/hooks';

interface ExtensionsApiResponse {
    object: string;
    data: ExtensionRegistryRecord[];
}

export const useExtensions = () => {
    const authenticated = useStoreState((state) => !!state.user.data?.uuid);
    return useSWR<ExtensionRegistryRecord[]>(
        authenticated ? '/api/client/extensions' : null,
        async () => {
            const { data } = await http.get<ExtensionsApiResponse>('/api/client/extensions');

            return Array.isArray(data?.data) ? data.data : [];
        },
        {
            revalidateOnFocus: true,
            revalidateOnReconnect: false,
            dedupingInterval: 3_000,
            refreshWhenHidden: false,
        },
    );
};
