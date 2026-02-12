import http from '@/api/http';

export default (uuid: string, category: string | null): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.put(`/api/client/servers/${uuid}/settings/category`, { category })
            .then(() => resolve())
            .catch((error) => reject(error));
    });
};
