import { act, useEffect, type ReactNode, type ButtonHTMLAttributes } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { MemoryRouter, useNavigate } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import FileEditContainer from './FileEditContainer';
import type { Props as EditorProps } from '@/reviactyl/elements/CodemirrorEditor';

const mocks = vi.hoisted(() => ({
    read: vi.fn(),
    update: vi.fn(),
    create: vi.fn(),
    editor: 'cm',
    shortcut: undefined as (() => void) | undefined,
    navigate: undefined as ((path: string) => void) | undefined,
}));
function Editor({ initialContent, fetchContent, onContentSaved }: EditorProps) {
    useEffect(() => {
        fetchContent(() => Promise.resolve(initialContent || ''));
        mocks.shortcut = onContentSaved;
    }, [initialContent, fetchContent, onContentSaved]);
    return <textarea aria-label='Content' value={initialContent || ''} readOnly />;
}
vi.mock('@/api/server/files/getFileContents', () => ({ default: mocks.read }));
vi.mock('@/api/server/files/updateFileContents', () => ({ default: mocks.update }));
vi.mock('@/api/server/files/saveFileContents', () => ({ default: mocks.create }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { id: 'server', uuid: 'uuid' } } }),
        useStoreActions: () => vi.fn(),
    },
}));
vi.mock('easy-peasy', () => ({ useStoreState: () => ({ fileEditor: mocks.editor }) }));
vi.mock('@/plugins/useFlash', () => ({ default: () => ({ addError: vi.fn(), clearFlashes: vi.fn() }) }));
vi.mock('@/api/http', () => ({ httpErrorToHuman: (error: Error) => error.message }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('./FileManagerBreadcrumbs', () => ({ default: () => null }));
vi.mock('./FileNameModal', () => ({ default: () => null }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({
    default: ({ visible }: { visible: boolean }) => (visible ? <span>Loading</span> : null),
}));
vi.mock('@/reviactyl/elements/ScreenBlock', () => ({
    ServerError: ({ message }: { message: string }) => <span>{message}</span>,
}));
vi.mock('@/reviactyl/elements/Button', () => ({
    default: (props: ButtonHTMLAttributes<HTMLButtonElement>) => <button {...props} />,
}));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/ErrorBoundary', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/reviactyl/ui/ContentBlock', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/ui/Card', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/CodemirrorEditor', () => ({ default: Editor }));
vi.mock('@/reviactyl/elements/MonacoEditor', () => ({ default: Editor }));

function Navigation() {
    mocks.navigate = useNavigate();
    return <FileEditContainer />;
}
function deferred() {
    let resolve!: (content: string) => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<string>((res, rej) => {
        resolve = res;
        reject = rej;
    });
    return { promise, resolve, reject };
}
let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    mocks.read.mockReset();
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});
const render = async () => {
    await act(async () => {
        root.render(
            <MemoryRouter initialEntries={['/server/server/files/edit#/a.txt']}>
                <Navigation />
            </MemoryRouter>,
        );
    });
};
const navigate = async (path = '/server/server/files/edit#/b.txt') => {
    await act(async () => mocks.navigate!(path));
};
const content = () => container.querySelector('textarea')!.value;
const save = () => container.querySelector('button')!;

describe.each(['cm', 'mo'])('file read ordering (%s)', (editor) => {
    beforeEach(() => {
        mocks.editor = editor;
    });
    it('saves B contents when a delayed A response completes after B loads', async () => {
        const a = deferred();
        mocks.read.mockReturnValueOnce(a.promise).mockResolvedValueOnce('B contents');
        await render();
        await navigate();
        await act(async () => a.resolve('A contents'));
        expect(content()).toBe('B contents');
        await act(async () => save().click());
        expect(mocks.update).toHaveBeenCalledWith('uuid', '/b.txt', 'B contents');
    });
    it('keeps saves blocked while B loads, even after the old A request finishes', async () => {
        const a = deferred();
        const b = deferred();
        mocks.read.mockReturnValueOnce(a.promise).mockReturnValueOnce(b.promise);
        await render();
        await navigate();
        await act(async () => a.resolve('A contents'));
        expect(save().disabled).toBe(true);
        await act(async () => {
            save().click();
            mocks.shortcut!();
        });
        expect(mocks.update).not.toHaveBeenCalled();
        await act(async () => b.resolve('B contents'));
        expect(save().disabled).toBe(false);
        await act(async () => mocks.shortcut!());
        expect(mocks.update).toHaveBeenCalledWith('uuid', '/b.txt', 'B contents');
    });
    it('ignores errors from a file that is no longer selected', async () => {
        const a = deferred();
        mocks.read.mockReturnValueOnce(a.promise).mockResolvedValueOnce('B contents');
        await render();
        await navigate();
        await act(async () => a.reject(new Error('A failed')));
        expect(container.textContent).not.toContain('A failed');
        expect(content()).toBe('B contents');
    });
    it('starts a blank new file and ignores a delayed existing-file read', async () => {
        const a = deferred();
        mocks.read.mockReturnValueOnce(a.promise);
        await render();
        await navigate('/server/server/files/new#/');
        await act(async () => a.resolve('A contents'));
        expect(content()).toBe('');
        expect(container.textContent).not.toContain('Loading');
        expect(save().textContent).toBe('Create File');
        expect(save().disabled).toBe(false);
    });
});
