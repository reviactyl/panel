import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import http from '@/api/http';
import { takePrefetched, takePrefetchedLocale } from '@/api/prefetch';

vi.mock('@/state', () => ({
    store: { getActions: () => ({ progress: { startContinuous: vi.fn(), setComplete: vi.fn() } }) },
}));

// The inline script the server puts in every authenticated page.
const blade = readFileSync(resolve(__dirname, '../../views/templates/partials/prefetch.blade.php'), 'utf8');
const script = blade.slice(blade.indexOf('<script>') + '<script>'.length, blade.lastIndexOf('</script>'));

const SERVER = { attributes: { uuid: '1d7db3ce-d31b-4f0f-ab56-a00cdfcb0f35', status: null } };
const json = (body: unknown, status = 200) =>
    Promise.resolve({ status, text: () => Promise.resolve(JSON.stringify(body)), headers: new Headers() });

const run = async (
    url: string,
    responses: Record<string, Promise<unknown>> = {},
    embedded?: Record<string, string>,
) => {
    window.history.replaceState(null, '', url);
    document.getElementById('reviactyl-initial-responses')?.remove();
    if (embedded) {
        const element = document.createElement('script');
        element.type = 'application/json';
        element.id = 'reviactyl-initial-responses';
        element.textContent = JSON.stringify(embedded);
        document.head.append(element);
    }
    const fetch = vi.fn((input: string) => responses[input.split('?')[0]!] ?? json({ data: [] }));
    vi.stubGlobal('fetch', fetch);
    new Function(script)();
    await new Promise((done) => setTimeout(done, 0));
    await new Promise((done) => setTimeout(done, 0));
    return fetch.mock.calls.map(([input]) => input);
};

describe('page prefetch script', () => {
    beforeEach(() => {
        Object.assign(window, { PanelUser: { uuid: 'user' }, PanelConfiguration: {} });
        sessionStorage.clear();
        localStorage.clear();
    });

    afterEach(() => {
        vi.unstubAllGlobals();
        delete window.__REVIACTYL_PREFETCH__;
        delete window.__REVIACTYL_PREFETCH_SERVER__;
    });

    it('requests the server first, then what the page needs by the URL identifier, then extras', async () => {
        const requested = await run('/server/1d7db3ce/files', { '/api/client/servers/1d7db3ce': json(SERVER) });

        expect(requested).toEqual([
            '/api/client/servers/1d7db3ce?include=allocations,variables,category',
            '/api/client/servers/1d7db3ce/files/list?directory=%2F',
            '/api/client/servers/1d7db3ce/websocket',
            '/api/client/servers/1d7db3ce/files/recycle-bin',
            '/api/client/extensions',
            '/api/client/notifications?unread=0&page=1',
            `/api/client/alerts?placement=server&server=${SERVER.attributes.uuid}`,
        ]);
    });

    it("serves the app's ${SERVER.attributes.uuid} requests from the identifier requests once the ${SERVER.attributes.uuid} is confirmed", async () => {
        await run('/server/1d7db3ce/files', { '/api/client/servers/1d7db3ce': json(SERVER) });

        const uuid = SERVER.attributes.uuid;
        expect(
            takePrefetched(http.getUri({ url: `/api/client/servers/${uuid}/files/list`, params: { directory: '/' } })),
        ).toBeDefined();
        expect(takePrefetched(`/api/client/servers/${uuid}/websocket`)).toBeDefined();
        expect(takePrefetched(`/api/client/servers/${uuid}/files/recycle-bin`)).toBeDefined();
        expect(
            takePrefetched(http.getUri({ url: '/api/client/alerts', params: { placement: 'server', server: uuid } })),
        ).toBeDefined();
        // A different server never matches.
        window.__REVIACTYL_PREFETCH__!['/api/client/servers/1d7db3ce/websocket'] = {
            promise: Promise.resolve({ status: 200, body: '', headers: {} }),
        };
        expect(takePrefetched('/api/client/servers/2a8bc4fe-0000-0000-0000-000000000000/websocket')).toBeUndefined();
    });

    it('lists the directory from the URL hash and skips the recycle bin outside the root', async () => {
        const requested = await run('/server/1d7db3ce/files#/logs//old', {
            '/api/client/servers/1d7db3ce': json(SERVER),
        });

        expect(requested).toContain('/api/client/servers/1d7db3ce/files/list?directory=%2Flogs%2Fold');
        expect(requested).not.toContain('/api/client/servers/1d7db3ce/files/recycle-bin');
    });

    it.each([
        '/server/1d7db3ce',
        '/server/1d7db3ce/files/edit/server.properties',
        '/server/1d7db3ce/files#/.recycle-bin',
    ])('does not list files on %s', async (url) => {
        const requested = await run(url, { '/api/client/servers/1d7db3ce': json(SERVER) });

        expect(requested).toContain('/api/client/servers/1d7db3ce/websocket');
        expect(requested.some((input) => input.includes('/files/'))).toBe(false);
    });

    it('prefetches the dashboard and the usage of servers that can report it', async () => {
        const list = {
            data: [
                { attributes: { uuid: 'running', status: null } },
                { attributes: { uuid: 'suspended', status: 'suspended' } },
                { attributes: { uuid: 'maintenance', status: null, is_node_under_maintenance: true } },
            ],
        };
        const requested = await run('/?page=2', { '/api/client': json(list) });

        expect(requested).toEqual([
            '/api/client?include=category&page=2',
            '/api/client/account/categories',
            '/api/client/alerts?placement=dashboard',
            '/api/client/extensions',
            '/api/client/notifications?unread=0&page=1',
            '/api/client/eggs',
            '/api/client/resources?servers=running',
        ]);
    });

    it('serves responses embedded in the page without requesting them, and chains off them', async () => {
        const list = JSON.stringify({ data: [{ attributes: { uuid: 'running', status: null } }] });
        const requested = await run(
            '/',
            {},
            {
                '/api/client?include=category&page=1': list,
                '/api/client/account/categories': '{"data":[]}',
                '/api/client/alerts?placement=dashboard': '{"data":[]}',
                '/api/client/eggs': '{"data":[]}',
                '/api/client/extensions': '{"data":[]}',
                '/api/client/notifications?unread=0&page=1': '{"data":[],"meta":{}}',
            },
        );

        // Only the usage batch (from a node) still goes to the network.
        expect(requested).toEqual(['/api/client/resources?servers=running']);
        await expect(takePrefetched('/api/client/notifications?page=1&unread=0')).resolves.toEqual({
            status: 200,
            body: '{"data":[],"meta":{}}',
            headers: { 'content-type': 'application/json' },
        });
    });

    it('does not ask for a websocket token the page was sent with', async () => {
        const token = `/api/client/servers/${SERVER.attributes.uuid}/websocket`;
        const requested = await run(
            '/server/1d7db3ce',
            {},
            {
                '/api/client/servers/1d7db3ce?include=allocations,variables,category': JSON.stringify(SERVER),
                [token]: '{"data":{"token":"t","socket":"s"}}',
            },
        );

        expect(requested.some((input) => input.endsWith('/websocket'))).toBe(false);
        await expect(takePrefetched(token)).resolves.toMatchObject({ status: 200 });
    });

    it('uses an embedded server response to resolve the identifier straight away', async () => {
        const requested = await run(
            '/server/1d7db3ce',
            {},
            {
                '/api/client/servers/1d7db3ce?include=allocations,variables,category': JSON.stringify(SERVER),
            },
        );

        expect(requested).toEqual([
            '/api/client/servers/1d7db3ce/websocket',
            '/api/client/extensions',
            '/api/client/notifications?unread=0&page=1',
            `/api/client/alerts?placement=server&server=${SERVER.attributes.uuid}`,
        ]);
        expect(window.__REVIACTYL_PREFETCH_SERVER__).toEqual({ id: '1d7db3ce', uuid: SERVER.attributes.uuid });
    });

    it('hands embedded translations to the i18n request that they cover, ignoring the build hash', async () => {
        const uri = '/locales/locale.json?locale=de%2Ben&namespace=strings%2Croutes%2Cauth';
        await run('/auth/login', {}, { [uri]: '{"de":{},"en":{}}' });

        expect(takePrefetchedLocale('/locales/locale.json?locale=en&namespace=strings&hash=abc')).toBeUndefined();
        expect(
            takePrefetchedLocale('/locales/locale.json?locale=de%2Ben&namespace=strings%2Cother&hash=abc'),
        ).toBeUndefined();
        await expect(
            takePrefetchedLocale('/locales/locale.json?locale=de%2Ben&namespace=routes%2Cstrings&hash=abc'),
        ).resolves.toMatchObject({ status: 200, body: '{"de":{},"en":{}}' });
        expect(takePrefetchedLocale('/locales/locale.json?locale=de%2Ben&namespace=strings&hash=abc')).toBeUndefined();
    });

    it('does not map identifiers or ask for alerts when the server request fails', async () => {
        const requested = await run('/server/1d7db3ce', { '/api/client/servers/1d7db3ce': json({}, 404) });

        expect(requested.some((input) => input.includes('/alerts'))).toBe(false);
        expect(window.__REVIACTYL_PREFETCH_SERVER__).toBeUndefined();
        expect(takePrefetched(`/api/client/servers/${SERVER.attributes.uuid}/websocket`)).toBeUndefined();
    });

    it('does nothing during a subuser preview or on unrelated pages', async () => {
        sessionStorage.setItem('reviactyl:subuser-preview-token', 'token');
        expect(await run('/server/1d7db3ce/files')).toEqual([]);
        sessionStorage.clear();
        expect(await run('/status/1d7db3ce')).toEqual([]);
    });
});
