import { act, StrictMode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { EventEmitter } from 'events';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import Console from './Console';
import { SocketEvent, SocketRequest } from '@/components/server/events';

const mocks = vi.hoisted(() => ({
    terminals: [] as any[],
    connected: true,
    instance: null as any,
}));

vi.mock('@xterm/xterm', () => ({
    Terminal: class {
        element?: HTMLElement;
        addons: any[] = [];
        unicode = { activeVersion: '' };
        options = { fontSize: 12 };
        resize = () => {};
        writeln = vi.fn();
        clear = vi.fn();
        scrollLines = vi.fn();
        attachCustomKeyEventHandler = vi.fn();
        dispose = vi.fn(() => {
            window.removeEventListener('resize', this.resize);
            this.addons.forEach((addon) => addon.dispose());
            this.element?.remove();
        });
        constructor() {
            mocks.terminals.push(this);
        }
        loadAddon(addon: any) {
            this.addons.push(addon);
        }
        open(container: HTMLElement) {
            this.element = document.createElement('div');
            container.appendChild(this.element);
            window.addEventListener('resize', this.resize);
        }
    },
}));

function addonMock() {
    return class {
        fit = vi.fn();
        dispose = vi.fn();
    };
}
vi.mock('@xterm/addon-fit', () => ({ FitAddon: addonMock() }));
vi.mock('@xterm/addon-search', () => ({ SearchAddon: addonMock() }));
vi.mock('@xterm/addon-web-links', () => ({ WebLinksAddon: addonMock() }));
vi.mock('@xterm/addon-unicode11', () => ({ Unicode11Addon: addonMock() }));
vi.mock('@/plugins/XtermSearchBarAddon', () => ({ SearchBarAddon: addonMock() }));
vi.mock('@/plugins/XtermScrollDownHelperAddon', () => ({ ScrollDownHelperAddon: addonMock() }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: any) => unknown) =>
            selector({
                server: { data: { id: 'test-server', containerText: 'Server', isTransferring: false } },
                socket: { connected: mocks.connected, instance: mocks.instance },
            }),
    },
}));
vi.mock('@/plugins/usePermissions', () => ({ usePermissions: () => [true] }));
vi.mock('@/reviactyl/elements/SpinnerOverlay', () => ({ default: () => null }));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));

let root: Root;
let container: HTMLDivElement;
let socket: EventEmitter & { send: ReturnType<typeof vi.fn> };

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.useFakeTimers();
    mocks.terminals = [];
    mocks.connected = true;
    socket = Object.assign(new EventEmitter(), { send: vi.fn() });
    mocks.instance = socket;
    localStorage.setItem('test-server:command_history', '[]');
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(async () => root.unmount());
    container.remove();
    localStorage.removeItem('test-server:command_history');
    vi.useRealTimers();
    vi.restoreAllMocks();
});

describe('console terminal lifecycle', () => {
    it('disposes the terminal and every loaded addon on unmount and cancels pending resize work', async () => {
        await act(async () => root.render(<Console />));
        const terminal = mocks.terminals[0];
        expect(terminal.addons).toHaveLength(6);
        terminal.addons[0].fit.mockClear();
        window.dispatchEvent(new Event('resize'));

        await act(async () => root.render(null));
        await act(async () => vi.runAllTimers());

        expect(terminal.dispose).toHaveBeenCalledTimes(1);
        terminal.addons.forEach((addon: any) => expect(addon.dispose).toHaveBeenCalledTimes(1));
        expect(terminal.addons[0].fit).not.toHaveBeenCalled();
        expect(socket.listenerCount(SocketEvent.CONSOLE_OUTPUT)).toBe(0);
    });

    it('opens a fresh working terminal on each return without retaining previous resize or socket listeners', async () => {
        const add = vi.spyOn(window, 'addEventListener');
        const remove = vi.spyOn(window, 'removeEventListener');
        for (let visit = 0; visit < 5; visit++) {
            await act(async () => root.render(<Console />));
            const terminal = mocks.terminals.at(-1);
            expect(terminal.element?.isConnected).toBe(true);
            expect(socket.send).toHaveBeenLastCalledWith(SocketRequest.SEND_LOGS);
            socket.emit(SocketEvent.CONSOLE_OUTPUT, `visit ${visit}\n`);
            expect(terminal.writeln).toHaveBeenLastCalledWith(`visit ${visit}\u001b[0m`);
            expect(socket.listenerCount(SocketEvent.CONSOLE_OUTPUT)).toBe(1);

            await act(async () => root.render(null));
            expect(terminal.dispose).toHaveBeenCalledTimes(1);
            expect(socket.listenerCount(SocketEvent.CONSOLE_OUTPUT)).toBe(0);
            const added = add.mock.calls.filter(([event]) => event === 'resize').length;
            const removed = remove.mock.calls.filter(([event]) => event === 'resize').length;
            expect(added - removed).toBe(0);
        }
    });

    it('keeps the same terminal across socket reconnects and disposes it after disconnecting', async () => {
        await act(async () => root.render(<Console />));
        const terminal = mocks.terminals.at(-1);
        mocks.connected = false;
        await act(async () => root.render(<Console />));
        expect(terminal.dispose).not.toHaveBeenCalled();
        mocks.connected = true;
        await act(async () => root.render(<Console />));
        expect(mocks.terminals).toHaveLength(1);
        socket.emit(SocketEvent.CONSOLE_OUTPUT, 'reconnected');
        expect(terminal.writeln).toHaveBeenLastCalledWith('reconnected\u001b[0m');
        mocks.connected = false;
        await act(async () => root.render(<Console />));
        await act(async () => root.render(null));
        expect(terminal.dispose).toHaveBeenCalledTimes(1);
    });

    it('creates a working terminal after StrictMode effect replay and disposes every created terminal', async () => {
        await act(async () =>
            root.render(
                <StrictMode>
                    <Console />
                </StrictMode>,
            ),
        );
        const live = mocks.terminals.filter((terminal) => terminal.dispose.mock.calls.length === 0);
        expect(live).toHaveLength(1);
        expect(live[0].element?.isConnected).toBe(true);
        socket.emit(SocketEvent.CONSOLE_OUTPUT, 'live after replay');
        expect(live[0].writeln).toHaveBeenLastCalledWith('live after replay\u001b[0m');
        await act(async () => root.render(null));
        mocks.terminals.forEach((terminal) => expect(terminal.dispose).toHaveBeenCalledTimes(1));
    });
});

const historyKey = 'test-server:command_history';
let input: HTMLInputElement;

const keyDown = async (key: string, options: KeyboardEventInit = {}) => {
    await act(async () => {
        input.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true, ...options }));
    });
};

const history = () => JSON.parse(localStorage.getItem(historyKey)!);

describe('console command composition', () => {
    beforeEach(async () => {
        localStorage.setItem(historyKey, JSON.stringify(['say previous']));
        await act(async () => root.render(<Console />));
        input = container.querySelector('input')!;
        socket.send.mockClear();
    });
    it.each([
        { isComposing: true, keyCode: 13 },
        { isComposing: true, keyCode: 229 },
        { isComposing: false, keyCode: 229 },
    ])('preserves composing input for %o, then submits completed text once', async (options) => {
        input.value = 'say nihon';
        await keyDown('Enter', options);

        expect(socket.send).not.toHaveBeenCalled();
        expect(input.value).toBe('say nihon');
        expect(history()).toEqual(['say previous']);

        input.value = 'say 日本';
        await keyDown('Enter', { isComposing: false, keyCode: 13 });

        expect(socket.send).toHaveBeenCalledExactlyOnceWith('send command', 'say 日本');
        expect(input.value).toBe('');
        expect(history()).toEqual(['say 日本', 'say previous']);

        await keyDown('Enter');
        expect(socket.send).toHaveBeenCalledTimes(1);
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
        expect(socket.send).not.toHaveBeenCalled();

        await keyDown('ArrowUp');
        expect(input.value).toBe('say previous');
        await keyDown('ArrowDown');
        expect(input.value).toBe('');
    });
});
