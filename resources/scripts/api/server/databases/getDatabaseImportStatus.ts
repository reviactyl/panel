import { DatabaseImportStatus, rawDataToDatabaseImportStatus } from '@/api/server/databases/getServerDatabases';
import http from '@/api/http';

export default (uuid: string, database: string): Promise<DatabaseImportStatus | null> => {
    return new Promise((resolve, reject) => {
        http.get(`/api/client/servers/${uuid}/databases/${database}/import`)
            .then((response) => resolve(rawDataToDatabaseImportStatus(response.data.attributes)))
            .catch(reject);
    });
};
