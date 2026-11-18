import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Locator } from '@playwright/test';

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

const color = (icon: Locator) =>
    icon.evaluate((element) => {
        const probe = document.createElement('span');
        document.body.append(probe);
        const colors = [
            'rgb(var(--text-muted, var(--color-muted-text)))',
            'rgb(var(--text-danger, var(--color-danger)))',
        ].map((value) => {
            probe.style.color = value;
            return getComputedStyle(probe).color;
        });
        probe.remove();
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d')!;
        const rgba = (value: string) => {
            context.clearRect(0, 0, 1, 1);
            context.fillStyle = value;
            context.fillRect(0, 0, 1, 1);
            return [...context.getImageData(0, 0, 1, 1).data];
        };
        const ancestors: Element[] = [];
        for (let node: Element | null = element; node; node = node.parentElement) ancestors.unshift(node);
        const background = ancestors.reduce(
            (background, node) => {
                const foreground = rgba(getComputedStyle(node).backgroundColor);
                return background.map(
                    (channel, index) =>
                        (foreground[index]! * foreground[3]!) / 255 + channel * (1 - foreground[3]! / 255),
                );
            },
            [255, 255, 255],
        );
        const luminance = (color: number[]) =>
            color
                .slice(0, 3)
                .map((channel) => channel / 255)
                .map((channel) => (channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4))
                .reduce((sum, channel, index) => sum + channel * [0.2126, 0.7152, 0.0722][index]!, 0);
        const actual = getComputedStyle(element).color;
        const values = [luminance(rgba(actual)), luminance(background)];
        return {
            actual,
            muted: colors[0],
            danger: colors[1],
            contrast: (Math.max(...values) + 0.05) / (Math.min(...values) + 0.05),
        };
    });

for (const theme of ['light', 'dark']) {
    for (const [list, label, action] of [
        ['api', 'Delete API Key', 'Delete API Key'],
        ['ssh', 'Delete SSH Key', 'Delete Key'],
        ['passkeys', 'Remove', 'Remove'],
    ]) {
        test(`${list} delete icon has muted rest and danger hover/focus in ${theme}`, async ({ page }) => {
            const demo = process.env.ACCOUNT_DELETE_DEMO_URL;
            const base = demo || 'http://panel.test';
            await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
            const mutations: string[] = [];
            if (demo) {
                await page.goto(`${base}/auth/login`);
                await page.locator('input[name=username]').fill('demo');
                await page.locator('input[name=password]').fill('demo');
                await page.getByRole('button', { name: 'Login', exact: true }).click();
                await page.waitForURL(`${base}/`);
            } else {
                const publicPath = resolve('public');
                let deleted = false;
                await page.route('**/*', async (route) => {
                    const url = new URL(route.request().url());
                    if (url.origin !== base) return route.abort();
                    if (url.pathname.startsWith('/api/client/account/')) {
                        if (route.request().method() !== 'GET') {
                            mutations.push(url.pathname);
                            deleted = true;
                            return route.fulfill({ status: 204 });
                        }
                        const attributes = {
                            identifier: 'regression',
                            description: 'Regression key',
                            name: 'Regression key',
                            fingerprint: 'regression',
                            public_key: 'ssh-ed25519 fixture',
                            allowed_ips: [],
                            created_at: '2026-01-01T00:00:00Z',
                            updated_at: '2026-01-01T00:00:00Z',
                        };
                        return route.fulfill({
                            json: {
                                data: deleted
                                    ? []
                                    : list === 'passkeys'
                                      ? [{ id: '1', ...attributes }]
                                      : [{ attributes }],
                            },
                        });
                    }
                    if (url.pathname.startsWith('/account/') || url.pathname === '/locales/locale.json') {
                        return route.fulfill({
                            contentType: url.pathname.startsWith('/account/') ? 'text/html' : 'application/json',
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
            await page.goto(`${base}/account/${list}`);
            await expect(page.locator('html')).toHaveClass(new RegExp(theme));
            const button = page.getByRole('button', { name: `${label}: Regression key`, exact: true });
            const icon = button.locator('svg');
            await expect(button).toBeVisible();
            await page.mouse.move(0, 0);
            await expect
                .poll(async () => {
                    const c = await color(icon);
                    return c.actual === c.muted;
                })
                .toBe(true);
            expect((await color(icon)).contrast).toBeGreaterThanOrEqual(3);
            await button.hover({ position: { x: 2, y: 2 } });
            await expect
                .poll(async () => {
                    const c = await color(icon);
                    return c.actual === c.danger;
                })
                .toBe(true);
            expect((await color(icon)).contrast).toBeGreaterThanOrEqual(3);
            await page.mouse.move(0, 0);
            await button.focus();
            await page.keyboard.press('Shift+Tab');
            await page.keyboard.press('Tab');
            await expect(button).toBeFocused();
            await expect(button).toHaveCSS('outline-style', 'solid');
            await expect
                .poll(async () => {
                    const c = await color(icon);
                    return c.actual === c.danger;
                })
                .toBe(true);
            expect((await color(icon)).contrast).toBeGreaterThanOrEqual(3);
            for (const key of ['Enter', 'Space']) {
                await page.keyboard.press(key);
                const dialog = page.getByRole('dialog').locator('[id^=headlessui-dialog-panel-]');
                await expect(dialog).toBeVisible();
                await expect(dialog.getByRole('button', { name: action, exact: true })).toBeEnabled();
                if (list === 'passkeys')
                    await expect(dialog.getByLabel('Account Password', { exact: true })).toBeVisible();
                expect(mutations).toHaveLength(0);
                await dialog.getByRole('button', { name: 'Cancel', exact: true }).click();
                await expect(dialog).toBeHidden();
                await expect(button).toBeFocused();
            }
            if (!demo) {
                await button.click();
                const dialog = page.getByRole('dialog').locator('[id^=headlessui-dialog-panel-]');
                if (list === 'passkeys') await dialog.getByLabel('Account Password', { exact: true }).fill('demo');
                await dialog.getByRole('button', { name: action, exact: true }).click();
                await expect(button).toHaveCount(0);
                expect(mutations).toHaveLength(1);
            }
        });
    }
}
