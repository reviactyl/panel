import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

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

for (const theme of ['light', 'dark']) {
    test(`TOTP keeps a white quiet zone and a dialog-colored loading state in ${theme} mode`, async ({ page }) => {
        await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
        let releaseToken!: () => void;
        const tokenReady = new Promise<void>((resolve) => (releaseToken = resolve));
        const publicPath = resolve('public');
        await page.route('**/*', async (route) => {
            const url = new URL(route.request().url());
            if (url.origin !== 'http://panel.test') return route.abort();
            if (url.pathname === '/account' || url.pathname === '/locales/locale.json') {
                return route.fulfill({
                    contentType: url.pathname === '/account' ? 'text/html' : 'application/json',
                    body: renderFixture(url.pathname + url.search),
                });
            }
            if (url.pathname === '/api/client/account/two-factor') {
                await tokenReady;
                return route.fulfill({
                    json: {
                        data: {
                            image_url_data: 'otpauth://totp/Panel:test?secret=JBSWY3DPEHPK3PXP',
                            secret: 'JBSWY3DPEHPK3PXP',
                        },
                    },
                });
            }
            if (url.pathname.startsWith('/api/')) return route.fulfill({ json: { data: [], categories: [] } });
            const path = resolve(publicPath, `.${url.pathname}`);
            if (!path.startsWith(publicPath + sep)) return route.abort();
            try {
                await route.fulfill({ path });
            } catch {
                await route.abort();
            }
        });
        await page.goto('http://panel.test/account');
        await expect(page.locator('html')).toHaveClass(new RegExp(theme));
        const opener = page.getByRole('button', { name: 'Enable Two-Step', exact: true });
        await opener.click();
        const dialog = page.getByRole('dialog', { name: 'Enable Two-Step Verification', exact: true });
        const frame = dialog.locator('#enable-totp-form > div').filter({ has: page.locator('.animate-spin') });
        await expect(frame).toBeVisible();
        await expect(frame).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');
        await expect(frame).toHaveCSS('box-shadow', 'none');
        releaseToken();
        const qr = dialog.locator('#enable-totp-form svg');
        await expect(qr).toBeVisible();
        const readyFrame = qr.locator('..');
        await expect(readyFrame).toHaveCSS('background-color', 'rgb(255, 255, 255)');
        for (const side of ['top', 'right', 'bottom', 'left']) {
            await expect(readyFrame).toHaveCSS(`padding-${side}`, '8px');
        }
        await expect(readyFrame).toHaveCSS('width', '224px');
        await expect(readyFrame).toHaveCSS('height', '224px');
        await expect(qr.locator('path').first()).toHaveAttribute('fill', '#FFFFFF');
        await dialog.getByRole('button', { name: 'Cancel', exact: true }).click();
        await expect(dialog).toHaveCount(0);
        await expect(opener).toBeEnabled();
        await expect(page.getByRole('button', { name: 'Update Password', exact: true })).toBeVisible();
        await expect(page.getByRole('button', { name: 'Update Email', exact: true })).toBeVisible();
    });
}
