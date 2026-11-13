import { act, useEffect } from 'react';
import { createRoot, Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import MonacoEditor from '@/components/editors/MonacoEditor';

const editor = vi.hoisted(() => ({
    addCommand: vi.fn(),
    getValue: vi.fn(() => 'edited content'),
    focus: vi.fn(),
    change: undefined as ((value: string | undefined) => void) | undefined,
}));

vi.mock('@monaco-editor/react', () => ({
    default: ({
        onMount,
        onChange,
    }: {
        onMount: (instance: typeof editor, monaco: unknown) => void;
        onChange: (value: string | undefined) => void;
    }) => {
        editor.change = onChange;
        useEffect(() => {
            onMount(editor, { KeyMod: { CtrlCmd: 2048 }, KeyCode: { KeyS: 49 } });
        }, []);
        return null;
    },
}));

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
});

it('uses the latest save callback without registering another shortcut or remounting the editor', async () => {
    const originalSave = vi.fn();
    const currentSave = vi.fn();
    const fetchContent = vi.fn();
    const onModeChanged = vi.fn();
    const render = async (onContentSaved: () => void) => {
        await act(async () => {
            root.render(
                <MonacoEditor
                    mode='text/plain'
                    fetchContent={fetchContent}
                    onModeChanged={onModeChanged}
                    onContentSaved={onContentSaved}
                />,
            );
        });
    };

    await render(originalSave);
    const shortcut = editor.addCommand.mock.calls[0]![1];
    shortcut();
    expect(originalSave).toHaveBeenCalledTimes(1);
    originalSave.mockClear();

    await render(currentSave);
    shortcut();
    expect(currentSave).toHaveBeenCalledTimes(1);
    expect(originalSave).not.toHaveBeenCalled();
    expect(editor.addCommand).toHaveBeenCalledTimes(1);
    expect(editor.focus).toHaveBeenCalledTimes(1);
    await expect(fetchContent.mock.calls.at(-1)![0]()).resolves.toBe('edited content');
});

it('reports edits and an emptied document through the current change callback', async () => {
    const original = vi.fn();
    const current = vi.fn();
    const render = async (onContentChanged: (content: string) => void) => {
        await act(async () =>
            root.render(
                <MonacoEditor
                    mode='text/plain'
                    fetchContent={vi.fn()}
                    onModeChanged={vi.fn()}
                    onContentSaved={vi.fn()}
                    onContentChanged={onContentChanged}
                />,
            ),
        );
    };
    await render(original);
    editor.change!('draft');
    expect(original).toHaveBeenCalledWith('draft');
    await render(current);
    editor.change!(undefined);
    expect(current).toHaveBeenCalledWith('');
    expect(original).toHaveBeenCalledOnce();
});
