import { beforeEach, afterEach, describe, expect, it, vi } from 'vitest';
import { AxiosError, AxiosHeaders } from 'axios';
import http from './http';

const progress = vi.hoisted(() => ({ startContinuous: vi.fn(), setComplete: vi.fn() }));
beforeEach(() => vi.clearAllMocks());

vi.mock('@/state', () => ({
    store: {
        getActions: () => ({ progress }),
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

describe('historical metrics background requests', () => {
    it.each([false, true])('leaves progress untouched when history rejects: %s', async (reject) => {
        const request = http.get('/api/client/servers/example/resources/history', {
            params: { days: 1 },
            adapter: async (config) => {
                if (reject) throw new AxiosError('Offline', undefined, config);
                return { config, status: 200, statusText: 'OK', headers: {}, data: { data: [] } };
            },
        });
        if (reject) await expect(request).rejects.toThrow('Offline');
        else await expect(request).resolves.toMatchObject({ data: { data: [] } });
        expect(progress.startContinuous).not.toHaveBeenCalled();
        expect(progress.setComplete).not.toHaveBeenCalled();
    });

    it('still shows progress for foreground requests', async () => {
        await http.get('/api/client/servers/example/backups', {
            adapter: async (config) => ({ config, status: 200, statusText: 'OK', headers: {}, data: {} }),
        });
        expect(progress.startContinuous).toHaveBeenCalledOnce();
        expect(progress.setComplete).toHaveBeenCalledOnce();
    });
});
