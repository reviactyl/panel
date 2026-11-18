import { expect, test, type Locator } from '@playwright/test';

const ringContrast = (ring: Locator) =>
    ring.evaluate((element) => {
        const rgb = (color: string) =>
            color
                .match(/[\d.]+/g)!
                .slice(0, 3)
                .map(Number);
        const luminance = (color: number[]) =>
            color
                .map((value) => value / 255)
                .map((value) => (value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4))
                .reduce((sum, value, index) => sum + value * [0.2126, 0.7152, 0.0722][index]!, 0);
        let surface: Element | null = element;
        while (surface && getComputedStyle(surface).backgroundColor === 'rgba(0, 0, 0, 0)') {
            surface = surface.parentElement;
        }
        if (!surface) throw new Error('Missing progress surface');
        const stroke = getComputedStyle(element).stroke;
        const background = getComputedStyle(surface).backgroundColor;
        const values = [luminance(rgb(stroke)), luminance(rgb(background))];
        return { stroke, background, ratio: (Math.max(...values) + 0.05) / (Math.min(...values) + 0.05) };
    });

for (const layout of ['list', 'grid']) {
    for (const theme of ['light', 'dark']) {
        test(`upload and URL download rings remain visible in ${theme} mode (${layout})`, async ({
            page,
        }, testInfo) => {
            const url = process.env.FILE_TRANSFER_DEMO_URL;
            test.skip(!url, 'Requires this task’s isolated Panel and Agent demo.');
            test.setTimeout(45000);
            page.setDefaultTimeout(8000);
            await page.setViewportSize({ width: 1280, height: 900 });
            await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
            await page.addInitScript((layout) => {
                let configuration: Record<string, unknown>;
                Object.defineProperty(window, 'PanelConfiguration', {
                    configurable: true,
                    get: () => configuration,
                    set: (value: Record<string, unknown>) => {
                        configuration = { ...value, fileLayout: layout };
                    },
                });
            }, layout);
            await page.goto(`${url}/auth/login`);
            await page.locator('input[name=username]').fill('demo');
            await page.locator('input[name=password]').fill('demo');
            await page.getByRole('button', { name: 'Login', exact: true }).click();
            await page.waitForURL(`${url}/`);
            const servers = await (await page.request.get(`${url}/api/client`)).json();
            const id = servers.data[0].attributes.identifier;
            await page.goto(`${url}/server/${id}/files`);
            await expect(page.locator('html')).toHaveClass(new RegExp(theme));
            await expect(page.getByRole('button', { name: 'Upload', exact: true })).toBeVisible();
            const tiles = page.locator('[class*="file_tile"]');
            if (layout === 'grid') await expect(tiles.first()).toBeVisible();
            else await expect(tiles).toHaveCount(0);
            const cdp = await page.context().newCDPSession(page);
            await cdp.send('Network.enable');
            await cdp.send('Network.emulateNetworkConditions', {
                offline: false,
                latency: 20,
                downloadThroughput: -1,
                uploadThroughput: 128 * 1024,
            });
            const filename = `ring-${theme}-${layout}-${Date.now()}.txt`;
            await page
                .locator('input[type=file]')
                .first()
                .setInputFiles({
                    name: filename,
                    mimeType: 'text/plain',
                    buffer: Buffer.alloc(1024 * 1024, 'a'),
                });
            const ring = page.locator('button circle[stroke-dasharray]').first();
            await expect(ring).toBeVisible();
            await expect
                .poll(async () => Number(await ring.evaluate((el) => (el as SVGElement).style.strokeDashoffset)))
                .toBeLessThan(28 * Math.PI);
            const uploadColors = await ringContrast(ring);
            await testInfo.attach('upload-contrast', {
                body: JSON.stringify(uploadColors),
                contentType: 'application/json',
            });
            await page.screenshot({ path: testInfo.outputPath(`${theme}-upload.png`) });
            expect.soft(uploadColors.ratio).toBeGreaterThanOrEqual(3);
            await ring.locator('xpath=ancestor::button').click();
            const dialog = page.getByRole('dialog');
            const rowRing = dialog.locator('circle[stroke-dasharray]');
            await expect(rowRing).toHaveCount(1);
            expect.soft((await ringContrast(rowRing)).ratio).toBeGreaterThanOrEqual(3);
            await dialog.getByRole('button', { name: 'Close', exact: true }).first().click();
            await expect(ring).toHaveCount(0, { timeout: 20000 });
            await expect(page.getByRole('link', { name: filename, exact: false })).toBeVisible();
            await cdp.send('Network.emulateNetworkConditions', {
                offline: false,
                latency: 0,
                downloadThroughput: -1,
                uploadThroughput: -1,
            });
            await page.getByRole('button', { name: 'URL Download', exact: true }).click();
            await page
                .locator('#url')
                .fill(
                    'https://raw.githubusercontent.com/reviactyl/panel/fa58940b8452ad8c018dcacc12c6eea75667e6d9/LICENSE',
                );
            await dialog.getByRole('button', { name: 'Download', exact: true }).click();
            const downloadRing = page.locator('circle[stroke-linecap=round][stroke-dasharray]');
            await expect(downloadRing).toBeVisible();
            const downloadColors = await ringContrast(downloadRing);
            await testInfo.attach('download-contrast', {
                body: JSON.stringify(downloadColors),
                contentType: 'application/json',
            });
            await page.screenshot({ path: testInfo.outputPath(`${theme}-download.png`) });
            expect.soft(downloadColors.ratio).toBeGreaterThanOrEqual(3);
            await expect(downloadRing).toHaveCount(0, { timeout: 20000 });
            await expect(page.locator('a[href$="/files/edit#/LICENSE"]')).toBeVisible();
            const downloaded = await page.request.get(`${url}/api/client/servers/${id}/files/contents?file=/LICENSE`);
            expect(downloaded.ok()).toBe(true);
            expect(await downloaded.text()).toContain('MIT License');
            await page.getByRole('button', { name: 'Action: LICENSE', exact: true }).click();
            await page.getByRole('button', { name: 'Rename', exact: true }).click();
            const renamed = `license-${theme}-${layout}-${Date.now()}.txt`;
            await page.locator('#file_name').fill(renamed);
            await page.getByRole('dialog').getByRole('button', { name: 'Rename', exact: true }).click();
            await expect(page.getByRole('link', { name: renamed, exact: false })).toBeVisible();
            await expect(page.getByRole('button', { name: 'Action: LICENSE', exact: true })).toHaveCount(0);
            await cdp.detach();
        });
    }
}
