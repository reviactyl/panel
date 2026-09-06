import useSWR, { SWRConfiguration, SWRResponse } from 'swr';
import { ActivityLog, Transformers } from '@definitions/user';
import { AxiosError } from 'axios';
import http, { PaginatedResult, QueryBuilderParams, withQueryBuilderParams } from '@/api/http';
import { toPaginatedSet } from '@definitions/helpers';
import useFilteredObject from '@/plugins/useFilteredObject';
import { useUserSWRKey } from '@/plugins/useSWRKey';

export type ActivityLogFilters = QueryBuilderParams<'ip' | 'event', 'timestamp'>;

const useActivityLogs = (
    filters?: ActivityLogFilters,
    config?: SWRConfiguration<PaginatedResult<ActivityLog>, AxiosError>,
): SWRResponse<PaginatedResult<ActivityLog>, AxiosError> => {
    const key = useUserSWRKey(['account', 'activity', JSON.stringify(useFilteredObject(filters || {}))]);

    return useSWR<PaginatedResult<ActivityLog>>(
        key,
        async (): Promise<PaginatedResult<ActivityLog>> => {
            const { data } = await http.get('/api/client/account/activity', {
                params: {
                    ...withQueryBuilderParams(filters),
                    include: ['actor'],
                },
            });

            return toPaginatedSet<ActivityLog>(data, Transformers.toActivityLog);
        },
        { revalidateOnMount: false, ...(config || {}) },
    );
};

export { useActivityLogs };
