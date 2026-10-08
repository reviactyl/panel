import { DatabaseImportStatus, rawDataToDatabaseImportStatus } from '@/api/server/databases/getServerDatabases';
import http from '@/api/http';

export interface RemoteDatabase {
    host: string;
    port: number;
    database: string;
    username: string;
    password: string;
}

interface Data {
    wipe: boolean;
    file?: File;
    remote?: RemoteDatabase;
}

export default (
    uuid: string,
    database: string,
    { wipe, file, remote }: Data,
    onUploadProgress?: (progress: number) => void,
): Promise<DatabaseImportStatus | null> => {
    const url = `/api/client/servers/${uuid}/databases/${database}/import`;

    return new Promise((resolve, reject) => {
        let request;
        if (remote) {
            request = http.post(url, {
                wipe,
                remote_host: remote.host,
                remote_port: remote.port,
                remote_database: remote.database,
                remote_username: remote.username,
                remote_password: remote.password,
            });
        } else {
            const data = new FormData();
            data.append('wipe', wipe ? '1' : '0');
            data.append('file', file!);

            request = http.post(url, data, {
                timeout: 0,
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (event) => onUploadProgress?.(event.total ? event.loaded / event.total : 0),
            });
        }

        request.then((response) => resolve(rawDataToDatabaseImportStatus(response.data.attributes))).catch(reject);
    });
};
