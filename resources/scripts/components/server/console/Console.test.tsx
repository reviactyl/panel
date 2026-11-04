import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import Console from './Console';

const mocks = vi.hoisted(() => ({
    socket: { connected: true, instance: { send: vi.fn(), addListener: vi.fn(), removeListener: vi.fn() } },
}));

vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({
                server: { data: { id: 'ime-server', isTransferring: false } },
                socket: mocks.socket,
            }),
    },
}));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [true] }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('@xterm/xterm', () => ({
    Terminal: class {
        element = document.createElement('div');
        clear = vi.fn();
        writeln = vi.fn();
    },
}));
vi.mock('@xterm/addon-fit', () => ({ FitAddon: class {} }));
vi.mock('@xterm/addon-search', () => ({ SearchAddon: class {} }));
vi.mock('@xterm/addon-web-links', () => ({ WebLinksAddon: class {} }));
vi.mock('@xterm/addon-unicode11', () => ({ Unicode11Addon: class {} }));
vi.mock('@/plugins/XtermSearchBarAddon', () => ({ SearchBarAddon: class {} }));
vi.mock('@/plugins/XtermScrollDownHelperAddon', () => ({ ScrollDownHelperAddon: class {} }));

const historyKey = 'ime-server:command_history';
let container: HTMLDivElement;
let root: Root;
let input: HTMLInputElement;

beforeEach(async () => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    localStorage.setItem(historyKey, JSON.stringify(['say previous']));
    container = document.createElement('div');
    root = createRoot(container);
    await act(async () => root.render(<Console />));
    input = container.querySelector('input')!;
    mocks.socket.instance.send.mockClear();
});

afterEach(async () => {
    await act(async () => root.unmount());
    localStorage.removeItem(historyKey);
});

const keyDown = async (key: string, options: KeyboardEventInit = {}) => {
    await act(async () => {
        input.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true, ...options }));
    });
};

const history = () => JSON.parse(localStorage.getItem(historyKey)!);

describe('console command composition', () => {
    it.each([
        { isComposing: true, keyCode: 13 },
        { isComposing: true, keyCode: 229 },
        { isComposing: false, keyCode: 229 },
    ])('preserves composing input for %o, then submits completed text once', async (options) => {
        input.value = 'say nihon';
        await keyDown('Enter', options);

        expect(mocks.socket.instance.send).not.toHaveBeenCalled();
        expect(input.value).toBe('say nihon');
        expect(history()).toEqual(['say previous']);

        input.value = 'say 日本';
        await keyDown('Enter', { isComposing: false, keyCode: 13 });

        expect(mocks.socket.instance.send).toHaveBeenCalledExactlyOnceWith('send command', 'say 日本');
        expect(input.value).toBe('');
        expect(history()).toEqual(['say 日本', 'say previous']);

        await keyDown('Enter');
        expect(mocks.socket.instance.send).toHaveBeenCalledTimes(1);
        expect(history()).toEqual(['say 日本', 'say previous']);
        await keyDown('ArrowUp');
        expect(input.value).toBe('say 日本');
        await keyDown('ArrowUp');
        expect(input.value).toBe('say previous');
    });

    it('leaves IME arrow keys to composition without changing history navigation', async () => {
        input.value = 'say nihon';
        await keyDown('ArrowUp', { isComposing: true });
        await keyDown('ArrowDown', { keyCode: 229 });
        expect(input.value).toBe('say nihon');
        expect(history()).toEqual(['say previous']);
        expect(mocks.socket.instance.send).not.toHaveBeenCalled();

        await keyDown('ArrowUp');
        expect(input.value).toBe('say previous');
        await keyDown('ArrowDown');
        expect(input.value).toBe('');
    });
});
