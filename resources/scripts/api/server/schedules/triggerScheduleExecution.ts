import http from '@/api/http';

export default async (server: string, schedule: number): Promise<{ skipped: boolean }> => {
    const { data } = await http.post(`/api/client/servers/${server}/schedules/${schedule}/execute`);

    return { skipped: data.skipped === true };
};
