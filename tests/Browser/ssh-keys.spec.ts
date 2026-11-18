import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Page } from '@playwright/test';

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

async function openAccount(page: Page, theme: string) {
    await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
    const demo = process.env.SSH_KEYS_DEMO_URL;
    if (demo) {
        await page.goto(`${demo}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${demo}/`);
        return `${demo}/account/ssh`;
    }

    const publicPath = resolve('public');
    await page.route('**/*', async (route) => {
        const url = new URL(route.request().url());
        if (url.origin !== 'http://panel.test') return route.abort();
        if (url.pathname === '/account/ssh' || url.pathname.startsWith('/locales/')) {
            return route.fulfill({
                contentType: url.pathname === '/account/ssh' ? 'text/html' : 'application/json',
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
    return 'http://panel.test/account/ssh';
}

const key = (name: string, fingerprint = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG') => ({
    object: 'ssh_key',
    attributes: { name, fingerprint, public_key: 'ssh-ed25519 test', created_at: '2026-11-01T12:00:00Z' },
});

for (const theme of ['light', 'dark']) {
    for (const width of [1280, 390]) {
        test(`SSH fingerprints fit and list actions work in ${theme} at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            const url = await openAccount(page, theme);
            const first = key('Laptop');
            const second = key('Second key', 'B'.repeat(43));
            const removals: unknown[] = [];
            await page.route('**/api/client/account/ssh-keys', (route) =>
                route.fulfill({ json: { data: [first, second] } }),
            );
            await page.route('**/api/client/account/ssh-keys/remove', async (route) => {
                removals.push(route.request().postDataJSON());
                await route.fulfill({ status: 204 });
            });
            await page.goto(url);
            await expect(page.locator('html')).toHaveClass(new RegExp(theme));
            const remove = page.getByRole('button', { name: 'Delete SSH Key: Laptop', exact: true });
            await expect(remove).toBeVisible();
            const row = remove.locator('..');
            const fingerprint = row.locator('p').filter({ hasText: first.attributes.fingerprint });
            await expect(fingerprint).toHaveText(`SHA256: ${first.attributes.fingerprint}`);
            await expect(fingerprint).toHaveAttribute('title', first.attributes.fingerprint);
            const geometry = await fingerprint.evaluate((element) => {
                const row = element.parentElement!.parentElement!;
                const box = row.getBoundingClientRect();
                const text = element.getBoundingClientRect();
                const button = row.querySelector('button')!.getBoundingClientRect();
                return {
                    truncated: element.scrollWidth > element.clientWidth,
                    ellipsis: getComputedStyle(element).textOverflow,
                    withinRow: text.left >= box.left && text.right <= button.left,
                    actionWithinRow: button.left >= box.left && button.right <= box.right,
                    pageFits: document.documentElement.scrollWidth <= innerWidth,
                };
            });
            expect(geometry).toEqual({
                truncated: true,
                ellipsis: 'ellipsis',
                withinRow: true,
                actionWithinRow: true,
                pageFits: true,
            });

            await page.getByRole('button', { name: 'Save', exact: true }).focus();
            await page.keyboard.press('Tab');
            await expect(remove).toBeFocused();
            expect(await remove.evaluate((element) => element.matches(':focus-visible'))).toBe(true);
            const dialog = page.getByRole('dialog', { name: 'Delete SSH Key', exact: true });
            for (const activation of ['Enter', 'Space']) {
                await page.keyboard.press(activation);
                await expect(dialog.getByRole('heading', { name: 'Delete SSH Key', exact: true })).toBeVisible();
                await expect(dialog).toContainText('Laptop');
                expect(removals).toEqual([]);
                if (activation === 'Enter') await dialog.getByRole('button', { name: 'Cancel', exact: true }).click();
                else await page.keyboard.press('Escape');
                await expect(dialog).toHaveCount(0);
                await expect(remove).toBeFocused();
            }
            await remove.click();
            await dialog.getByRole('button', { name: 'Delete Key', exact: true }).click();
            await expect(remove).toHaveCount(0);
            expect(removals).toEqual([{ fingerprint: first.attributes.fingerprint }]);
            const remaining = page.getByRole('button', { name: 'Delete SSH Key: Second key', exact: true });
            await expect(remaining).toBeVisible();
            await remaining.click();
            await dialog.getByRole('button', { name: 'Delete Key', exact: true }).click();
            await expect(page.getByText('No SSH Keys exist for this account.', { exact: true })).toBeVisible();
            expect(removals).toEqual([
                { fingerprint: first.attributes.fingerprint },
                { fingerprint: second.attributes.fingerprint },
            ]);
        });
    }

    test(`SSH loading ends after failure and creation recovers the list in ${theme}`, async ({ page }) => {
        const url = await openAccount(page, theme);
        let release!: () => void;
        const response = new Promise<void>((resolve) => (release = resolve));
        await page.route('**/api/client/account/ssh-keys', async (route) => {
            if (route.request().method() === 'POST') {
                return route.fulfill({ json: key('Recovered key') });
            }
            await response;
            await route.fulfill({ status: 500, json: { errors: [{ detail: 'SSH keys unavailable.' }] } });
        });
        await page.goto(url);
        await expect(page.getByText('Loading...', { exact: true })).toBeVisible();
        release();
        await expect(page.getByText('SSH keys unavailable.', { exact: true })).toBeVisible();
        await expect(page.getByText('Loading...', { exact: true })).toHaveCount(0);
        await expect(page.getByText('No SSH Keys exist for this account.', { exact: true })).toHaveCount(0);
        const name = page.getByRole('textbox', { name: 'SSH Key Name', exact: true });
        await page.getByText('SSH Key Name', { exact: true }).click();
        await expect(name).toBeFocused();
        await name.fill('Recovered key');
        const publicKey = page.getByRole('textbox', { name: 'Public Key', exact: true });
        await page.getByText('Public Key', { exact: true }).click();
        await expect(publicKey).toBeFocused();
        await publicKey.fill('ssh-ed25519 test');
        await page.getByRole('button', { name: 'Save', exact: true }).click();
        await expect(page.getByRole('button', { name: 'Delete SSH Key: Recovered key', exact: true })).toBeVisible();
        await expect(page.getByText('SSH keys unavailable.', { exact: true })).toBeVisible();
        await expect(page.getByText('Loading...', { exact: true })).toHaveCount(0);
    });
}
