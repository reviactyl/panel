import { expect, test } from '@playwright/test';

for (const key of ['Enter', 'Space']) {
    test(`opens image and video filenames with Tab and ${key}`, async ({ page }) => {
        const url = process.env.MEDIA_PREVIEW_DEMO_URL;
        test.skip(!url, 'Requires an isolated demo with keyboard-image.png, keyboard-video.mp4 and keyboard-folder.');
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        const filesUrl = `${url}/server/${server.attributes.identifier}/files`;
        await page.goto(filesUrl);
        for (const filename of ['keyboard-image.png', 'keyboard-video.mp4']) {
            const trigger = page.getByRole('button').filter({ has: page.locator(`[title="${filename}"]`) });
            await expect(trigger).toBeVisible();
            await page.getByRole('checkbox', { name: filename, exact: true }).focus();
            await page.keyboard.press('Tab');
            await expect(trigger).toBeFocused();
            await page.keyboard.press(key);
            await expect(page.getByRole('dialog')).toBeVisible();
            await expect(page.getByRole('heading', { name: filename, exact: true })).toBeVisible();
            await expect(page).toHaveURL(filesUrl);
            await page.keyboard.press('Escape');
            await expect(page.getByRole('dialog')).toHaveCount(0);
        }
        const folder = page.getByRole('link').filter({ has: page.locator('[title="keyboard-folder"]') });
        await folder.focus();
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(`${filesUrl}#/keyboard-folder`);
        await page.goto(filesUrl);
        const text = page.getByRole('link').filter({ has: page.locator('[title="welcome.txt"]') });
        await text.focus();
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(`${filesUrl}/edit#/welcome.txt`);
    });
}
