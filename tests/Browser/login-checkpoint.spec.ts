import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

test.use({ locale: 'en' });

const renderFixture = (path: string) =>
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
    });

for (const key of ['Enter', 'Space']) {
    test(`switches both checkpoint methods with ${key} and clears previous codes`, async ({ page }) => {
        const demoURL = process.env.CHECKPOINT_DEMO_URL;
        const baseURL = demoURL || 'http://panel.test';
        const submissions: Record<string, string>[] = [];
        if (!demoURL) {
            const html = renderFixture('/auth/login');
            const publicPath = resolve('public');
            await page.context().addCookies([{ name: 'XSRF-TOKEN', value: 'test-token', url: baseURL }]);
            await page.route('**/*', async (route) => {
                const url = new URL(route.request().url());
                if (url.origin !== baseURL) return route.abort();
                if (url.pathname === '/auth/login' && route.request().method() === 'GET') {
                    return route.fulfill({ contentType: 'text/html', body: html });
                }
                if (url.pathname === '/locales/locale.json') {
                    return route.fulfill({
                        contentType: 'application/json',
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
            await page.route('**/auth/login', async (route) => {
                if (route.request().method() !== 'POST') return route.fallback();
                await route.fulfill({ json: { data: { complete: false, confirmation_token: 'test-checkpoint' } } });
            });
        }
        await page.route('**/auth/login/checkpoint', async (route) => {
            if (route.request().method() !== 'POST') return route.fallback();
            submissions.push(route.request().postDataJSON());
            await route.fulfill({ json: { data: { complete: false } } });
        });
        await page.goto(`${baseURL}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.locator('button[type=submit]').click();
        await expect(page).toHaveURL(`${baseURL}/auth/login/checkpoint`);
        const code = page.locator('input[name=code]');
        const recoveryCode = page.locator('input[name=recoveryCode]');
        const submit = page.locator('button[type=submit]');
        await code.fill('123456');
        await page.keyboard.press('Tab');
        await expect(submit).toBeFocused();
        await page.keyboard.press('Tab');
        const lostDevice = page.getByRole('button', { name: /lost.*device/i });
        await expect(lostDevice).toBeFocused();
        await expect(lostDevice).toHaveCSS('outline-style', 'solid');
        await page.keyboard.press(key);
        await expect(recoveryCode).toBeVisible();
        await expect(recoveryCode).toHaveValue('');
        await recoveryCode.fill('recovery-one');
        await submit.click();
        await expect.poll(() => submissions.length).toBe(1);
        expect(submissions[0]).toMatchObject({ authentication_code: '', recovery_token: 'recovery-one' });
        await expect(submit).toBeEnabled();
        await recoveryCode.focus();
        await page.keyboard.press('Tab');
        await expect(submit).toBeFocused();
        await page.keyboard.press('Tab');
        const returnToAuthenticator = page.getByRole('button', { name: /have.*device/i });
        await expect(returnToAuthenticator).toBeFocused();
        await page.keyboard.press(key);
        await expect(code).toHaveValue('');
        await code.fill('654321');
        await submit.click();
        await expect.poll(() => submissions.length).toBe(2);
        expect(submissions[1]).toMatchObject({ authentication_code: '654321' });
        expect(submissions[1]).not.toHaveProperty('recovery_token');
    });
}
