import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

for (const width of [1280, 390]) {
    for (const state of ['installing', 'failed']) {
        test(`Agent ${state} messages stay inside their columns at ${width}px`, async ({ page }) => {
            const html = execFileSync('php', ['tests/Fixtures/software-updates-page.php', state], {
                encoding: 'utf8',
                maxBuffer: 10 * 1024 * 1024,
                env: {
                    ...process.env,
                    APP_ENV: 'testing',
                    APP_ENVIRONMENT_ONLY: 'false',
                    APP_KEY: 'SomeRandomString3232RandomString',
                    APP_URL: 'http://panel.test',
                    CACHE_STORE: 'array',
                    SESSION_DRIVER: 'array',
                    REDIS_CLIENT: 'predis',
                },
            });
            const publicPath = resolve('public');
            await page.route('**/*', async (route) => {
                const url = new URL(route.request().url());
                if (url.origin !== 'http://panel.test') return route.abort();
                if (url.pathname === '/admin/software-updates') {
                    return route.fulfill({ contentType: 'text/html', body: html });
                }
                if (/\/livewire-[^/]+\/livewire\.js$/.test(url.pathname)) {
                    return route.fulfill({ path: 'vendor/livewire/livewire/dist/livewire.js' });
                }
                const path = resolve(publicPath, `.${url.pathname}`);
                if (!path.startsWith(publicPath + sep)) return route.abort();
                try {
                    await route.fulfill({ path });
                } catch {
                    await route.abort();
                }
            });
            await page.setViewportSize({ width, height: 900 });
            await page.goto('http://panel.test/admin/software-updates');
            const row = page.locator('.software-update-table').nth(1).locator('tbody tr');
            const button = row.getByRole('button');
            if (state === 'installing') {
                await expect(button).toHaveText('StagingAndValidatingRelease'.repeat(20));
                await expect(button).toBeDisabled();
            } else {
                await expect(button).toBeEnabled();
                await expect(button).toHaveText(/Update now/);
            }
            expect(
                await page
                    .locator('.software-update-table td')
                    .evaluateAll((cells) => cells.every((cell) => cell.scrollWidth <= cell.clientWidth + 1)),
            ).toBe(true);
            expect(
                await button.evaluate((button) => {
                    const cell = button.closest('td')!.getBoundingClientRect();
                    const bounds = button.getBoundingClientRect();
                    return bounds.left >= cell.left && bounds.right <= cell.right;
                }),
            ).toBe(true);
        });
    }
}
