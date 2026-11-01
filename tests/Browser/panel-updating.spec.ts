import { expect, test } from '@playwright/test';
import { readFileSync } from 'node:fs';

const source = readFileSync('resources/views/filament/pages/software-updates.blade.php', 'utf8');
const controller = source
    .split('x-data="')[1]!
    .split('"\n    x-init')[0]!
    .replaceAll("{{ $panelBusy ? 'true' : 'false' }}", 'false')
    .replace(/@js\(collect\([^\n]+/, 'null,')
    .replace(/panelUpdateMessage: @js\([^\n]+/, "panelUpdateMessage: '',");

for (const outcome of ['complete', 'failed']) {
    test(`waits through maintenance and restarting before ${outcome}`, async ({ page }) => {
        await page.goto('about:blank');
        const result = await page.evaluate(
            async ({ controller, outcome }) => {
                let reloads = 0;
                let pageReady = false;
                let response: any = { state: 'complete', version: 'old', updated_at: 'old' };
                const fakeWindow = {
                    location: {
                        origin: 'http://localhost',
                        href: 'http://localhost/admin/software-updates',
                        reload: () => reloads++,
                    },
                };
                const poller = new Function('window', 'fetch', `return (${controller})`)(
                    fakeWindow,
                    async (url: string | URL) => {
                        if (String(url) === fakeWindow.location.href) return { ok: pageReady };
                        if (response === null) throw new Error('Offline');
                        return { ok: true, status: 200, json: async () => response };
                    },
                );
                poller.panelUpdateBaseline = response;
                poller.panelUpdatePollingActive = true;
                poller.panelUpdateStartedAt = Date.now();
                const poll = async () => {
                    await poller.pollPanelUpdate();
                    clearTimeout(poller.panelUpdatePollTimer);
                };
                await poll();
                const staleReloads = reloads;
                response = null;
                await poll();
                response = { state: 'restarting', version: 'new', updated_at: 'new' };
                await poll();
                const earlyReloads = reloads;
                response = { ...response, state: outcome };
                await poll();
                const unavailablePageReloads = reloads;
                pageReady = true;
                await poll();
                return { staleReloads, earlyReloads, unavailablePageReloads, reloads };
            },
            { controller, outcome },
        );
        expect(result).toEqual({ staleReloads: 0, earlyReloads: 0, unavailablePageReloads: 0, reloads: 1 });
    });
}

for (const maintenanceResponse of [false, true]) {
    test(`demo update reloads the document (maintenance response: ${maintenanceResponse})`, async ({ page }) => {
        const url = process.env.UPDATE_DEMO_URL;
        test.skip(!url, 'Requires the isolated update simulation demo.');
        test.setTimeout(90000);
        const errors: string[] = [];
        page.on('pageerror', (error) => errors.push(error.message));
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo-admin');
        await page.locator('input[name=password]').fill('demo-admin');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        await page.goto(`${url}/admin/software-updates`);
        await page.waitForFunction(
            () => !!(document.querySelector('[x-data*=panelUpdateRequested]') as any)?._x_dataStack,
        );
        let downloadingPolls = 0;
        await page.route('**/admin/software-updates/status?*', async (route) => {
            const response = await route.fetch();
            const status = await response.json();
            if (status.state === 'downloading' && ++downloadingPolls > 1 && downloadingPolls <= 3) {
                return route.fulfill({ status: 503, body: 'Restarting' });
            }
            await route.fulfill({ response });
        });
        await page.route('**/update-proof.js', (route) =>
            route.fulfill({
                contentType: 'application/javascript',
                body: 'window.updateAssetLoaded = true;',
            }),
        );
        await page.route('**/admin/software-updates', async (route) => {
            const response = await route.fetch();
            if (!route.request().isNavigationRequest()) return route.fulfill({ response });
            const body = (await response.text()).replace(
                '</body>',
                '<div id="updated-ui">New admin UI</div><script src="/update-proof.js"></script></body>',
            );
            await route.fulfill({ response, body });
        });
        if (maintenanceResponse) {
            await page.route('**/livewire-*/update', async (route) => {
                if (route.request().postData()?.includes('updatePanel')) {
                    await route.fetch();
                    await route.fulfill({ status: 503, contentType: 'text/html', body: 'Maintenance' });
                } else {
                    await route.fulfill({ status: 503, contentType: 'text/html', body: 'Demo update maintenance' });
                }
            });
        }
        const reloaded = page.waitForEvent('load', { timeout: 45000 });
        await page.evaluate(() => {
            (window as any).originalUpdateButton = document.querySelector('table button');
        });
        await page.getByRole('button', { name: 'Update now', exact: true }).first().click();
        await expect(
            page.getByRole('button').filter({ hasText: /Waiting for Update Worker|Downloading/ }),
        ).toBeVisible();
        await expect(page.getByText('Updating your panel', { exact: true })).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Downloading', exact: true })).toBeVisible();
        expect(
            await page.evaluate(() => (window as any).originalUpdateButton === document.querySelector('table button')),
        ).toBe(true);
        await expect(page.locator('table').first().locator('tbody td').nth(4)).toHaveText('Out of date');
        await expect(page.getByText('Reconnecting to the panel…', { exact: true })).toBeVisible({ timeout: 30000 });
        await expect(page.getByRole('heading', { name: 'Software Update', exact: true })).toBeVisible();
        await expect(page.locator('iframe')).toHaveCount(0);
        await page.screenshot({ path: '/tmp/panel-updating-desktop.png' });
        await page.setViewportSize({ width: 390, height: 844 });
        await page.screenshot({ path: '/tmp/panel-updating-mobile.png' });
        await reloaded;
        await expect(page.locator('#updated-ui')).toHaveText('New admin UI');
        expect(await page.evaluate(() => (window as any).updateAssetLoaded)).toBe(true);
        const status = await page.request.get(`${url}/admin/software-updates/status`);
        expect((await status.json()).state).toBe('complete');
        await expect(page.getByRole('heading', { name: 'Software Update', exact: true })).toBeVisible();
        expect(errors).toEqual([]);
    });
}

test('a rejected retry with unchanged failure status returns to the page', async ({ page }) => {
    await page.goto('about:blank');
    const visible = await page.evaluate(async (controller) => {
        const status = { state: 'failed', version: 'old', updated_at: 'old' };
        const poller = new Function('window', 'fetch', `return (${controller})`)(
            { location: { origin: 'http://localhost' } },
            async () => ({ ok: true, status: 200, json: async () => status }),
        );
        poller.panelUpdateBaseline = status;
        poller.panelUpdatePollingActive = true;
        poller.panelUpdateRequested = true;
        poller.panelUpdateRequestRejected = true;
        poller.panelUpdateStartedAt = Date.now() - 11000;
        await poller.pollPanelUpdate();
        poller.stopPolling();
        return poller.panelUpdateRequested;
    }, controller);
    expect(visible).toBe(false);
});
