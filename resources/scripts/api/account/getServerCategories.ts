import http from '@/api/http';
import { ServerCategory } from '@/api/server/types';

export { ServerCategory };

export default (): Promise<ServerCategory[]> => {
    return new Promise((resolve, reject) => {
        http.get('/api/client/account/categories')
            .then(({ data }) =>
                resolve(
                    (data.data || []).map((datum: any) => ({
                        uuid: datum.attributes.uuid,
                        name: datum.attributes.name,
                        description: datum.attributes.description,
                        color: datum.attributes.color,
                        createdAt: new Date(datum.attributes.created_at),
                        updatedAt: new Date(datum.attributes.updated_at),
                    }))
                )
            )
            .catch(reject);
    });
};
