import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

const longName = 'W'.repeat(160);
let html: string;

test.beforeAll(() => {
    html = execFileSync('php', ['tests/Fixtures/auth-page.php', '/status/example'], {
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
});

for (const width of [320, 768, 1280]) {
    test(`public status names fit and keep a circular dot at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 900 });
        await page.clock.install();
        let name = longName;
        let status = 'offline';
        const publicPath = resolve('public');
        await page.route('**/*', async (route) => {
            const url = new URL(route.request().url());
            if (url.origin !== 'http://panel.test') return route.abort();
            if (url.pathname === '/status/example') {
                return route.fulfill({ contentType: 'text/html', body: html });
            }
            if (url.pathname === '/api/public/servers/example') {
                return route.fulfill({ json: { name, description: 'Server description', status } });
            }
            if (url.pathname.startsWith('/locales/')) return route.fulfill({ json: {} });
            const path = resolve(publicPath, `.${url.pathname}`);
            if (!path.startsWith(publicPath + sep)) return route.abort();
            try {
                await route.fulfill({ path });
            } catch {
                await route.abort();
            }
        });
        await page.goto('http://panel.test/status/example');

        for (const currentName of [longName, 'Demo Playground', 'A server with several ordinary words in its name']) {
            if (name !== currentName) {
                name = currentName;
                status = 'running';
                await page.clock.fastForward(10000);
            }
            const heading = page.getByRole('heading', { level: 1, name, exact: true });
            await expect(heading).toBeVisible();
            await expect(page.getByText(status, { exact: true })).toBeVisible();
            await expect(page.getByText('Server description', { exact: true })).toBeVisible();
            const geometry = await heading.evaluate((heading) => {
                const bounds = heading.getBoundingClientRect();
                const dot = heading.firstElementChild!.getBoundingClientRect();
                const walker = document.createTreeWalker(heading, NodeFilter.SHOW_TEXT);
                const lines: DOMRect[] = [];
                while (walker.nextNode()) {
                    const range = document.createRange();
                    range.selectNodeContents(walker.currentNode);
                    lines.push(...range.getClientRects());
                }
                return {
                    dot: { width: dot.width, height: dot.height },
                    textFits: lines.every((line) => line.left >= bounds.left - 1 && line.right <= bounds.right + 1),
                    lineCount: new Set(lines.map((line) => line.top)).size,
                    overflows: heading.scrollWidth > heading.clientWidth,
                };
            });
            expect.soft(geometry.dot).toEqual({ width: 16, height: 16 });
            expect.soft(geometry.textFits).toBe(true);
            expect.soft(geometry.overflows).toBe(false);
            if (name === longName) expect.soft(geometry.lineCount).toBeGreaterThan(1);
            await expect(heading.locator(':scope > :first-child')).toHaveCSS(
                'background-color',
                status === 'offline' ? 'rgb(239, 68, 68)' : 'rgb(16, 185, 129)',
            );
        }
    });
}
