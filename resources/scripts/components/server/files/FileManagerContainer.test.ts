// @vitest-environment node
import { readFileSync } from 'node:fs';
import ts from 'typescript';
import { describe, expect, it, vi } from 'vitest';
import type { FileObject } from '@/api/server/files/loadDirectory';
import { sortFiles, type SortDirection, type SortType } from './sortFiles';

// Execute the production search effect with controlled directory responses.
const source = readFileSync(new URL('./FileManagerContainer.tsx', import.meta.url), 'utf8');
const tree = ts.createSourceFile('FileManagerContainer.tsx', source, ts.ScriptTarget.Latest, true, ts.ScriptKind.TSX);
let effect = '';
let listing = '';
let deriveVisibleFiles = '';
function visit(node: ts.Node) {
    if (
        ts.isVariableDeclaration(node) &&
        node.name.getText(tree) === 'visibleFiles' &&
        node.initializer &&
        ts.isCallExpression(node.initializer)
    ) {
        deriveVisibleFiles = node.initializer.arguments[0]?.getText(tree) ?? '';
    }
    if (
        ts.isCallExpression(node) &&
        ts.isPropertyAccessExpression(node.expression) &&
        node.expression.name.text === 'map' &&
        node.arguments[0]?.getText(tree).includes('<FileObjectRow')
    ) {
        listing = node.expression.expression.getText(tree);
    }
    if (
        ts.isCallExpression(node) &&
        node.expression.getText(tree) === 'useEffect' &&
        node.arguments[1]?.getText(tree) === '[query, uuid]'
    ) {
        effect = node.arguments[0]?.getText(tree) ?? '';
    }
    ts.forEachChild(node, visit);
}
visit(tree);
if (!effect) throw new Error('Search effect not found');
if (!listing || !deriveVisibleFiles) throw new Error('File listing not found');
const compiled = ts.transpile(`const effect = ${effect};`, { target: ts.ScriptTarget.ES2022 });

const listFiles = (files: FileObject[], sortType: SortType, sortDirection: SortDirection): FileObject[] =>
    new Function(
        'files',
        'sortFiles',
        'sortType',
        'sortDirection',
        'query',
        `const visibleFiles = (${deriveVisibleFiles})(); return ${listing};`,
    )(files, sortFiles, sortType, sortDirection, '');

describe('large directory sorting', () => {
    const files = Array.from({ length: 251 }, (_, index) => ({
        name: `file-${index}`,
        isFile: true,
        size: index,
        modifiedAt: new Date(index * 1000),
    })) as FileObject[];

    it.each(['size', 'date'] as const)('includes the leading %s result from beyond entry 250', (sortType) => {
        const visible = listFiles(files, sortType, 'desc');

        expect(visible[0]?.name).toBe('file-250');
        expect(visible).toHaveLength(250);
        expect(files[0]?.name).toBe('file-0');
    });

    it.each(['name', 'size', 'date'] as const)('keeps directories first in both %s directions', (sortType) => {
        const directory = { ...files[0], name: 'folder', isFile: false } as FileObject;

        for (const direction of ['asc', 'desc'] as const) {
            const visible = listFiles([...files, directory], sortType, direction);

            expect(visible[0]).toBe(directory);
            expect(visible).toHaveLength(250);
        }
    });
});

function harness() {
    const searchGenRef = { current: 0 };
    const setRecursiveResults = vi.fn();
    const setIsSearching = vi.fn();
    const loadDirectory = vi.fn();
    const run = (query: string) =>
        new Function(
            'query',
            'searchGenRef',
            'loadDirectory',
            'setRecursiveResults',
            'setIsSearching',
            'uuid',
            `${compiled}\nreturn effect();`,
        )(query, searchGenRef, loadDirectory, setRecursiveResults, setIsSearching, 'uuid');
    return { run, loadDirectory, setRecursiveResults, setIsSearching };
}
const entry = (name: string, isFile: boolean) => ({ name, isFile }) as FileObject;
const settle = () => new Promise((resolve) => setTimeout(resolve, 0));

describe('recursive file search cancellation', () => {
    it('does not request descendants or restore stale results after clearing a pending search', async () => {
        const search = harness();
        let resolve!: (files: FileObject[]) => void;
        search.loadDirectory.mockReturnValueOnce(new Promise<FileObject[]>((done) => (resolve = done)));
        search.run('match');
        expect(search.loadDirectory).toHaveBeenCalledExactlyOnceWith('uuid', '/', expect.any(AbortSignal));
        search.run('');
        resolve([entry('child', false), entry('match.txt', true)]);
        await settle();
        expect(search.loadDirectory).toHaveBeenCalledTimes(1);
        expect(search.setRecursiveResults).toHaveBeenLastCalledWith([]);
        expect(search.setIsSearching).toHaveBeenLastCalledWith(false);
    });

    it('aborts pending requests on cleanup and does not publish their results', async () => {
        const search = harness();
        let resolve!: (files: FileObject[]) => void;
        search.loadDirectory.mockReturnValueOnce(new Promise<FileObject[]>((done) => (resolve = done)));
        const cleanup = search.run('match');
        const signal = search.loadDirectory.mock.calls[0]![2] as AbortSignal;
        expect(signal.aborted).toBe(false);
        cleanup();
        expect(signal.aborted).toBe(true);
        resolve([entry('child', false), entry('match.txt', true)]);
        await settle();
        expect(search.loadDirectory).toHaveBeenCalledTimes(1);
        expect(search.setRecursiveResults).toHaveBeenLastCalledWith([]);
    });

    it('returns recursive matches when starting a new search after clearing', async () => {
        const search = harness();
        search.run('');
        search.loadDirectory.mockResolvedValueOnce([entry('child', false)]);
        search.loadDirectory.mockResolvedValueOnce([entry('MATCH.txt', true), entry('other.txt', true)]);
        search.run('match');
        await settle();
        expect(search.loadDirectory.mock.calls).toEqual([
            ['uuid', '/', expect.any(AbortSignal)],
            ['uuid', '/child', expect.any(AbortSignal)],
        ]);
        expect(search.setRecursiveResults).toHaveBeenLastCalledWith([
            expect.objectContaining({ name: 'MATCH.txt', fullPath: '/child/MATCH.txt' }),
        ]);
        expect(search.setIsSearching).toHaveBeenLastCalledWith(false);
    });
});
