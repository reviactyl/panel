import http from '@/api/http';

export interface ClientEgg {
    id: number;
    name: string;
}

/**
 * Fetches eggs for the dashboard filter. When type is 'admin', returns eggs from
 * "other" servers (for root admins viewing others' servers).
 */
export default (type?: 'admin'): Promise<ClientEgg[]> => {
    return http
        .get('/api/client/eggs', { params: type === 'admin' ? { type: 'admin' } : {} })
        .then(({ data }) => data.data || []);
};
