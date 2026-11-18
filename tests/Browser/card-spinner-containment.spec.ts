import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

const fixtures = new Map<string, string>();
const renderFixture = (path: string, surface: string) => {
    const key = `${surface}:${path}`;
    if (!fixtures.has(key)) {
        fixtures.set(
            key,
            execFileSync(
                'php',
                ['tests/Fixtures/auth-page.php', path, surface === 'account' ? 'admin' : 'maintenance'],
                {
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
                },
            ),
        );
    }
    return fixtures.get(key)!;
};

for (const surface of ['account', 'maintenance']) {
    for (const theme of ['light', 'dark']) {
        for (const width of [1280, 390]) {
            test(`${surface} contains its logout overlay in ${theme} mode at ${width}px`, async ({
                page,
            }, testInfo) => {
                const demoUrl = process.env.CARD_SPINNER_DEMO_URL;
                test.skip(
                    !!demoUrl && process.env.CARD_SPINNER_DEMO_SURFACE !== surface,
                    'Run against the matching Account or Maintenance demo configuration.',
                );
                await page.setViewportSize({ width, height: 900 });
                await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
                const origin = demoUrl || 'http://panel.test';
                const path = surface === 'account' ? '/account' : '/';
                if (demoUrl) {
                    await page.goto(`${origin}/auth/login`);
                    await page.locator('input[name=username]').fill('demo');
                    await page.locator('input[name=password]').fill('demo');
                    await page.getByRole('button', { name: 'Login', exact: true }).click();
                    await page.waitForURL(`${origin}/`);
                } else {
                    const publicPath = resolve('public');
                    await page.route('**/*', async (route) => {
                        const url = new URL(route.request().url());
                        if (url.origin !== origin) return route.abort();
                        if ([path, '/auth/login', '/locales/locale.json'].includes(url.pathname)) {
                            return route.fulfill({
                                contentType: url.pathname === '/locales/locale.json' ? 'application/json' : 'text/html',
                                body: renderFixture(url.pathname + url.search, surface),
                            });
                        }
                        if (url.pathname.startsWith('/api/')) {
                            return route.fulfill({ json: { data: [], categories: [] } });
                        }
                        const asset = resolve(publicPath, `.${url.pathname}`);
                        if (!asset.startsWith(publicPath + sep)) return route.abort();
                        try {
                            await route.fulfill({ path: asset });
                        } catch {
                            await route.abort();
                        }
                    });
                }
                await page.goto(`${origin}${path}`);
                await expect(page.locator('html')).toHaveClass(new RegExp(theme));
                const logout = page.getByRole('button', { name: 'Logout', exact: true }).last();
                await expect(logout).toBeVisible();
                const card =
                    surface === 'account'
                        ? logout.locator('xpath=ancestor::div[contains(@class,"rounded-ui")][1]')
                        : page
                              .getByText('We are currently under maintenance. Kindly check back later!', {
                                  exact: true,
                              })
                              .locator('..');
                await expect(card).toBeVisible();
                const bounds = (await card.boundingBox())!;
                expect(bounds.x).toBeGreaterThanOrEqual(0);
                expect(bounds.x + bounds.width).toBeLessThanOrEqual(width);
                expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width);
                if (surface === 'account') {
                    await expect(page.getByRole('button', { name: 'Update Password', exact: true })).toBeVisible();
                    await expect(page.getByRole('button', { name: 'Update Email', exact: true })).toBeVisible();
                    await expect(page.getByRole('button', { name: 'Enable Two-Step', exact: true })).toBeVisible();
                    await expect(page.getByRole('group', { name: 'Avatar Style', exact: true })).toBeVisible();
                }
                let release!: () => void;
                const pending = new Promise<void>((resolve) => (release = resolve));
                await page.route('**/auth/logout', async (route) => {
                    await pending;
                    await route.fulfill({
                        status: 500,
                        json: { errors: [{ detail: 'Logout failed. Please retry.' }] },
                    });
                });
                try {
                    await logout.focus();
                    await page.keyboard.press(width === 390 ? 'Space' : 'Enter');
                    const overlay = card.locator('div.absolute').filter({ has: page.locator('.animate-spin') });
                    await expect(overlay).toBeVisible();
                    await expect(card).toHaveCSS('position', 'relative');
                    await expect(card).toHaveCSS('overflow', 'hidden');
                    const overlayBounds = (await overlay.boundingBox())!;
                    for (const key of ['x', 'y', 'width', 'height'] as const) {
                        expect(Math.abs(overlayBounds[key] - bounds[key])).toBeLessThanOrEqual(2);
                    }
                    const radius = await card.evaluate((element) => parseFloat(getComputedStyle(element).borderRadius));
                    expect(radius).toBeGreaterThan(0);
                    const corner = await page.evaluate(({ x, y }) => {
                        const hit = document.elementFromPoint(x + 1, y + 1);
                        return !!hit?.closest('div.absolute');
                    }, bounds);
                    expect(corner, 'Rounded card corners clip the overlay').toBe(false);
                    await page.screenshot({ path: testInfo.outputPath('loading.png'), fullPage: true });
                } finally {
                    release();
                }
                await expect(card.locator('.animate-spin')).toHaveCount(0);
                await expect(logout).toBeEnabled();
                await expect(page.getByText('Logout failed. Please retry.', { exact: true })).toBeVisible();
                await page.unroute('**/auth/logout');
                if (!demoUrl) {
                    await page.route('**/auth/logout', (route) =>
                        route.fulfill({ json: { data: { csrf_token: 'new-session' } } }),
                    );
                }
                await logout.click();
                await page.waitForURL(`${origin}/auth/login`);
                await expect(page.locator('input[name=username]')).toBeVisible();
            });
        }
    }
}
