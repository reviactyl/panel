import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { EventEmitter } from 'events';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import WebsocketHandler from './WebsocketHandler';

const mocks = vi.hoisted(() => ({
    getToken: vi.fn(),
    uuid: 'server-uuid',
    socket: { connected: false, instance: null as unknown },
    setInstance: vi.fn(),
    setConnectionState: vi.fn(),
    setServerStatus: vi.fn(),
}));
const sockets: MockWebsocket[] = [];
class MockWebsocket extends EventEmitter {
    setToken = vi.fn(() => this);
    connect = vi.fn(() => this);
    close = vi.fn();
    constructor() {
        super();
        sockets.push(this);
    }
}
vi.mock('@/plugins/Websocket', () => ({
    Websocket: vi.fn(function () {
        return new MockWebsocket();
    }),
}));
vi.mock('@/plugins/PreviewWebsocket', () => ({ PreviewWebsocket: vi.fn() }));
vi.mock('@/api/server/getWebsocketToken', () => ({ default: mocks.getToken }));
vi.mock('@/context/SubuserPreviewContext', () => ({ useSubuserPreview: () => ({ session: null }) }));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) =>
            selector({ socket: mocks.socket, server: { data: { uuid: mocks.uuid } } }),
        useStoreActions: (selector: (actions: unknown) => unknown) =>
            selector({
                socket: { setInstance: mocks.setInstance, setConnectionState: mocks.setConnectionState },
                status: { setServerStatus: mocks.setServerStatus },
            }),
    },
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('framer-motion', () => ({
    motion: {
        div: ({ children, className }: { children: ReactNode; className: string }) => (
            <div className={className}>{children}</div>
        ),
    },
}));

let container: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.clearAllMocks();
    sockets.length = 0;
    mocks.uuid = 'server-uuid';
    mocks.socket.connected = false;
    mocks.socket.instance = null;
    vi.spyOn(console, 'error').mockImplementation(() => {});
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    vi.restoreAllMocks();
});

describe('initial websocket token recovery', () => {
    it.each(['server change', 'remount'])('ignores stale token failures after %s', async (navigation) => {
        let rejectOld!: (error: Error) => void;
        mocks.getToken.mockReturnValueOnce(
            new Promise((_, reject) => {
                rejectOld = reject;
            }),
        );
        await act(async () => root.render(<WebsocketHandler key='first' />));
        mocks.uuid = 'new-server';
        mocks.getToken.mockResolvedValueOnce({ token: 'new-token', socket: 'wss://new.test' });
        await act(async () => root.render(<WebsocketHandler key={navigation === 'remount' ? 'second' : 'first'} />));
        const socket = sockets[1]!;
        await act(async () => socket.emit('auth success'));
        mocks.setConnectionState.mockClear();
        await act(async () => rejectOld(new Error('Old server failed')));
        expect(mocks.setConnectionState).not.toHaveBeenCalled();
        expect(mocks.setInstance).toHaveBeenCalledExactlyOnceWith(socket);
        expect(container.textContent).toBe('');
    });

    it('does not connect a stale successful token request after changing servers', async () => {
        let resolveOld!: (value: { token: string; socket: string }) => void;
        mocks.getToken.mockReturnValueOnce(
            new Promise((resolve) => {
                resolveOld = resolve;
            }),
        );
        await act(async () => root.render(<WebsocketHandler />));
        mocks.uuid = 'new-server';
        mocks.getToken.mockResolvedValueOnce({ token: 'new-token', socket: 'wss://new.test' });
        await act(async () => root.render(<WebsocketHandler />));
        await act(async () => resolveOld({ token: 'old-token', socket: 'wss://old.test' }));
        expect(sockets[0]!.connect).not.toHaveBeenCalled();
        expect(mocks.setInstance).toHaveBeenCalledExactlyOnceWith(sockets[1]);
    });

    it('shows an error, retries without reloading, and clears feedback after authentication', async () => {
        mocks.getToken.mockRejectedValueOnce(new Error('Unavailable'));
        await act(async () => root.render(<WebsocketHandler />));
        expect(container.textContent).toContain('connection-error');
        const retry = container.querySelector('button');
        expect(retry?.textContent).toBe('retry');
        expect(mocks.setInstance).not.toHaveBeenCalled();

        let resolveToken!: (value: { token: string; socket: string }) => void;
        mocks.getToken.mockReturnValueOnce(
            new Promise((resolve) => {
                resolveToken = resolve;
            }),
        );
        await act(async () => {
            retry!.click();
            retry!.click();
        });
        expect(mocks.getToken).toHaveBeenCalledTimes(2);
        expect(mocks.getToken).toHaveBeenNthCalledWith(2, 'server-uuid');
        expect(container.textContent).toContain('connection-trouble');
        expect(container.querySelector('button')).toBeNull();

        await act(async () => resolveToken({ token: 'fresh-token', socket: 'wss://node.test' }));
        const socket = sockets[1]!;
        expect(socket.setToken).toHaveBeenCalledWith('fresh-token');
        expect(socket.connect).toHaveBeenCalledWith('wss://node.test');
        expect(mocks.setInstance).toHaveBeenCalledWith(socket);
        await act(async () => socket.emit('auth success'));
        expect(mocks.setConnectionState).toHaveBeenCalledWith(true);
        mocks.socket.connected = true;
        mocks.socket.instance = socket;
        await act(async () => root.render(<WebsocketHandler />));
        expect(container.textContent).toBe('');
        socket.emit('status', 'running');
        expect(mocks.setServerStatus).toHaveBeenCalledWith('running');
    });

    it('keeps retry available after repeated token failures', async () => {
        mocks.getToken.mockRejectedValue(new Error('Unavailable'));
        await act(async () => root.render(<WebsocketHandler />));
        await act(async () => container.querySelector('button')?.click());
        expect(mocks.getToken).toHaveBeenCalledTimes(2);
        expect(container.textContent).toContain('connection-error');
        expect(container.querySelector('button')?.textContent).toBe('retry');
    });
});
