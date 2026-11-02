import { describe, expect, it } from 'vitest';
import { folderPath, folderTree, descendantUuids } from './folderTree';
import { ServerCategory } from '@/api/account/getServerCategories';

const folder = (uuid: string, parentUuid: string | null): ServerCategory => ({
    uuid,
    name: uuid,
    parentUuid,
    description: null,
    color: null,
    createdAt: new Date(),
    updatedAt: new Date(),
});
const folders = [folder('root', null), folder('other', null), folder('child', 'root'), folder('leaf', 'child')];
describe('nested folder navigation', () => {
    it('orders children under their parents with full destination paths', () => {
        expect(folderTree(folders).map(({ path }) => path)).toEqual([
            'other',
            'root',
            'root / child',
            'root / child / leaf',
        ]);
        expect(folderPath(folders, 'leaf').map((folder) => folder.uuid)).toEqual(['root', 'child', 'leaf']);
    });
    it('excludes the folder and all descendants from move destinations', () => {
        expect([...descendantUuids(folders, 'root')]).toEqual(['root', 'child', 'leaf']);
        expect(folderPath([folder('broken', 'broken')], 'broken')).toHaveLength(1);
    });
    it('orders sibling priorities left to right, then names and creation times', () => {
        const older = { ...folder('older', null), name: 'Same', priority: 0, createdAt: new Date('2020-01-01') };
        const newer = { ...folder('newer', null), name: 'Same', priority: 0, createdAt: new Date('2021-01-01') };
        const right = { ...folder('right', null), name: 'Alpha', priority: 10 };
        const left = { ...folder('left', null), name: 'Zulu', priority: -1 };
        expect(folderTree([right, newer, older, left]).map(({ folder }) => folder.uuid)).toEqual([
            'left',
            'older',
            'newer',
            'right',
        ]);
    });
});
