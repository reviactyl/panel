import { describe, expect, it } from 'vitest';
import { isRecyclePath, recycleStoragePath } from './recyclePaths';

describe('recycled file editor paths', () => {
    it('resolves display names and nested paths to the stored payload without changing child names', () => {
        expect(recycleStoragePath('/.recycle-bin/id/settings.yml')).toBe('/.recycle-bin/id/file');
        expect(recycleStoragePath('/.recycle-bin/id/world/日本語 % back\\slash.txt')).toBe(
            '/.recycle-bin/id/file/日本語 % back\\slash.txt',
        );
    });
    it('preserves normal file paths, including similarly named folders', () => {
        expect(recycleStoragePath('/world/settings.yml')).toBe('/world/settings.yml');
        expect(recycleStoragePath('/.recycle-bin-copy/file.txt')).toBe('/.recycle-bin-copy/file.txt');
        expect(isRecyclePath('/world/.recycle-bin')).toBe(false);
    });
});
