import { ServerCategory } from '@/api/account/getServerCategories';

export const compareFolders = (a: ServerCategory, b: ServerCategory): number =>
    (a.priority ?? 0) - (b.priority ?? 0) ||
    a.name.localeCompare(b.name, undefined, { sensitivity: 'base' }) ||
    (a.createdAt?.getTime() || 0) - (b.createdAt?.getTime() || 0);

export const folderPath = (folders: ServerCategory[], uuid: string): ServerCategory[] => {
    const byUuid = new Map(folders.map((folder) => [folder.uuid, folder]));
    const path: ServerCategory[] = [];
    const seen = new Set<string>();
    let folder = byUuid.get(uuid);
    while (folder && !seen.has(folder.uuid)) {
        seen.add(folder.uuid);
        path.unshift(folder);
        folder = folder.parentUuid ? byUuid.get(folder.parentUuid) : undefined;
    }
    return path;
};

export const folderTree = (folders: ServerCategory[]) => {
    const result: { folder: ServerCategory; depth: number; path: string }[] = [];
    const seen = new Set<string>();
    const children = new Map<string | null, ServerCategory[]>();
    for (const folder of [...folders].sort(compareFolders)) {
        const parent = folder.parentUuid || null;
        const siblings = children.get(parent);
        if (siblings) siblings.push(folder);
        else children.set(parent, [folder]);
    }
    const pending = (children.get(null) || []).map((folder) => ({ folder, depth: 0, names: [folder.name] })).reverse();
    while (pending.length) {
        const entry = pending.pop()!;
        if (seen.has(entry.folder.uuid)) continue;
        seen.add(entry.folder.uuid);
        result.push({ folder: entry.folder, depth: entry.depth, path: entry.names.join(' / ') });
        for (const folder of [...(children.get(entry.folder.uuid) || [])].reverse()) {
            pending.push({ folder, depth: entry.depth + 1, names: [...entry.names, folder.name] });
        }
    }
    return result;
};

export const descendantUuids = (folders: ServerCategory[], uuid: string): Set<string> => {
    const result = new Set([uuid]);
    let previousSize = 0;
    while (previousSize !== result.size) {
        previousSize = result.size;
        for (const folder of folders) {
            if (folder.parentUuid && result.has(folder.parentUuid)) result.add(folder.uuid);
        }
    }
    return result;
};
