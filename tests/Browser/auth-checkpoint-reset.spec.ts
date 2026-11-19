import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

test.use({ locale: 'en' });
const baseURL = process.env.AUTH_CHECKPOINT_RESET_DEMO_URL || 'http://panel.test';
const fixture = (path: string) =>
    execFileSync('php', ['tests/Fixtures/auth-page.php', path], {
        env: {
            ...process.env,
            APP_ENV: 'testing',
            APP_ENVIRONMENT_ONLY: 'false',
            APP_URL: baseURL,
            APP_KEY: 'SomeRandomString3232RandomString',
            CACHE_STORE: 'array',
            SESSION_DRIVER: 'array',
            REDIS_CLIENT: 'predis',
        },
        encoding: 'utf8',
        maxBuffer: 10 * 1024 * 1024,
    });

test.beforeEach(async ({ page }) => {
    if (process.env.AUTH_CHECKPOINT_RESET_DEMO_URL) return;
    const publicPath = resolve('public');
    await page.context().addCookies([{ name: 'XSRF-TOKEN', value: 'test-token', url: baseURL }]);
    await page.route('**/*', async (route) => {
        const url = new URL(route.request().url());
        if (url.origin !== baseURL) return route.abort();
        if (url.pathname.startsWith('/auth/') || url.pathname === '/locales/locale.json') {
            return route.fulfill({
                contentType: url.pathname.startsWith('/auth/') ? 'text/html' : 'application/json',
                body: fixture(url.pathname === '/auth/login/checkpoint' ? '/auth/login' : url.pathname + url.search),
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
});

test('direct checkpoint visits redirect to usable sign-in', async ({ page }) => {
    await page.goto(`${baseURL}/auth/login/checkpoint`);
    await expect(page).toHaveURL(`${baseURL}/auth/login`);
    await expect(page.getByLabel('Username or Email')).toBeEditable();
    await expect(page.locator('button[type=submit]')).toBeEnabled();
});

test('checkpoint labels follow the selected method and focus the input', async ({ page }) => {
    await page.route('**/auth/login', async (route) => {
        if (route.request().method() !== 'POST') return route.fallback();
        await route.fulfill({ json: { data: { complete: false, confirmation_token: 'test-checkpoint' } } });
    });
    await page.goto(`${baseURL}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.locator('button[type=submit]').click();
    await expect(page).toHaveURL(`${baseURL}/auth/login/checkpoint`);
    for (const [label, name] of [
        ['Authentication Code', 'code'],
        ['Recovery Code', 'recoveryCode'],
    ] as const) {
        const input = page.getByLabel(label, { exact: true });
        await expect(input).toBeVisible();
        await expect(input).toHaveAttribute('name', name);
        await page.locator('label').filter({ hasText: label }).click();
        await expect(input).toBeFocused();
        if (name === 'code') await page.getByRole('button', { name: /lost.*device/i }).click();
    }
});

for (const query of ['', '?email=']) {
    test(`reset without email accepts and submits typed email (${query || 'no query'})`, async ({ page }) => {
        const submissions: unknown[] = [];
        await page.route('**/auth/password/reset', async (route) => {
            if (route.request().method() !== 'POST') return route.fallback();
            submissions.push(route.request().postDataJSON());
            await route.fulfill({
                status: 400,
                json: { errors: [{ detail: 'This password reset token is invalid.' }] },
            });
        });
        await page.goto(`${baseURL}/auth/password/reset/reset-token${query}`);
        const email = page.getByLabel('Email', { exact: true });
        await expect(email).toBeEditable();
        await page
            .locator('label')
            .filter({ hasText: /^Email$/ })
            .click();
        await expect(email).toBeFocused();
        await email.fill('typed@example.com');
        await page.getByLabel('New Password', { exact: true }).fill('ValidPassword123!');
        await page.getByLabel('Confirm New Password', { exact: true }).fill('ValidPassword123!');
        await page.locator('button[type=submit]').click();
        await expect.poll(() => submissions.length).toBe(1);
        expect(submissions[0]).toEqual({
            email: 'typed@example.com',
            token: 'reset-token',
            password: 'ValidPassword123!',
            password_confirmation: 'ValidPassword123!',
        });
        await expect(page.getByText('This password reset token is invalid.', { exact: true })).toBeVisible();
        await expect(email).toBeEditable();
        await expect(email).toHaveValue('typed@example.com');
    });
}

test('reset links retain a supplied email', async ({ page }) => {
    await page.goto(`${baseURL}/auth/password/reset/reset-token?email=demo%2Breset%40example.com`);
    const email = page.locator('input').first();
    await expect(email).toHaveValue('demo+reset@example.com');
    await expect(email).toBeDisabled();
});
