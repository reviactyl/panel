import http from '@/api/http';

export default (ids: string[]): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post('/api/client/account/categories/reorder', { ids })
            .then(() => resolve())
            .catch((error) => reject(error));
    });
};
