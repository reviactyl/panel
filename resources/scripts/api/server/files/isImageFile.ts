import { FileObject } from '@/api/server/files/loadDirectory';

export const isImageFile = (file: FileObject): boolean => {
    if (!file.isFile) return false;

    // All common image MIME types of viewable Images
    const imageMimeTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/svg+xml',
        'image/tiff',
        'image/x-icon',
        'image/vnd.microsoft.icon',
    ];
    
    return imageMimeTypes.includes(file.mimetype.toLowerCase());
};

