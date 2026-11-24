import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Page } from '@playwright/test';

const fonts = ['Poppins', 'Inter', 'Roboto', 'Cairo', 'Google+Sans', 'Playpen+Sans+Arabic', 'Noto+Sans'];

async function servePanel(page: Page, font: string) {
    const html = execFileSync('php', ['tests/Fixtures/auth-page.php', '/auth/login', '', font], {
        env: {
            ...process.env,
            APP_ENV: 'testing',
            APP_ENVIRONMENT_ONLY: 'false',
            APP_URL: 'http://panel.test',
            APP_KEY: 'SomeRandomString3232RandomString',
            CACHE_STORE: 'array',
            SESSION_DRIVER: 'array',
            REDIS_CLIENT: 'predis',
        },
        encoding: 'utf8',
        maxBuffer: 10 * 1024 * 1024,
    });
    const publicPath = resolve('public');
    const requests: string[] = [];
    await page.route('**/*', async (route) => {
        const url = new URL(route.request().url());
        if (url.origin === 'https://fonts.googleapis.com') {
            if (url.pathname === '/css2') requests.push(url.searchParams.get('family')!.split(':')[0]!);
            const family = url.searchParams.get('family')?.split(':')[0] ?? 'Rubik';
            return route.fulfill({
                contentType: 'text/css',
                body: `@font-face { font-family: '${family}'; src: url('http://panel.test/font.woff2'); font-weight: 100 900; }`,
            });
        }
        if (url.origin !== 'http://panel.test') return route.abort();
        if (url.pathname === '/locales/locale.json') {
            return route.fulfill({
                contentType: 'application/json',
                body: execFileSync('php', ['tests/Fixtures/auth-page.php', url.pathname + url.search], {
                    env: {
                        ...process.env,
                        APP_ENV: 'testing',
                        APP_ENVIRONMENT_ONLY: 'false',
                        APP_KEY: 'SomeRandomString3232RandomString',
                        CACHE_STORE: 'array',
                        REDIS_CLIENT: 'predis',
                    },
                    encoding: 'utf8',
                }),
            });
        }
        if (url.pathname === '/auth/login') return route.fulfill({ contentType: 'text/html', body: html });
        if (url.pathname === '/preview') {
            return route.fulfill({
                contentType: 'text/html',
                body: `<script>
                    window.previewReady = false;
                    window.addEventListener('message', (event) => {
                        if (event.data?.type === 'reviactyl:designify-preview-ready') window.previewReady = true;
                    });
                </script><iframe src="/auth/login" title="Panel preview"></iframe>`,
            });
        }
        if (url.pathname === '/font.woff2') {
            return route.fulfill({
                contentType: 'font/woff2',
                path: 'node_modules/@fontsource-variable/ibm-plex-sans/files/ibm-plex-sans-latin-wght-normal.woff2',
                headers: { 'Access-Control-Allow-Origin': '*' },
            });
        }
        const path = resolve(publicPath, `.${url.pathname}`);
        if (!path.startsWith(publicPath + sep)) return route.abort();
        try {
            await route.fulfill({ path });
        } catch {
            await route.abort();
        }
    });
    return requests;
}

for (const font of fonts) {
    test(`saved ${font} loads and uses its actual family`, async ({ page }) => {
        const requests = await servePanel(page, font);
        await page.goto('http://panel.test/auth/login');
        const family = font.replaceAll('+', ' ');
        await expect(page.locator('body')).toHaveCSS(
            'font-family',
            `${family.includes(' ') ? `"${family}"` : family}, sans-serif`,
        );
        expect(requests).toContain(family);
        expect(
            await page.evaluate(async (name) => (await document.fonts.load(`16px "${name}"`)).length, family),
        ).toBeGreaterThan(0);
    });
}

test('live preview loads each selected family and reuses its stylesheet across switches', async ({ page }) => {
    const requests = await servePanel(page, 'Poppins');
    await page.goto('http://panel.test/preview');
    const frame = page.frames().find((candidate) => candidate.url().endsWith('/auth/login'))!;
    await expect(frame.locator('body')).toHaveCSS('font-family', 'Poppins, sans-serif');
    // The preview bridge announces itself once its message listener is registered; earlier messages are dropped.
    await page.waitForFunction(() => (window as unknown as { previewReady: boolean }).previewReady, null, {
        timeout: 5000,
    });
    for (const font of ['Noto+Sans', 'Playpen+Sans+Arabic', 'Google+Sans', 'Inter', 'Noto Sans', null, 'Poppins']) {
        const family = font?.replaceAll('+', ' ') ?? 'Poppins';
        await page.evaluate((fontFamily) => {
            document
                .querySelector('iframe')!
                .contentWindow!.postMessage(
                    { type: 'reviactyl:designify-preview', settings: { fontFamily, radius: '8px' } },
                    window.location.origin,
                );
        }, font);
        await expect(frame.locator('body')).toHaveCSS(
            'font-family',
            `${family.includes(' ') ? `"${family}"` : family}, sans-serif`,
        );
        await expect.poll(() => requests.includes(family)).toBe(true);
        await expect
            .poll(() => frame.evaluate(async (name) => (await document.fonts.load(`16px "${name}"`)).length, family))
            .toBeGreaterThan(0);
        await expect(frame.locator('link[data-designify-font]')).toHaveCount(1);
        await expect(frame.locator('html')).toHaveCSS('--radius', '8px');
    }
});
