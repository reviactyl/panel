import { FileObject } from '@/api/server/files/loadDirectory';

export type SortType = 'name' | 'size' | 'date';
export type SortDirection = 'asc' | 'desc';

export const sortFiles = (
    files: FileObject[],
    sortType: SortType = 'name',
    sortDirection: SortDirection = 'asc'
): FileObject[] => {
    const multiplier = sortDirection === 'asc' ? 1 : -1;
    const sorted = [...files].sort((a, b) => {
        if (a.isFile !== b.isFile) {
            return a.isFile ? 1 : -1;
        }

        if (sortType === 'name') {
            return a.name.localeCompare(b.name) * multiplier;
        }

        if (sortType === 'size') {
            return a.isFile ? (a.size - b.size) * multiplier : 0;
        }

        return (a.modifiedAt.getTime() - b.modifiedAt.getTime()) * multiplier;
    });

    return sorted.filter((file, index) => index === 0 || file.name !== sorted[index - 1]?.name);
};
