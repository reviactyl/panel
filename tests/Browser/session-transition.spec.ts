import { expect, test } from '@playwright/test';
import { build } from 'vite';
import { resolve } from 'node:path';

test('bounded logout cancels old browser requests before a late session cookie can arrive', async ({
    page,
    context,
}) => {
    const fixture = resolve('tests/Browser/fixtures/session-transition');
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        resolve: {
            alias: [
                { find: '@/state', replacement: `${fixture}/state.ts` },
                { find: '@/lib/subuserPreviewStorage', replacement: `${fixture}/state.ts` },
                { find: '@', replacement: resolve('resources/scripts') },
            ],
        },
        build: { write: false, lib: { entry: `${fixture}/main.ts`, name: 'SessionFixture', formats: ['iife'] } },
    });
    const outputs = Array.isArray(result) ? result : [result];
    const chunk = outputs
        .flatMap((output) => ('output' in output ? output.output : []))
        .find((output) => output.type === 'chunk');
    if (!chunk || chunk.type !== 'chunk') throw new Error('Missing fixture bundle');
    await page.route('http://panel.test/', (route) =>
        route.fulfill({ contentType: 'text/html', body: '<main>Session fixture</main>' }),
    );
    await page.route('**/auth/logout', (route) =>
        route.fulfill({ json: {}, headers: { 'Set-Cookie': 'session=new; Path=/; HttpOnly' } }),
    );
    let release!: () => void;
    const held = new Promise<void>((resolve) => {
        release = resolve;
    });
    let arrived!: () => void;
    const issued = new Promise<void>((resolve) => {
        arrived = resolve;
    });
    let settled!: () => void;
    const lateResponse = new Promise<void>((resolve) => {
        settled = resolve;
    });
    await page.route('**/api/client/work', async (route) => {
        arrived();
        await held;
        try {
            await route.fulfill({ json: {}, headers: { 'Set-Cookie': 'session=old; Path=/; HttpOnly' } });
        } finally {
            settled();
        }
    });
    await page.goto('http://panel.test/');
    await context.addCookies([{ name: 'session', value: 'old', url: 'http://panel.test/', httpOnly: true }]);
    await page.addScriptTag({ content: chunk.code });
    await page.evaluate(() => (window as any).SessionFixture.startWork());
    await issued;
    const cancelled = page.waitForEvent('requestfailed', (request) => request.url().endsWith('/api/client/work'));
    await page.evaluate(() => (window as any).SessionFixture.logout());
    await cancelled;
    expect(await page.evaluate(() => (window as any).SessionFixture.workStatus)).toBe('ERR_CANCELED');
    release();
    await lateResponse;
    expect((await context.cookies()).find((cookie) => cookie.name === 'session')?.value).toBe('new');
});
