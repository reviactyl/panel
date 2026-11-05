import { act, useEffect, useState, type ReactNode, type ButtonHTMLAttributes } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import {
    createMemoryRouter,
    RouterProvider,
    Route,
    Routes,
    Link,
    useNavigate,
    type NavigateFunction,
} from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import FileEditContainer from './FileEditContainer';
import type { Props as EditorProps } from '@/reviactyl/elements/CodemirrorEditor';

const mocks = vi.hoisted(() => ({
    read: vi.fn(),
    update: vi.fn(),
    create: vi.fn(),
    list: vi.fn(),
    directory: '/',
    setDirectory: vi.fn(),
    editor: 'cm',
    shortcut: undefined as (() => void) | undefined,
    navigate: undefined as NavigateFunction | undefined,
}));
function Editor({ initialContent, fetchContent, onContentSaved, onContentChanged }: EditorProps) {
    const [value, setValue] = useState((initialContent || '').replace(/\r\n?/g, '\n'));
    useEffect(() => {
        fetchContent(() => Promise.resolve(value));
        mocks.shortcut = onContentSaved;
    }, [value, fetchContent, onContentSaved]);
    return (
        <textarea
            aria-label='Content'
            value={value}
            onInput={(event) => {
                setValue(event.currentTarget.value);
                onContentChanged?.(event.currentTarget.value);
            }}
        />
    );
}
vi.mock('@/api/server/files/getFileContents', () => ({ default: mocks.read }));
vi.mock('@/api/server/files/updateFileContents', () => ({ default: mocks.update }));
vi.mock('@/api/server/files/saveFileContents', () => ({ default: mocks.create }));
vi.mock('@/api/server/files/loadDirectory', () => ({ default: mocks.list }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ server: { data: { id: 'server', uuid: 'uuid' } }, files: { directory: mocks.directory } }),
        useStoreActions: () => mocks.setDirectory,
    },
}));
vi.mock('easy-peasy', () => ({ useStoreState: () => ({ fileEditor: mocks.editor }) }));
vi.mock('@/plugins/useFlash', () => ({ default: () => ({ addError: vi.fn(), clearFlashes: vi.fn() }) }));
vi.mock('@/api/http', () => ({ httpErrorToHuman: (error: Error) => error.message }));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('./FileManagerBreadcrumbs', () => ({
    default: () => <Link to='/server/server/files'>container</Link>,
}));
vi.mock('@/reviactyl/elements/Modal', () => ({
    default: ({ visible, children }: { visible: boolean; children: ReactNode }) =>
        visible ? <div role='dialog'>{children}</div> : null,
}));
vi.mock('react-i18next', () => ({
    useTranslation: () => ({
        t: (key: string, options?: { attribute: string }) =>
            key === 'validation:unique' ? `The ${options?.attribute || '{{attribute}}'} has already been taken.` : key,
    }),
}));
vi.mock('@/reviactyl/elements/Spinner', () => ({ default: () => <span>Loading</span> }));
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
    return (
        <>
            <Link to='/account'>Account</Link>
            <Routes>
                <Route path='/server/server/files/edit' element={<FileEditContainer />} />
                <Route path='/server/server/files/new' element={<FileEditContainer />} />
                <Route path='*' element={<Link to='/server/server/files/edit#/a.txt'>Open file</Link>} />
            </Routes>
        </>
    );
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
    mocks.update.mockReset();
    mocks.create.mockReset();
    mocks.list.mockReset().mockResolvedValue([]);
    vi.stubGlobal(
        'confirm',
        vi.fn(() => false),
    );
    mocks.shortcut = undefined;
    mocks.directory = '/';
    mocks.setDirectory.mockImplementation((directory: string) => {
        mocks.directory = directory;
    });
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
});
const render = async (path = '/server/server/files/edit#/a.txt') => {
    await act(async () => {
        root.render(
            <RouterProvider
                router={createMemoryRouter([{ path: '*', element: <Navigation /> }], {
                    initialEntries: [path],
                })}
            />,
        );
    });
};
const navigate = async (path = '/server/server/files/edit#/b.txt') => {
    await act(async () => mocks.navigate!(path));
};
const content = () => container.querySelector('textarea')!.value;
const save = () =>
    Array.from(container.querySelectorAll('button')).find((button) =>
        ['Save Content', 'Create File'].includes(button.textContent || ''),
    )!;
const edit = async (value: string) => {
    await act(async () => {
        const textarea = container.querySelector('textarea')!;
        textarea.value = value;
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    });
};
const nameFile = async (name: string) => {
    await act(async () => {
        const input = container.querySelector<HTMLInputElement>('input[name="fileName"]')!;
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, name);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await act(async () =>
        container.querySelector('form')!.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })),
    );
};
const beforeUnload = () => {
    const event = new Event('beforeunload', { cancelable: true });
    window.dispatchEvent(event);
    return event.defaultPrevented;
};

describe.each(['cm', 'mo'])('file read ordering (%s)', (editor) => {
    beforeEach(() => {
        mocks.editor = editor;
    });
    it('replaces the loading placeholder with the loaded editor without an overlay fade', async () => {
        const read = deferred();
        mocks.read.mockReturnValueOnce(read.promise);
        await render();
        expect(container.querySelector('textarea')).toBeNull();
        expect(container.textContent).toContain('Loading');
        await act(async () => read.resolve('loaded file'));
        expect(content()).toBe('loaded file');
        expect(container.textContent).not.toContain('Loading');
        expect(save().disabled).toBe(false);
    });
    it('keeps the editor mounted while saving', async () => {
        const update = deferred();
        mocks.read.mockResolvedValueOnce('loaded file');
        mocks.update.mockReturnValueOnce(update.promise);
        await render();
        const editor = container.querySelector('textarea');
        await act(async () => save().click());
        expect(container.querySelector('textarea')).toBe(editor);
        expect(save().disabled).toBe(true);
        await act(async () => update.resolve(''));
        expect(container.querySelector('textarea')).toBe(editor);
        expect(save().disabled).toBe(false);
    });
    it('keeps an empty file editable after loading', async () => {
        mocks.read.mockResolvedValueOnce('');
        await render();
        expect(content()).toBe('');
        expect(save().disabled).toBe(false);
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
            mocks.shortcut?.();
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

describe.each(['cm', 'mo'])('unsaved file navigation (%s)', (editor) => {
    beforeEach(() => {
        mocks.editor = editor;
        mocks.read.mockResolvedValue('original');
    });

    it.each(['breadcrumb', 'panel', 'another file'])(
        'retains the draft when %s navigation is canceled',
        async (target) => {
            await render();
            await edit('unsaved draft');
            if (target === 'breadcrumb') {
                await act(async () =>
                    container.querySelector<HTMLAnchorElement>('a[href="/server/server/files"]')!.click(),
                );
            } else {
                await navigate(target === 'panel' ? '/account' : '/server/server/files/edit#/b.txt');
            }
            expect(window.confirm).toHaveBeenCalledOnce();
            expect(content()).toBe('unsaved draft');
            expect(mocks.read).toHaveBeenCalledOnce();
            expect(mocks.update).not.toHaveBeenCalled();
            expect(beforeUnload()).toBe(true);
        },
    );

    it('discards only after confirmation and reloads the original on return', async () => {
        await render();
        await edit('unsaved draft');
        vi.mocked(window.confirm).mockReturnValue(true);
        await navigate('/server/server/files');
        expect(container.querySelector('textarea')).toBeNull();
        expect(beforeUnload()).toBe(false);
        await navigate('/server/server/files/edit#/a.txt');
        expect(content()).toBe('original');
        expect(mocks.update).not.toHaveBeenCalled();
    });

    it('protects a new unsaved file', async () => {
        await render('/server/server/files/new#/');
        await edit('new draft');
        await navigate('/account');
        expect(window.confirm).toHaveBeenCalledOnce();
        expect(content()).toBe('new draft');
        expect(beforeUnload()).toBe(true);
    });

    it.each(['unchanged', 'undone', 'saved'])('does not prompt for %s content', async (state) => {
        await render();
        if (state !== 'unchanged') await edit('edited');
        if (state === 'undone') await edit('original');
        if (state === 'saved') await act(async () => save().click());
        expect(beforeUnload()).toBe(false);
        await navigate('/account');
        expect(window.confirm).not.toHaveBeenCalled();
        expect(container.querySelector('textarea')).toBeNull();
    });

    it.each(['\r\n', '\r'])('does not warn after undoing edits in a file using %j line endings', async (ending) => {
        mocks.read.mockResolvedValue(`first${ending}second`);
        await render();
        await edit('changed\nsecond');
        await edit('first\nsecond');
        expect(beforeUnload()).toBe(false);
        await navigate('/account');
        expect(window.confirm).not.toHaveBeenCalled();
    });

    it('does not warn after saving an untouched CRLF file as LF', async () => {
        mocks.read.mockResolvedValue('first\r\nsecond');
        await render();
        await act(async () => save().click());
        expect(mocks.update).toHaveBeenCalledWith('uuid', '/a.txt', 'first\nsecond');
        expect(beforeUnload()).toBe(false);
        await navigate('/account');
        expect(window.confirm).not.toHaveBeenCalled();
    });

    it('retains protection after a failed save', async () => {
        vi.spyOn(console, 'error').mockImplementation(() => undefined);
        mocks.update.mockRejectedValue(new Error('save failed'));
        await render();
        await edit('unsaved draft');
        await act(async () => save().click());
        await navigate('/account');
        expect(window.confirm).toHaveBeenCalledOnce();
        expect(content()).toBe('unsaved draft');
        expect(beforeUnload()).toBe(true);
    });

    it('keeps edits made while a save is pending dirty', async () => {
        const update = deferred();
        mocks.update.mockReturnValueOnce(update.promise);
        await render();
        await edit('saved revision');
        await act(async () => save().click());
        await edit('later revision');
        await act(async () => update.resolve(''));
        await navigate('/account');
        expect(mocks.update).toHaveBeenCalledWith('uuid', '/a.txt', 'saved revision');
        expect(window.confirm).toHaveBeenCalledOnce();
        expect(content()).toBe('later revision');
    });

    it('opens a newly saved file without a discard prompt', async () => {
        await render('/server/server/files/new#/');
        await edit('created content');
        mocks.read.mockResolvedValue('created content');
        await act(async () => save().click());
        await nameFile('new.txt');
        expect(mocks.create).toHaveBeenCalledWith('uuid', '/new.txt', 'created content');
        expect(window.confirm).not.toHaveBeenCalled();
        expect(content()).toBe('created content');
        expect(beforeUnload()).toBe(false);
    });

    it('guards browser back navigation', async () => {
        await render('/account');
        await navigate('/server/server/files/edit#/a.txt');
        await edit('unsaved draft');
        await act(async () => mocks.navigate!(-1));
        expect(window.confirm).toHaveBeenCalledOnce();
        expect(content()).toBe('unsaved draft');
    });
});

describe.each(['cm', 'mo'])('new file name collisions (%s)', (editor) => {
    beforeEach(() => {
        mocks.editor = editor;
    });

    it.each(['server.properties', 'nested/../server.properties', 'nested/server.properties'])(
        'rejects the existing target %s and saves the same draft under another name',
        async (name) => {
            mocks.list.mockResolvedValue([{ name: 'server.properties', isFile: true }]);
            await render('/server/server/files/new#/');
            await edit('new draft');
            await act(async () => save().click());
            await nameFile(name);
            expect(mocks.list).toHaveBeenCalledWith('uuid', name === 'nested/server.properties' ? '/nested' : '/');
            expect(mocks.create).not.toHaveBeenCalled();
            expect(container.textContent).toContain('The file-name has already been taken.');
            expect(container.querySelector('[role="dialog"]')).not.toBeNull();
            expect(content()).toBe('new draft');
            expect(beforeUnload()).toBe(true);

            mocks.read.mockResolvedValue('new draft');
            await nameFile('different.txt');
            expect(mocks.create).toHaveBeenCalledExactlyOnceWith('uuid', '/different.txt', 'new draft');
            expect(content()).toBe('new draft');
            expect(window.confirm).not.toHaveBeenCalled();
            expect(beforeUnload()).toBe(false);
        },
    );

    it('keeps the draft and name dialog when the directory cannot be checked', async () => {
        mocks.list.mockRejectedValue(new Error('Directory unavailable'));
        await render('/server/server/files/new#/');
        await edit('new draft');
        await act(async () => save().click());
        await nameFile('new.txt');
        expect(mocks.create).not.toHaveBeenCalled();
        expect(container.textContent).toContain('Directory unavailable');
        expect(container.querySelector('[role="dialog"]')).not.toBeNull();
        expect(content()).toBe('new draft');
    });
});

describe('new file target directory', () => {
    it('checks the root after previously browsing a subdirectory', async () => {
        mocks.directory = '/old-directory';
        mocks.list.mockResolvedValue([{ name: 'server.properties' }]);
        await render('/server/server/files/new#/');
        await edit('draft');
        await act(async () => save().click());
        await nameFile('server.properties');
        expect(mocks.list).toHaveBeenCalledWith('uuid', '/');
        expect(mocks.create).not.toHaveBeenCalled();
    });

    it('checks the full target directory, including entries after the display limit', async () => {
        mocks.list.mockResolvedValue([
            ...Array.from({ length: 250 }, (_, index) => ({ name: `file-${index}` })),
            { name: 'server.properties' },
        ]);
        await render('/server/server/files/new#/configs');
        await edit('draft');
        await act(async () => save().click());
        await nameFile('server.properties');
        expect(mocks.list).toHaveBeenCalledWith('uuid', '/configs');
        expect(mocks.create).not.toHaveBeenCalled();
    });

    it('also rejects a directory occupying the requested name', async () => {
        mocks.list.mockResolvedValue([{ name: 'configs', isFile: false }]);
        await render('/server/server/files/new#/');
        await edit('draft');
        await act(async () => save().click());
        await nameFile('configs');
        expect(mocks.create).not.toHaveBeenCalled();
        expect(container.textContent).toContain('has already been taken');
    });

    it('disables creation until the directory lookup completes', async () => {
        let loaded!: (files: never[]) => void;
        mocks.list.mockReturnValue(new Promise<never[]>((resolve) => (loaded = resolve)));
        await render('/server/server/files/new#/');
        await edit('draft');
        await act(async () => save().click());
        await nameFile('new.txt');
        const dialog = container.querySelector('[role="dialog"]')!;
        expect(dialog.querySelector('button')!.disabled).toBe(true);
        expect(dialog.querySelector('input')!.disabled).toBe(true);
        expect(mocks.create).not.toHaveBeenCalled();
        mocks.read.mockResolvedValue('draft');
        await act(async () => loaded([]));
        expect(mocks.create).toHaveBeenCalledExactlyOnceWith('uuid', '/new.txt', 'draft');
    });
});
