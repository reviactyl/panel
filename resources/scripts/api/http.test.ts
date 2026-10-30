import { afterEach, describe, expect, it, vi } from 'vitest';
import { AxiosError, AxiosHeaders } from 'axios';
import http from './http';

vi.mock('@/state', () => ({
    store: {
        getActions: () => ({ progress: { startContinuous: vi.fn(), setComplete: vi.fn() } }),
    },
}));

afterEach(() => vi.unstubAllGlobals());

describe('session expiry', () => {
    it.each(['/api/client/servers/example', '/api/client/servers/example/resources'])(
        'returns a stale authenticated page to login on a 401 from %s',
        async (url) => {
            const location = { pathname: '/server/example', href: '/server/example' };
            vi.stubGlobal('window', { location });
            const error = new AxiosError('Unauthenticated', undefined, undefined, undefined, {
                status: 401,
                statusText: 'Unauthorized',
                data: {},
                headers: {},
                config: { headers: new AxiosHeaders() },
            });

            await expect(http.get(url, { adapter: () => Promise.reject(error) })).rejects.toBe(error);
            expect(location.href).toBe('/auth/login');
        },
    );

    it.each([
        ['/auth/login', 401],
        ['/auth/login/checkpoint', 401],
        ['/server/example', 403],
        ['/server/example', 500],
        ['/server/example', undefined],
    ])('preserves errors on %s with status %s without navigating', async (pathname, status) => {
        const location = { pathname, href: pathname };
        vi.stubGlobal('window', { location });
        const error = { response: status ? { status } : undefined };

        await expect(http.get('/api/client', { adapter: () => Promise.reject(error) })).rejects.toBe(error);
        expect(location.href).toBe(pathname);
    });

    it('passes successful authenticated responses through', async () => {
        const location = { pathname: '/server/example', href: '/server/example' };
        vi.stubGlobal('window', { location });
        const response = await http.get('/api/client', {
            adapter: async (config) => ({ config, status: 200, statusText: 'OK', headers: {}, data: { id: 1 } }),
        });

        expect(response.data).toEqual({ id: 1 });
        expect(location.href).toBe('/server/example');
    });
});
