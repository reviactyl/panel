import { act } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import { SWRConfig } from 'swr';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import http from '@/api/http';
import { useAlerts, type AlertPlacement } from '@/api/alerts';

const state = vi.hoisted(() => ({ user: null as null | { uuid: string } }));
vi.mock('@/api/http', () => ({ default: { get: vi.fn() } }));
vi.mock('@/state/hooks', () => ({
    useStoreState: (select: (value: { user: { data: typeof state.user } }) => unknown) =>
        select({ user: { data: state.user } }),
}));

const Probe = ({ placement }: { placement: AlertPlacement }) => {
    useAlerts(placement);
    return null;
};

let root: Root;

beforeEach(() => {
    Object.assign(globalThis, { IS_REACT_ACT_ENVIRONMENT: true });
    vi.mocked(http.get)
        .mockReset()
        .mockResolvedValue({ data: { data: [] } });
    root = createRoot(document.createElement('div'));
});

afterEach(async () => {
    await act(async () => root.unmount());
});

const render = (placement: AlertPlacement) =>
    act(async () =>
        root.render(
            <SWRConfig value={{ provider: () => new Map(), dedupingInterval: 0 }}>
                <Probe placement={placement} />
            </SWRConfig>,
        ),
    );

describe('alert loading', () => {
    it('loads sign-in alerts for guests', async () => {
        state.user = null;
        await render('auth');
        expect(http.get).toHaveBeenCalledWith('/auth/alerts');
    });

    it('does not ask the guest-only endpoint once someone is signed in', async () => {
        state.user = { uuid: 'user' };
        await render('auth');
        expect(http.get).not.toHaveBeenCalled();
    });

    it('still loads dashboard alerts for signed-in users', async () => {
        state.user = { uuid: 'user' };
        await render('dashboard');
        expect(http.get).toHaveBeenCalledWith('/api/client/alerts', { params: { placement: 'dashboard' } });
    });
});
