import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Locator, type Page } from '@playwright/test';

const fixtures = new Map<string, string>();
function renderFixture(path: string) {
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
}

async function serveFixture(page: Page) {
    const publicPath = resolve('public');
    await page.route('**/*', async (route) => {
        const url = new URL(route.request().url());
        if (url.origin !== 'http://panel.test') return route.abort();
        if (['/', '/account/api', '/locales/locale.json', '/locales/list.json'].includes(url.pathname)) {
            return route.fulfill({
                contentType: url.pathname.startsWith('/locales/') ? 'application/json' : 'text/html',
                body: renderFixture(url.pathname + url.search),
            });
        }
        if (url.pathname === '/api/client/account/api-keys') {
            return route.fulfill({
                json: {
                    data: [
                        { attributes: { identifier: 'scrim-key', description: 'Scrim regression', allowed_ips: [] } },
                    ],
                },
            });
        }
        if (url.pathname.startsWith('/api/') || url.pathname === '/auth/alerts') {
            return route.fulfill({
                json: {
                    data: [],
                    unread: 0,
                    meta: { pagination: { total: 0, count: 0, per_page: 50, current_page: 1, total_pages: 1 } },
                },
            });
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

async function expectDarkScrim(scrim: Locator) {
    await expect(scrim).toBeVisible();
    await expect
        .poll(() =>
            scrim.evaluate((node) => {
                const canvas = document.createElement('canvas');
                canvas.width = canvas.height = 1;
                const context = canvas.getContext('2d')!;
                context.fillStyle = getComputedStyle(node).backgroundColor;
                context.fillRect(0, 0, 1, 1);
                const [red, green, blue, alpha] = context.getImageData(0, 0, 1, 1).data;
                return { dark: Math.max(red!, green!, blue!) < 40, alpha };
            }),
        )
        .toEqual({ dark: true, alpha: 102 });
}

for (const theme of ['light', 'dark']) {
    for (const width of [1280, 390]) {
        test(`modal, confirmation and dashboard drawer stay dark and usable in ${theme} at ${width}px`, async ({
            page,
        }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
            await serveFixture(page);
            await page.goto('http://panel.test/');
            await expect(page.locator('html')).toHaveClass(new RegExp(theme));
            const manage = page.getByRole('button', { name: 'Manage', exact: true });
            await manage.focus();
            await page.keyboard.press('Enter');
            const modal = page.getByRole('dialog', { name: 'Manage Server Categories', exact: true });
            await expectDarkScrim(modal.locator('[data-wallpaper-backdrop]'));
            const name = modal.locator('input[name=name]');
            await name.fill('Category regression');
            await expect(name).toHaveValue('Category regression');
            await expect(modal).toBeVisible();
            await page.keyboard.press('Escape');
            await expect(modal).toHaveCount(0);
            await expect(manage).toBeFocused();

            await page.goto('http://panel.test/account/api');
            const remove = page.getByRole('button', { name: 'Delete API Key: Scrim regression', exact: true });
            for (const method of ['cancel', 'escape', 'background', 'close']) {
                await remove.focus();
                await page.keyboard.press('Space');
                const dialog = page.getByRole('dialog', { name: 'Delete API Key', exact: true });
                await expectDarkScrim(dialog.locator('[data-wallpaper-backdrop]'));
                await expect(dialog.getByRole('button', { name: 'Delete API Key', exact: true })).toBeEnabled();
                await page.keyboard.press('Tab');
                expect(await dialog.evaluate((node) => node.contains(document.activeElement))).toBe(true);
                if (method === 'escape') await page.keyboard.press('Escape');
                else if (method === 'background') {
                    await dialog.locator('[data-wallpaper-backdrop]').click({ position: { x: 4, y: 4 } });
                } else
                    await dialog
                        .getByRole('button', { name: method === 'cancel' ? 'Cancel' : 'Close', exact: true })
                        .click();
                await expect(dialog).toHaveCount(0);
                await expect(remove).toBeFocused();
            }
            if (width === 390) {
                const toggle = page.getByRole('button', { name: 'Show sidebar', exact: true });
                await toggle.focus();
                await page.keyboard.press('Enter');
                const scrim = page.locator('[data-wallpaper-backdrop].fixed');
                await expectDarkScrim(scrim);
                const sidebar = page.locator('div[class*="self-start"][class*="lg:fixed"]');
                await expect(sidebar).toBeVisible();
                await scrim.click({ position: { x: 380, y: 400 } });
                await expect(sidebar).toBeHidden();
                await toggle.click();
                await expectDarkScrim(scrim);
                await sidebar.getByRole('link', { name: 'Account Overview', exact: true }).click();
                await expect(scrim).toHaveCount(0);
            }
        });
    }
}

for (const theme of ['light', 'dark']) {
    test(`Pi modal and both mobile drawers preserve their workflow in ${theme}`, async ({ page }, testInfo) => {
        const url = process.env.SCRIM_DEMO_URL;
        test.skip(!url, 'Requires this task’s isolated Panel and Agent demo.');
        await page.setViewportSize({ width: 390, height: 900 });
        await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        expect(server).toBeTruthy();
        const serverPath = `/server/${server.attributes.identifier}`;
        for (const path of ['/', `${serverPath}/backups`]) {
            await page.goto(`${url}${path}`);
            const toggle = page.getByRole('button', { name: 'Show sidebar', exact: true });
            await toggle.focus();
            await page.keyboard.press('Space');
            const scrim = page.locator('[data-wallpaper-backdrop].fixed');
            await expectDarkScrim(scrim);
            const sidebar = page.locator('div[class*="self-start"][class*="lg:fixed"]');
            await expect(sidebar).toBeVisible();
            await page.screenshot({ path: testInfo.outputPath(`${path === '/' ? 'dashboard' : 'server'}-drawer.png`) });
            await scrim.click({ position: { x: 380, y: 400 } });
            await expect(sidebar).toBeHidden();
            await expect(scrim).toHaveCount(0);
            await toggle.click();
            await expectDarkScrim(scrim);
            await page.getByRole('button', { name: 'Close sidebar', exact: true }).last().click();
            await expect(scrim).toHaveCount(0);
        }
        const trigger = page.locator('button').filter({ hasText: /^Create server backup$/ });
        for (const method of ['escape', 'close', 'background']) {
            await trigger.focus();
            await page.keyboard.press('Enter');
            const modal = page.getByRole('dialog', { name: 'Create server backup', exact: true });
            await expectDarkScrim(modal.locator('[data-wallpaper-backdrop]'));
            const name = modal.locator('input[name=name]');
            await name.fill('Scrim regression');
            await expect(name).toHaveValue('Scrim regression');
            if (method === 'escape') await page.keyboard.press('Escape');
            else if (method === 'close') await modal.getByRole('button', { name: 'Close', exact: true }).click();
            else await modal.locator('[data-wallpaper-backdrop]').click({ position: { x: 4, y: 4 } });
            await expect(modal).toHaveCount(0);
            await expect(trigger).toBeFocused();
        }
        await page.goto(`${url}${serverPath}/files`);
        await page.getByText('welcome.txt', { exact: true }).first().click({ button: 'right' });
        await page.getByRole('button', { name: 'Delete', exact: true }).focus();
        await page.keyboard.press('Enter');
        const confirmation = page.getByRole('dialog');
        await expectDarkScrim(confirmation.locator('[data-wallpaper-backdrop]'));
        await expect(confirmation).toContainText('welcome.txt');
        await page.screenshot({ path: testInfo.outputPath('confirmation.png') });
        await confirmation.getByRole('button', { name: 'Cancel', exact: true }).click();
        await expect(confirmation).toHaveCount(0);
        await expect(page.getByText('welcome.txt', { exact: true }).first()).toBeVisible();
    });
}
