import { expect, test } from '@playwright/test';

for (const width of [390, 767, 768, 1280]) {
    test(`file select-all is visible and toggles the displayed rows at ${width}px`, async ({ page }) => {
        const url = process.env.FILE_SELECTION_DEMO_URL;
        test.skip(!url, 'Requires an isolated Panel and Agent demo.');
        await page.setViewportSize({ width, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.locator('button[type=submit]').click();
        await page.waitForURL(`${url}/`);
        const response = await page.request.get(`${url}/api/client`);
        const servers = await response.json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        expect(server).toBeTruthy();
        await page.goto(`${url}/server/${server.attributes.identifier}/files`);
        const rows = page.locator('input[name=selectedFiles]');
        await expect(rows.first()).toBeVisible();
        const count = await rows.count();
        const selectAll = page.getByRole('checkbox', { name: 'Select All', exact: true });
        await expect(selectAll).toBeVisible();
        await selectAll.check();
        await expect(page.locator('input[name=selectedFiles]:checked')).toHaveCount(count);
        await rows.first().uncheck();
        await expect(selectAll).not.toBeChecked();
        await selectAll.check();
        await expect(page.locator('input[name=selectedFiles]:checked')).toHaveCount(count);
        await selectAll.uncheck();
        await expect(page.locator('input[name=selectedFiles]:checked')).toHaveCount(0);
    });
}
