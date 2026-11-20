import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import http from '@/api/http';
import { clearPrefetched } from '@/api/prefetch';
import watchPageData, { requestPageData } from './pageData';

const state = vi.hoisted(() => ({ user: { uuid: 'user' } as null | { uuid: string } }));
vi.mock('@/api/http', () => ({ default: { get: vi.fn() } }));
vi.mock('@/state', () => ({ store: { getState: () => ({ user: { data: state.user } }) } }));

describe('page data on navigation', () => {
    let listener: (state: { location: { pathname: string; search: string } }) => void;
    const router = { subscribe: vi.fn((callback) => ((listener = callback), () => undefined)) };
    const go = (path: string) => {
        const [pathname, search = ''] = path.split('?');
        listener({ location: { pathname: pathname!, search: search ? `?${search}` : '' } });
    };

    beforeEach(() => {
        vi.mocked(http.get)
            .mockReset()
            .mockResolvedValue({ data: { data: {} } });
        state.user = { uuid: 'user' };
        sessionStorage.clear();
        window.history.replaceState(null, '', '/account');
        watchPageData(router as never);
    });

    afterEach(() => sessionStorage.clear());

    it('asks once when entering the dashboard or another server', () => {
        go('/?page=2');
        go('/');
        go('/server/abc');
        go('/server/abc/files');
        go('/server/def/files');

        expect(vi.mocked(http.get).mock.calls.map(([, config]) => (config as any).params)).toEqual([
            { path: '/', page: 2 },
            { path: '/server/abc' },
            { path: '/server/def/files' },
        ]);
    });

    it('does nothing for other pages, signed out, or during a subuser preview', () => {
        go('/account/api');
        state.user = null;
        go('/');
        state.user = { uuid: 'user' };
        go('/account');
        sessionStorage.setItem('reviactyl:subuser-preview-token', 'token');
        go('/server/abc');

        expect(http.get).not.toHaveBeenCalled();
    });

    it('asks again after a failed request instead of reusing it', async () => {
        vi.mocked(http.get).mockRejectedValueOnce(new Error('cancelled'));
        go('/');
        await new Promise((done) => setTimeout(done, 0));
        go('/account');
        go('/');

        expect(http.get).toHaveBeenCalledTimes(2);
    });

    it("never reuses the previous account's answer after the session resets", async () => {
        clearPrefetched();
        vi.mocked(http.get).mockResolvedValueOnce({ data: { data: { old: 'account' } } });
        await expect(requestPageData('/')).resolves.toEqual({ old: 'account' });
        clearPrefetched();

        await expect(requestPageData('/')).resolves.toEqual({});
        expect(http.get).toHaveBeenCalledTimes(2);
    });
});
