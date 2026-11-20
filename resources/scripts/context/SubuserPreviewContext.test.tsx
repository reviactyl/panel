import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { afterEach, beforeEach, expect, it, vi } from 'vitest';
import { SubuserPreviewProvider, useSubuserPreview } from './SubuserPreviewContext';

const mocks = vi.hoisted(() => ({
    user: { uuid: 'first' } as { uuid: string } | undefined,
    token: null as string | null,
    status: vi.fn(),
    start: vi.fn(),
    heartbeat: vi.fn(),
    stop: vi.fn(),
}));
vi.mock('@/state/hooks', () => ({
    useStoreState: (selector: (state: unknown) => unknown) => selector({ user: { data: mocks.user } }),
}));
vi.mock('@/api/subuserPreview', () => ({
    getPreviewToken: () => mocks.token,
    clearPreviewToken: () => {
        mocks.token = null;
    },
    storePreviewToken: (token: string) => {
        mocks.token = token;
    },
    getSubuserPreviewStatus: mocks.status,
    startSubuserPreview: mocks.start,
    heartbeatSubuserPreview: mocks.heartbeat,
    stopSubuserPreview: mocks.stop,
}));
let root: Root;
let container: HTMLDivElement;
const fixture = { uuid: 'preview', serverName: 'Private server', subuserEmail: 'private@example.test' };
const View = () => {
    const { loading, session, start, exit } = useSubuserPreview();
    return (
        <>
            <button data-action='start' onClick={() => start('server', 'subuser')}>
                {loading ? 'loading' : (session?.subuserEmail ?? 'ordinary')}
            </button>
            <button data-action='exit' onClick={() => void exit().catch(() => undefined)} />
        </>
    );
};
const render = () =>
    root.render(
        <SubuserPreviewProvider>
            <View />
        </SubuserPreviewProvider>,
    );
beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    mocks.user = { uuid: 'first' };
    mocks.token = null;
    vi.clearAllMocks();
    mocks.start.mockResolvedValue({ token: 'preview-token', session: fixture });
    mocks.stop.mockResolvedValue(undefined);
    container = document.createElement('div');
    root = createRoot(container);
});
afterEach(async () => {
    await act(async () => root.unmount());
});
it('retains active preview in the same identity, then clears private UI across logout and same-user login', async () => {
    await act(async () => render());
    await act(async () => container.querySelector<HTMLButtonElement>('[data-action=start]')!.click());
    expect(container.textContent).toBe(fixture.subuserEmail);
    await act(async () => render());
    expect(container.textContent).toBe(fixture.subuserEmail);
    mocks.token = null;
    mocks.user = undefined;
    await act(async () => render());
    expect(container.textContent).toBe('ordinary');
    expect(document.documentElement.style.getPropertyValue('--subuser-preview-offset')).toBe('0px');
    mocks.user = { uuid: 'first' };
    await act(async () => render());
    expect(container.textContent).toBe('ordinary');
});

it('keeps the preview session available after a failed exit for a retry', async () => {
    await act(async () => render());
    await act(async () => container.querySelector<HTMLButtonElement>('[data-action=start]')!.click());
    mocks.stop.mockRejectedValueOnce(new Error('Temporary failure'));

    await act(async () => container.querySelector<HTMLButtonElement>('[data-action=exit]')!.click());
    expect(container.textContent).toContain(fixture.subuserEmail);
    expect(mocks.token).toBe('preview-token');

    await act(async () => container.querySelector<HTMLButtonElement>('[data-action=exit]')!.click());
    expect(container.textContent).toContain('ordinary');
    expect(mocks.stop).toHaveBeenCalledTimes(2);
});
it('ignores a previous identity status request completing after a new login', async () => {
    let finish!: (value: unknown) => void;
    mocks.token = 'old-preview';
    mocks.status.mockReturnValueOnce(
        new Promise((resolve) => {
            finish = resolve;
        }),
    );
    await act(async () => render());
    expect(container.textContent).toBe('loading');
    mocks.token = null;
    mocks.user = { uuid: 'second' };
    await act(async () => render());
    expect(container.textContent).toBe('ordinary');
    await act(async () => finish({ active: true, ownedByTab: true, session: fixture }));
    expect(container.textContent).toBe('ordinary');
});
it('does not clear a new identity token when the previous status request fails late', async () => {
    let reject!: (error: Error) => void;
    mocks.token = 'old-preview';
    mocks.status.mockReturnValueOnce(
        new Promise((_resolve, fail) => {
            reject = fail;
        }),
    );
    await act(async () => render());
    mocks.token = 'new-preview';
    mocks.user = { uuid: 'second' };
    mocks.status.mockResolvedValueOnce({
        active: true,
        ownedByTab: true,
        session: { ...fixture, uuid: 'new-session' },
    });
    await act(async () => render());
    await act(async () => reject(new Error('Previous session ended')));
    expect(mocks.token).toBe('new-preview');
    expect(container.textContent).toBe(fixture.subuserEmail);
});
