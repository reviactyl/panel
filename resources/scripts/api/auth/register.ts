import http from '@/api/http';

export default (data: any): Promise<any> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/register', data)
            .then((response) => {
                return resolve({
                    complete: response.data.data.complete,
                    intended: response.data.data.intended || undefined,
                });
            })
            .catch(reject);
    });
};
