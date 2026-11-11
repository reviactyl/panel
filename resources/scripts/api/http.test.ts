import { beforeEach, afterEach, describe, expect, it, vi } from 'vitest';
import { AxiosError, AxiosHeaders } from 'axios';
import http, { resetHttpSession, withHttpSessionTransition } from './http';

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

describe('HTTP session transition', () => {
    it('cancels requests started in the previous session and gives new requests a fresh signal', async () => {
        let finish!: () => void;
        let signal: AbortSignal | undefined;
        const request = http.get('/api/client/extensions', {
            adapter: (config) => {
                signal = config.signal as AbortSignal;
                return new Promise((resolve) => {
                    finish = () => resolve({ config, data: {}, status: 200, statusText: 'OK', headers: {} });
                });
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 0));
        resetHttpSession();
        expect(signal?.aborted).toBe(true);
        finish();
        await expect(request).rejects.toMatchObject({ code: 'ERR_CANCELED' });
        await http.get('/api/client', {
            adapter: async (config) => {
                expect(config.signal?.aborted).toBe(false);
                return { config, data: {}, status: 200, statusText: 'OK', headers: {} };
            },
        });
    });
});

describe('logout request ordering', () => {
    it.each(['/auth/logout', '/auth/login', '/auth/login/checkpoint', '/auth/login/passkey'])(
        '%s drains issued requests, blocks queued old-session reads, and releases new-session reads',
        async (path) => {
            let finish!: () => void;
            let enter!: () => void;
            const entered = new Promise<void>((resolve) => {
                enter = resolve;
            });
            const issued = http.get('/api/client/account', {
                adapter: (config) =>
                    new Promise((resolve) => {
                        enter();
                        finish = () => resolve({ config, data: {}, status: 200, statusText: 'OK', headers: {} });
                    }),
            });
            await entered;
            const logout = vi.fn();
            let fresh!: Promise<unknown>;
            const changing = withHttpSessionTransition(async () => {
                await http.post(
                    path,
                    {},
                    {
                        adapter: async (config) => {
                            logout();
                            return { config, data: {}, status: 200, statusText: 'OK', headers: {} };
                        },
                    },
                );
                resetHttpSession();
                fresh = http.get('/auth/alerts', {
                    adapter: async (config) => ({ config, data: {}, status: 200, statusText: 'OK', headers: {} }),
                });
            });
            const staleAdapter = vi.fn();
            const queued = expect(http.get('/api/client/extensions', { adapter: staleAdapter })).rejects.toMatchObject({
                code: 'ERR_CANCELED',
            });
            await new Promise((resolve) => setTimeout(resolve, 0));
            expect(logout).not.toHaveBeenCalled();
            finish();
            await issued;
            await changing;
            await queued;
            await expect(fresh).resolves.toMatchObject({ status: 200 });
            expect(staleAdapter).not.toHaveBeenCalled();
            expect(logout).toHaveBeenCalledOnce();
        },
    );

    it('releases blocked requests without changing the session when logout fails', async () => {
        const before = await http.get('/api/client', {
            adapter: async (config) => ({ config, data: {}, status: 200, statusText: 'OK', headers: {} }),
        });
        let fail!: () => void;
        const changing = withHttpSessionTransition(
            () =>
                new Promise<void>((_, reject) => {
                    fail = () => reject(new Error('offline'));
                }),
        );
        const rejected = expect(changing).rejects.toThrow('offline');
        const queued = http.get('/api/client/extensions', {
            adapter: async (config) => {
                expect(config.signal).toBe(before.config.signal);
                return { config, data: {}, status: 200, statusText: 'OK', headers: {} };
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 0));
        fail();
        await rejected;
        await expect(queued).resolves.toMatchObject({ status: 200 });
    });
});
