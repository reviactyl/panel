import { FileObject } from '@/api/server/files/loadDirectory';

export const isVideoFile = (file: FileObject): boolean => {
    if (!file.isFile) return false;

    const videoMimeTypes = [
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime',
        'video/x-matroska',
        'video/x-msvideo',
    ];

    return videoMimeTypes.includes(file.mimetype.toLowerCase());
};

export default isVideoFile;
