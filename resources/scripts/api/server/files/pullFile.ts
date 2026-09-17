import http from '@/api/http';

export default (uuid: string, url: string, directory: string): Promise<string> =>
    http
        .post<{ identifier: string }>(`/api/client/servers/${uuid}/files/pull`, { url, directory })
        .then(({ data }) => data.identifier);
