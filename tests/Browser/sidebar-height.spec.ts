import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Cookie, type Locator, type Page } from '@playwright/test';

const fixtures = new Map<string, string>();
let demoCookies: Cookie[] | undefined;
const renderFixture = (path: string) => {
    if (!fixtures.has(path)) {
        fixtures.set(
            path,
            execFileSync('php', ['tests/Fixtures/auth-page.php', path, 'classic'], {
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

async function openPage(page: Page) {
    const demo = process.env.SIDEBAR_HEIGHT_DEMO_URL;
    const base = demo || 'http://panel.test';
    if (demo && demoCookies) {
        await page.context().addCookies(demoCookies);
    } else if (demo) {
        await page.goto(`${base}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.locator('button[type=submit]').click();
        await page.waitForURL(`${base}/`);
        demoCookies = await page.context().cookies();
    } else {
        const publicPath = resolve('public');
        await page.route('**/*', async (route) => {
            const url = new URL(route.request().url());
            if (url.origin !== base) return route.abort();
            if (url.pathname.startsWith('/account') || url.pathname === '/locales/locale.json') {
                return route.fulfill({
                    contentType: url.pathname.startsWith('/account') ? 'text/html' : 'application/json',
                    body: renderFixture(url.pathname + url.search),
                });
            }
            if (url.pathname.startsWith('/api/')) {
                return route.fulfill({ json: { data: [], categories: [], unread: 0, last_page: 1 } });
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
    await page.goto(`${base}/account`);
    await page.waitForFunction(
        () => document.documentElement.style.getPropertyValue('--subuser-preview-offset') === '0px',
    );
    return base;
}

const drawerFor = (page: Page) =>
    page.locator('div.m-2').filter({ has: page.getByRole('button', { name: 'Logout', exact: true }) });
const toggleFor = (page: Page) => page.locator('div.lg\\:hidden button');
const bounds = (element: Locator) => element.evaluate((node) => node.getBoundingClientRect().toJSON());

for (const viewport of [
    { width: 320, height: 568 },
    { width: 375, height: 667 },
    { width: 667, height: 375 },
    { width: 1023, height: 600 },
    { width: 1024, height: 768 },
    { width: 1440, height: 900 },
]) {
    for (const offset of [0, 64]) {
        test(`Classic sidebar stays contained and scrollable at ${viewport.width}×${viewport.height}, offset ${offset}`, async ({
            page,
        }) => {
            await page.setViewportSize(viewport);
            await openPage(page);
            await page.evaluate(
                (offset) => document.documentElement.style.setProperty('--subuser-preview-offset', `${offset}px`),
                offset,
            );
            if (viewport.width < 1024) await toggleFor(page).click();
            const drawer = drawerFor(page);
            await expect(drawer).toBeVisible();
            const geometry = await bounds(drawer);
            expect(geometry.top).toBe(72 + offset);
            expect(geometry.bottom).toBeLessThanOrEqual(viewport.height - 8);
            if (viewport.width >= 1024) expect(geometry.bottom).toBe(viewport.height - 28);
            const footer = drawer.locator(':scope > div').last();
            const footerBefore = await bounds(footer);
            expect(footerBefore.bottom).toBeLessThanOrEqual(geometry.bottom);
            const navigation = drawer.locator(':scope > .flex-1');
            expect(await navigation.evaluate((node) => node.scrollHeight > node.clientHeight)).toBe(true);
            await navigation.hover();
            await page.mouse.wheel(0, 2000);
            await expect.poll(() => navigation.evaluate((node) => node.scrollTop)).toBeGreaterThan(0);
            expect((await bounds(footer)).bottom).toBe(footerBefore.bottom);
            expect(await drawer.evaluate((node) => node.scrollTop)).toBe(0);
            const lastLink = navigation.getByRole('link', { name: 'Sidebar regression 20', exact: true });
            await lastLink.focus();
            await expect(lastLink).toBeInViewport();
            await page.keyboard.press('Tab');
            const notifications = drawer.getByRole('button', { name: /^Notifications/ });
            await expect(notifications).toBeFocused();
            expect(await notifications.evaluate((node) => node.matches(':focus-visible'))).toBe(true);
            await page.keyboard.press('Enter');
            await expect(
                page.getByRole('dialog').getByRole('heading', { name: 'Notifications', exact: true }),
            ).toBeVisible();
            await page.keyboard.press('Escape');
            await expect(page.getByRole('dialog')).toHaveCount(0);
            await expect(notifications).toBeFocused();
            await notifications.click();
            await expect(
                page.getByRole('dialog').getByRole('heading', { name: 'Notifications', exact: true }),
            ).toBeVisible();
            await page.keyboard.press('Escape');
        });
    }
}

test('Classic drawer preserves keyboard/pointer navigation, toggle and backdrop dismissal after viewport changes', async ({
    page,
}) => {
    await page.setViewportSize({ width: 375, height: 667 });
    const base = await openPage(page);
    const drawer = drawerFor(page);
    const toggle = toggleFor(page);
    await expect(drawer).toBeHidden();
    await toggle.focus();
    await page.keyboard.press('Enter');
    await expect(drawer).toBeVisible();
    await page.keyboard.press('Space');
    await expect(drawer).toBeHidden();
    await toggle.click();
    await page.locator('[data-wallpaper-backdrop]').click({ position: { x: 300, y: 120 } });
    await expect(drawer).toBeHidden();
    await toggle.click();
    await page.setViewportSize({ width: 667, height: 375 });
    expect((await bounds(drawer)).bottom).toBeLessThanOrEqual(367);
    const api = drawer.locator('a[href="/account/api"]').first();
    await api.focus();
    await page.keyboard.press('Enter');
    await expect(page).toHaveURL(`${base}/account/api`);
    await expect(drawer).toBeHidden();
    await expect(page.locator('[data-wallpaper-backdrop]')).toHaveCount(0);
    await toggle.click();
    await drawer.locator('a[href="/account/"]').click();
    await expect(page).toHaveURL(`${base}/account/`);
    await expect(drawer).toBeHidden();
    await toggle.click();
    await page.goBack();
    await expect(drawer).toBeHidden();
    await page.setViewportSize({ width: 1440, height: 900 });
    await expect(drawer).toBeVisible();
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(drawer).toBeHidden();
});
