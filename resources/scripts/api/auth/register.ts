import http from '@/api/http';

export default (data: any): Promise<any> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/register', data)
            .then((response) => resolve(response.data))
            .catch(reject);
    });
};
