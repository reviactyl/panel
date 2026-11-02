import http from '@/api/http';
import { ServerCategory } from '@/api/server/types';

export default (data: {
    name: string;
    description?: string;
    color?: string;
    parentUuid?: string | null;
    priority?: number;
}): Promise<ServerCategory> => {
    const { parentUuid, ...attributes } = data;
    return new Promise((resolve, reject) => {
        http.post('/api/client/account/categories', {
            ...attributes,
            ...(parentUuid !== undefined ? { parent_uuid: parentUuid } : {}),
        })
            .then(({ data }) =>
                resolve({
                    uuid: data.attributes.uuid,
                    name: data.attributes.name,
                    parentUuid: data.attributes.parent_uuid ?? null,
                    priority: data.attributes.priority ?? 0,
                    description: data.attributes.description,
                    color: data.attributes.color,
                    createdAt: new Date(data.attributes.created_at),
                    updatedAt: new Date(data.attributes.updated_at),
                }),
            )
            .catch(reject);
    });
};
