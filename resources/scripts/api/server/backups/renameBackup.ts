import http from '@/api/http';

export default async (uuid: string, backup: string, name: string): Promise<void> => {
    await http.post(`/api/client/servers/${uuid}/backups/${backup}/rename`, {
        name,
    });
};
