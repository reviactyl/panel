import http from '@/api/http';
import { ServerCategory } from '@/api/server/types';

export default (uuid: string, data: { name?: string; description?: string; color?: string }): Promise<ServerCategory> => {
    return new Promise((resolve, reject) => {
        http.put(`/api/client/account/categories/${uuid}`, data)
            .then(({ data }) => resolve({
                uuid: data.attributes.uuid,
                name: data.attributes.name,
                description: data.attributes.description,
                color: data.attributes.color,
                createdAt: new Date(data.attributes.created_at),
                updatedAt: new Date(data.attributes.updated_at),
            }))
            .catch(reject);
    });
};
