import { act, type ReactNode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import SettingsContainer from './SettingsContainer';

const mocks = vi.hoisted(() => ({
    username: 'demo',
    server: {
        id: 'serv_jrxwliv37re4fi6egcrsranpoi',
        __deprecatedUuidShort: '4c6f65a2',
        uuid: '4c6f65a2-bbfc-49c2-a3c4-30a32881af72',
        node: 'Demo Node',
        sftpDetails: { ip: '192.168.4.35', port: 2202 },
    },
    copy: vi.fn(),
}));
vi.mock('easy-peasy', () => ({
    useStoreState: (selector: (state: unknown) => unknown) =>
        selector({ user: { data: { username: mocks.username } } }),
}));
vi.mock('@/state/server', () => ({
    ServerContext: {
        useStoreState: (selector: (state: unknown) => unknown) => selector({ server: { data: mocks.server } }),
    },
}));
vi.mock('react-i18next', () => ({ useTranslation: () => ({ t: (key: string) => key }) }));
vi.mock('copy-to-clipboard', () => ({ default: mocks.copy }));
vi.mock('@/reviactyl/elements/Fade', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/Can', () => ({ default: ({ children }: { children: ReactNode }) => children }));
vi.mock('@/reviactyl/elements/ServerContentBlock', () => ({
    default: ({ children }: { children: ReactNode }) => children,
}));
vi.mock('@/components/FlashMessageRender', () => ({ default: () => null }));
vi.mock('./RenameServerBox', () => ({ default: () => null }));
vi.mock('./ReinstallServerBox', () => ({ default: () => null }));
vi.mock('./TimezoneBox', () => ({ default: () => null }));

let container: HTMLDivElement;
let portal: HTMLDivElement;
let root: Root;
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.copy.mockClear();
    container = document.createElement('div');
    portal = document.createElement('div');
    portal.id = 'modal-portal';
    document.body.appendChild(portal);
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
    portal.remove();
});

describe('SFTP credentials', () => {
    it.each(['serv_jrxwliv37re4fi6egcrsranpoi', '4c6f65a2'])(
        'uses the short UUID for display, copying and the launch link when the route identifier is %s',
        async (id) => {
            mocks.server.id = id;
            mocks.username = 'demo.user';
            await act(async () => root.render(<SettingsContainer />));

            const input = container.querySelectorAll('input')[1];
            if (!input) throw new Error('SFTP username input was not rendered.');
            expect(input.value).toBe('demo.user.4c6f65a2');
            expect(input.readOnly).toBe(true);
            await act(async () => input.click());
            expect(mocks.copy).toHaveBeenCalledWith('demo.user.4c6f65a2');
            expect(container.querySelector('a[href^="sftp:"]')?.getAttribute('href')).toBe(
                'sftp://demo.user.4c6f65a2@192.168.4.35:2202',
            );
        },
    );
});
