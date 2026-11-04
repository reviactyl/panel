import { expect, test } from '@playwright/test';

for (const key of ['Enter', 'Space']) {
    test(`opens file actions, Rename and Download with ${key}`, async ({ page }) => {
        const url = process.env.FILE_ACTIONS_DEMO_URL;
        test.skip(!url, 'Requires an isolated Panel and Agent demo.');
        await page.setViewportSize({ width: key === 'Enter' ? 1280 : 390, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        const base = `${url}/api/client/servers/${server.attributes.identifier}/files`;
        const filename = key === 'Enter' ? 'demo-check.txt' : 'sftp-check.txt';
        const original = await (await page.request.get(`${base}/contents?file=/${filename}`)).body();
        await page.goto(`${url}/server/${server.attributes.identifier}/files`);
        const file = page.getByRole('link', { name: filename, exact: false });
        await file.focus();
        await page.keyboard.press('Tab');
        const toggle = page.getByRole('button', { name: `Action: ${filename}`, exact: true });
        await expect(toggle).toBeFocused();
        await page.keyboard.press(key);
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        const menu = page.locator(`[id="${await toggle.getAttribute('aria-controls')}"]`);
        const toggleBox = await toggle.boundingBox();
        const menuBox = await menu.boundingBox();
        expect(Math.abs(menuBox!.x + menuBox!.width - (toggleBox!.x + toggleBox!.width))).toBeLessThan(3);

        for (const label of [
            'Rename',
            'Move',
            'Permissions',
            'Copy',
            'Archive',
            'Archive as tar.gz',
            'Archive as zip',
            'Download',
            'Delete',
        ]) {
            await page.keyboard.press('Tab');
            await expect(menu.getByRole('button', { name: label, exact: true })).toBeFocused();
        }
        await page.keyboard.press('Escape');
        await expect(toggle).toBeFocused();
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await page.keyboard.press(key);
        await page.keyboard.press('Tab');
        await expect(menu.getByRole('button', { name: 'Rename', exact: true })).toBeFocused();
        await page.keyboard.press(key);
        const name = page.getByRole('textbox', { name: 'File Name', exact: true });
        await expect(name).toBeFocused();
        await expect(name).toHaveValue(filename);
        await page.keyboard.press('Escape');
        await expect(name).toHaveCount(0);
        await expect(toggle).toBeFocused();
        await page.keyboard.press(key);
        for (let index = 0; index < 8; index++) await page.keyboard.press('Tab');
        await expect(menu.getByRole('button', { name: 'Download', exact: true })).toBeFocused();
        const downloaded = page.waitForEvent('download');
        await page.keyboard.press(key);
        const download = await downloaded;
        expect(download.suggestedFilename()).toBe(filename);
        const stream = await download.createReadStream();
        const chunks: Buffer[] = [];
        for await (const chunk of stream!) chunks.push(Buffer.from(chunk));
        expect(Buffer.concat(chunks)).toEqual(original);
        await expect(toggle).toBeFocused();
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    });
}
