import { execFileSync } from 'node:child_process';
import { expect, test } from '@playwright/test';

for (const status of [403, 404, 500]) {
    test(`${status} preview keeps page edits while switching live light and dark palettes`, async ({ page }) => {
        const html = execFileSync('php', ['tests/Fixtures/error-page.php', String(status)], {
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
        });
        await page.addInitScript(() => localStorage.setItem('theme', 'light'));
        await page.route('**/*', (route) => {
            const url = new URL(route.request().url());
            if (url.origin !== 'http://panel.test') return route.abort();
            return route.fulfill({
                contentType: 'text/html',
                body: url.pathname === '/' ? `<iframe src="/preview/${status}"></iframe>` : html,
            });
        });
        await page.goto('http://panel.test/');
        const frame = page.frames().find((frame) => frame.url().endsWith(`/preview/${status}`))!;
        const root = frame.locator('html');
        const palette = () =>
            root.evaluate((root) => {
                const style = getComputedStyle(root);
                return ['background', 'text', 'muted'].map((key) => style.getPropertyValue(`--error-${key}`).trim());
            });
        await expect.poll(palette).toEqual(['#fafafa', '#18181b', '#3f3f46']);
        const send = async (settings: Record<string, unknown>) =>
            page.evaluate((settings) => {
                document
                    .querySelector('iframe')!
                    .contentWindow!.postMessage(
                        { type: 'reviactyl:designify-preview', settings },
                        window.location.origin,
                    );
            }, settings);
        const edits = {
            title: `Draft ${status}`,
            message: `Message ${status}`,
            button: `Return ${status}`,
            color: '#ffff00',
            image: '/custom-error.png',
        };
        await send({ errors: { [status]: edits } });
        await expect(frame.locator('[data-designify-error-title]')).toHaveText(edits.title);
        await send({
            errors: { [status]: edits },
            color950L: '#f0e6d2',
            color100L: '#172b3a',
            color300L: '#445566',
            color950: '#102030',
            color100: '#fedcba',
            color300: '#aabbcc',
        });
        for (const theme of ['light', 'dark', 'light']) {
            await frame.evaluate((theme) => {
                localStorage.setItem('theme', theme);
                window.dispatchEvent(new Event('designifychange'));
            }, theme);
            await expect(root).toHaveClass(theme === 'dark' ? 'dark' : '');
            await expect
                .poll(palette)
                .toEqual(theme === 'dark' ? ['#102030', '#fedcba', '#aabbcc'] : ['#f0e6d2', '#172b3a', '#445566']);
            await expect(frame.locator('[data-designify-error-title]')).toHaveText(edits.title);
            await expect(frame.locator('[data-designify-error-message]')).toHaveText(edits.message);
            await expect(frame.locator('[data-designify-error-button]')).toHaveText(edits.button);
            await expect(frame.locator('[data-designify-error-image]')).toHaveAttribute('src', edits.image);
            await expect(frame.locator('[data-designify-error-code]')).toHaveClass(/hidden/);
            await expect
                .poll(() => root.evaluate((root) => root.style.getPropertyValue('--designify-error-color')))
                .toBe(edits.color);
        }
        await send({ color950L: '#efe1cd' });
        await expect.poll(palette).toEqual(['#efe1cd', '#172b3a', '#445566']);
        await send({ color950L: '#f0e6d2' });
        await send({ color950L: '', color100L: null, color300L: 42 });
        await expect.poll(palette).toEqual(['#f0e6d2', '#172b3a', '#445566']);
        await frame.evaluate(() => {
            window.dispatchEvent(
                new MessageEvent('message', {
                    origin: 'https://other.test',
                    source: window.parent,
                    data: { type: 'reviactyl:designify-preview', settings: { color950L: '#ff0000' } },
                }),
            );
            window.dispatchEvent(
                new MessageEvent('message', {
                    origin: window.location.origin,
                    source: window,
                    data: { type: 'reviactyl:designify-preview', settings: { color950L: '#00ff00' } },
                }),
            );
        });
        await expect.poll(palette).toEqual(['#f0e6d2', '#172b3a', '#445566']);
        await send({ errors: { [status]: { ...edits, image: '', color: '' } } });
        await expect(frame.locator('[data-designify-error-image]')).not.toHaveAttribute('src');
        await expect(frame.locator('[data-designify-error-code]')).toHaveClass(/flex/);
        await expect(frame.locator('[data-designify-error-code]')).not.toHaveClass(/hidden/);
        await expect.poll(palette).toEqual(['#f0e6d2', '#172b3a', '#445566']);
        await frame.goto(`http://panel.test/preview/${status}`);
        await expect.poll(palette).toEqual(['#fafafa', '#18181b', '#3f3f46']);
    });
}
