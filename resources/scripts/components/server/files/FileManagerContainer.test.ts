// @vitest-environment node
import { readFileSync } from 'node:fs';
import ts from 'typescript';
import { describe, expect, it, vi } from 'vitest';
import type { FileObject } from '@/api/server/files/loadDirectory';

// Execute the production search effect with controlled directory responses.
const source = readFileSync(new URL('./FileManagerContainer.tsx', import.meta.url), 'utf8');
const tree = ts.createSourceFile('FileManagerContainer.tsx', source, ts.ScriptTarget.Latest, true, ts.ScriptKind.TSX);
let effect = '';
function visit(node: ts.Node) {
    if (
        ts.isCallExpression(node) &&
        node.expression.getText(tree) === 'useEffect' &&
        node.arguments[1]?.getText(tree) === '[query]'
    ) {
        effect = node.arguments[0]?.getText(tree) ?? '';
    }
    ts.forEachChild(node, visit);
}
visit(tree);
if (!effect) throw new Error('Search effect not found');
const compiled = ts.transpile(`const effect = ${effect};`, { target: ts.ScriptTarget.ES2022 });

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
        expect(search.loadDirectory).toHaveBeenCalledExactlyOnceWith('uuid', '/');
        search.run('');
        resolve([entry('child', false), entry('match.txt', true)]);
        await settle();
        expect(search.loadDirectory).toHaveBeenCalledTimes(1);
        expect(search.setRecursiveResults).toHaveBeenLastCalledWith([]);
        expect(search.setIsSearching).toHaveBeenLastCalledWith(false);
    });

    it('returns recursive matches when starting a new search after clearing', async () => {
        const search = harness();
        search.run('');
        search.loadDirectory.mockResolvedValueOnce([entry('child', false)]);
        search.loadDirectory.mockResolvedValueOnce([entry('MATCH.txt', true), entry('other.txt', true)]);
        search.run('match');
        await settle();
        expect(search.loadDirectory.mock.calls).toEqual([
            ['uuid', '/'],
            ['uuid', '/child'],
        ]);
        expect(search.setRecursiveResults).toHaveBeenLastCalledWith([
            expect.objectContaining({ name: 'MATCH.txt', fullPath: '/child/MATCH.txt' }),
        ]);
        expect(search.setIsSearching).toHaveBeenLastCalledWith(false);
    });
});
