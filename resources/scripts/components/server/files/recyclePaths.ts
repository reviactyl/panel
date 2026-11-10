export const isRecyclePath = (path: string) => path === '/.recycle-bin' || path.startsWith('/.recycle-bin/');

export const recycleStoragePath = (path: string) => {
    const parts = path.split('/');
    if (isRecyclePath(path) && parts.length >= 4) parts[3] = 'file';
    return parts.join('/');
};
