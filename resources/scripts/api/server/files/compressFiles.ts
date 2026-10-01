import { FileObject } from '@/api/server/files/loadDirectory';
import http from '@/api/http';
import { rawDataToFileObject } from '@/api/transformers';

export default async (
    uuid: string,
    directory: string,
    files: string[],
    format?: 'tar.gz' | 'zip',
): Promise<FileObject> => {
    const { data } = await http.post(
        `/api/client/servers/${uuid}/files/compress`,
        { root: directory, files, format },
        {
            timeout: 60000,
            timeoutErrorMessage:
                'It looks like this archive is taking a long time to generate. It will appear once completed.',
        },
    );

    return rawDataToFileObject(data);
};
