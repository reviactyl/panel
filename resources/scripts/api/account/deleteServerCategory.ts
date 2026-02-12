import http from '@/api/http';

export default (uuid: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.delete(`/api/client/account/categories/${uuid}`)
            .then(() => resolve())
            .catch(reject);
    });
};
