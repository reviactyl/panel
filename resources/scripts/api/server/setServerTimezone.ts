import http from '@/api/http';

export default async (uuid: string, timezone: string | null): Promise<void> => {
    await http.put(`/api/client/servers/${uuid}/settings/timezone`, { timezone });
};
