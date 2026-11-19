import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type BrowserContext, type Page } from '@playwright/test';

const demoUrl = process.env.THEME_TOGGLE_DEMO_URL;
let demoCookies: Awaited<ReturnType<BrowserContext['cookies']>> = [];

test.beforeAll(async ({ browser }) => {
    if (!demoUrl) return;
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto(`${demoUrl}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.locator('button[type=submit]').click();
    await page.waitForURL(`${demoUrl}/`);
    demoCookies = await context.cookies();
    await context.close();
});
const publicPath = resolve('public');
const fixtures = new Map<string, string>();
const renderFixture = (path: string) => {
    if (!fixtures.has(path)) {
        fixtures.set(
            path,
            execFileSync('php', ['tests/Fixtures/auth-page.php', path, 'admin'], {
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
            }),
        );
    }
    return fixtures.get(path)!;
};

async function openAccount(page: Page) {
    if (demoUrl) {
        await page.context().addCookies(demoCookies);
    } else {
        await page.route('**/*', async (route) => {
            const url = new URL(route.request().url());
            if (url.origin !== 'http://panel.test') return route.abort();
            if (url.pathname === '/account' || url.pathname.startsWith('/locales/')) {
                return route.fulfill({
                    contentType: url.pathname === '/account' ? 'text/html' : 'application/json',
                    body: renderFixture(url.pathname + url.search),
                });
            }
            if (url.pathname.startsWith('/api/') || url.pathname === '/auth/alerts') {
                return route.fulfill({ json: { data: [], categories: [], unread: 0, last_page: 1 } });
            }
            const file = resolve(publicPath, `.${url.pathname}`);
            if (!file.startsWith(publicPath + sep)) return route.abort();
            try {
                await route.fulfill({ path: file });
            } catch {
                await route.abort();
            }
        });
    }
    await page.goto(`${demoUrl ?? 'http://panel.test'}/account`);
    await expect(page.getByRole('button', { name: 'Light', exact: true })).toBeVisible();
}

for (const width of [390, 1280]) {
    for (const colorScheme of ['light', 'dark'] as const) {
        test(`theme icons stay centered in ${colorScheme} mode at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.emulateMedia({ colorScheme });
            await openAccount(page);
            for (const name of ['Light', 'Dark', 'System']) {
                const button = page.getByRole('button', { name, exact: true });
                await button.scrollIntoViewIfNeeded();
                const offset = await button.evaluate((element) => {
                    const button = element.getBoundingClientRect();
                    const icon = element.querySelector('svg')!.getBoundingClientRect();
                    return {
                        x: Math.abs(icon.x + icon.width / 2 - button.x - button.width / 2),
                        y: Math.abs(icon.y + icon.height / 2 - button.y - button.height / 2),
                    };
                });
                expect.soft(offset.x).toBeLessThan(0.5);
                expect.soft(offset.y).toBeLessThan(0.5);
            }
        });

        test(`theme choices retain visible keyboard focus and save selections in ${colorScheme} mode at ${width}px`, async ({
            page,
        }, testInfo) => {
            await page.setViewportSize({ width, height: 900 });
            await page.emulateMedia({ colorScheme });
            await openAccount(page);
            const light = page.getByRole('button', { name: 'Light', exact: true });
            await light.focus();
            await page.keyboard.press('Shift+Tab');
            for (const [name, value, key] of [
                ['Light', 'light', 'Enter'],
                ['Dark', 'dark', 'Space'],
                ['System', 'system', 'Enter'],
            ]) {
                await page.keyboard.press('Tab');
                const button = page.getByRole('button', { name, exact: true });
                await expect(button).toBeFocused();
                await page.keyboard.press(key!);
                await expect(button).toHaveAttribute('aria-pressed', 'true');
                await expect(button).toBeFocused();
                expect(await button.evaluate((element) => element.matches(':focus-visible'))).toBe(true);
                const outline = await button.evaluate((element) => {
                    const style = getComputedStyle(element);
                    return {
                        width: parseFloat(style.outlineWidth),
                        offset: parseFloat(style.outlineOffset),
                        style: style.outlineStyle,
                    };
                });
                expect.soft(outline.style).toBe('solid');
                expect.soft(outline.width).toBeGreaterThanOrEqual(2);
                expect.soft(outline.offset + outline.width).toBeLessThanOrEqual(0);
                expect(await page.evaluate(() => localStorage.getItem('theme'))).toBe(value);
                await expect(page.locator('html')).toHaveClass(new RegExp(value === 'system' ? colorScheme : value!));
                await button.locator('..').screenshot({ path: testInfo.outputPath(`${name}-focus.png`) });
            }
            await page.reload();
            await expect(page.getByRole('button', { name: 'System', exact: true })).toHaveAttribute(
                'aria-pressed',
                'true',
            );
            await page.emulateMedia({ colorScheme: colorScheme === 'dark' ? 'light' : 'dark' });
            await expect(page.locator('html')).toHaveClass(new RegExp(colorScheme === 'dark' ? 'light' : 'dark'));
            await light.click();
            await page.reload();
            await expect(light).toHaveAttribute('aria-pressed', 'true');
            await expect(page.locator('html')).toHaveClass(/light/);
        });
    }
}
