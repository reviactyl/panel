import http from '@/api/http';

export interface FileDownload {
    identifier: string;
    progress: number;
}

export default (uuid: string): Promise<FileDownload[]> =>
    http
        .get<{ downloads: FileDownload[] }>(`/api/client/servers/${uuid}/files/pull`)
        .then(({ data }) => data.downloads || []);
