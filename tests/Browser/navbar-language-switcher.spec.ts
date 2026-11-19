import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Page } from '@playwright/test';

test.use({ locale: 'en' });

const fixtures = new Map<string, string>();
const renderFixture = (path: string) => {
    if (!fixtures.has(path)) {
        fixtures.set(
            path,
            execFileSync('php', ['tests/Fixtures/auth-page.php', path], {
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

async function prepare(page: Page) {
    const demo = process.env.NAVBAR_LANGUAGE_DEMO_URL;
    const base = demo || 'http://panel.test';
    if (!demo) {
        const publicPath = resolve('public');
        await page.route('**/*', async (route) => {
            const url = new URL(route.request().url());
            if (url.origin !== base) return route.abort();
            if (url.pathname.startsWith('/auth/') || url.pathname.startsWith('/locales/')) {
                return route.fulfill({
                    contentType: url.pathname.startsWith('/auth/') ? 'text/html' : 'application/json',
                    body: renderFixture(url.pathname + url.search),
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
    }
    return base;
}

for (const metadata of ['pending', 'missing', 'flagless']) {
    test(`visible fallback with ${metadata} language metadata`, async ({ page }, testInfo) => {
        const base = await prepare(page);
        let release!: () => void;
        const pending = new Promise<void>((done) => (release = done));
        await page.route('**/locales/list.json', async (route) => {
            if (metadata === 'pending') await pending;
            return route.fulfill({
                json: {
                    ...(metadata === 'missing'
                        ? {}
                        : { en: { name: 'English', flag: metadata === 'flagless' ? '' : 'us' } }),
                    fr: { name: 'Français', flag: 'fr' },
                },
            });
        });
        await page.goto(`${base}/auth/login`);
        const trigger = page.getByRole('button', { name: /^Panel Language:/ });
        await expect(trigger).toBeVisible();
        await expect(trigger).toHaveText('EN');
        await expect(trigger).toBeInViewport();
        await page.screenshot({ path: testInfo.outputPath(`${metadata}.png`) });
        release();
        if (metadata === 'pending') {
            await expect(trigger).toHaveAccessibleName('Panel Language: English');
            await expect(trigger.locator('.fi-us')).toBeVisible();
            await expect(trigger).toHaveText('');
        }
        await trigger.click();
        await expect(page.getByRole('button', { name: 'Français', exact: true })).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        await expect(trigger).toBeFocused();
    });
}

for (const viewport of [
    { width: 320, height: 568 },
    { width: 667, height: 375 },
    { width: 1280, height: 800 },
]) {
    for (const theme of ['light', 'dark']) {
        test(`language list scrolls and supports keyboard and locale changes at ${viewport.width}×${viewport.height} in ${theme}`, async ({
            page,
        }, testInfo) => {
            await page.setViewportSize(viewport);
            await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
            const base = await prepare(page);
            await page.goto(`${base}/auth/login`);
            const trigger = page.locator('button[aria-expanded][aria-label]');
            await expect(trigger).toHaveAccessibleName('Panel Language: English');
            await trigger.focus();
            await page.keyboard.press('Enter');
            const list = page.getByRole('group', { name: 'Panel Language', exact: true });
            await expect(list).toBeVisible();
            await expect(trigger).toHaveAttribute('aria-controls', (await list.getAttribute('id'))!);
            await expect(list.getByRole('button', { name: 'English', exact: true })).toHaveAttribute(
                'aria-pressed',
                'true',
            );
            await expect(list).toHaveCSS('overflow-y', 'auto');
            const checkBounds = async () => {
                await expect
                    .poll(async () => {
                        const bounds = await list.evaluate((node) => node.getBoundingClientRect().toJSON());
                        const viewport = page.viewportSize()!;
                        return (
                            bounds.top >= 7 &&
                            bounds.bottom <= viewport.height - 7 &&
                            bounds.left >= 7 &&
                            bounds.right <= viewport.width - 7
                        );
                    })
                    .toBe(true);
            };
            await checkBounds();
            expect(await list.evaluate((node) => node.scrollHeight > node.clientHeight)).toBe(true);
            await list.hover();
            await page.mouse.wheel(0, 1000);
            await expect.poll(() => list.evaluate((node) => node.scrollTop)).toBeGreaterThan(0);
            await page.screenshot({ path: testInfo.outputPath('scrollable.png') });
            const last = list.getByRole('button').last();
            await last.focus();
            await expect(last).toBeInViewport();
            await page.keyboard.press('Escape');
            await expect(list).toHaveCount(0);
            await expect(trigger).toBeFocused();
            await expect(trigger).toHaveAccessibleName('Panel Language: English');
            await page.keyboard.press('Space');
            await expect(list).toBeVisible();
            await page.keyboard.press('Tab');
            await expect(list.getByRole('button').first()).toBeFocused();
            expect(
                await list
                    .getByRole('button')
                    .first()
                    .evaluate((node) => node.matches(':focus-visible')),
            ).toBe(true);
            await expect(list.getByRole('button').first()).toHaveCSS('outline-style', 'solid');
            await page.setViewportSize({ width: 568, height: 320 });
            await checkBounds();
            const french = list.getByRole('button', { name: /^Français/ });
            await french.focus();
            await page.keyboard.press('Enter');
            await expect(list).toHaveCount(0);
            await expect(trigger).toBeFocused();
            await expect(trigger).toHaveAccessibleName(/Français/);
            await expect(page.locator('html')).toHaveAttribute('lang', 'fr');
            await trigger.click();
            await page.getByRole('button', { name: 'English', exact: true }).click();
            await expect(trigger).toHaveAccessibleName('Panel Language: English');
            await expect(page.locator('html')).toHaveAttribute('lang', 'en');
            await trigger.click();
            await page.getByRole('img', { name: /Reviactyl/ }).click();
            await expect(list).toHaveCount(0);
            await expect(trigger).toHaveAttribute('aria-expanded', 'false');
        });
    }
}
