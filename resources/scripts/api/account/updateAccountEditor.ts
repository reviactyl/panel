import http from '@/api/http';

interface Data {
    fileEditor: string;
}

export default ({ fileEditor }: Data): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.put('/api/client/account/file-editor', { fileEditor })
            .then(() => resolve())
            .catch(reject);
    });
};
