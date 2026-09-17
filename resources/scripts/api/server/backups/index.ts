import http from '@/api/http';
import renameBackup from '@/api/server/backups/renameBackup';

export const restoreServerBackup = async (uuid: string, backup: string, truncate?: boolean): Promise<void> => {
    await http.post(`/api/client/servers/${uuid}/backups/${backup}/restore`, {
        truncate,
    });
};

export { renameBackup };
