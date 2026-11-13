import { expect, test } from '@playwright/test';

for (const key of ['Enter', 'Space']) {
    test(`names and activates backup actions with ${key}`, async ({ page }) => {
        const url = process.env.BACKUP_ACTIONS_DEMO_URL;
        test.skip(!url, 'Requires this task’s isolated Pi demo with completed and failed backup fixtures.');
        await page.setViewportSize({ width: key === 'Enter' ? 1280 : 390, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.getByRole('textbox', { name: 'Username or Email', exact: true }).fill('demo');
        await page.getByRole('textbox', { name: 'Password', exact: true }).fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        expect(server).toBeTruthy();
        const base = `${url}/server/${server.attributes.identifier}`;
        const api = `${url}/api/client/servers/${server.attributes.identifier}/backups`;
        await page.goto(`${base}/backups`);

        const failedName = `REV-103 failed ${key}`;
        const failed = page.getByRole('button', { name: new RegExp(`^Delete.*${failedName}`) });
        await expect(failed).toBeVisible();
        await failed.focus();
        await page.keyboard.press('Shift+Tab');
        await page.keyboard.press('Tab');
        await expect(failed).toBeFocused();
        await page.keyboard.press(key);
        const dialog = page.getByRole('dialog');
        await expect(dialog).toContainText(failedName);
        await expect(failed).toBeVisible();
        await dialog.getByRole('button', { name: 'Cancel', exact: true }).focus();
        await page.keyboard.press(key);
        await expect(dialog).not.toBeVisible();
        await expect(failed).toBeVisible();

        const completedName = `REV-103 completed ${key}`;
        await expect
            .poll(async () => {
                const backups = await (await page.request.get(api)).json();
                return backups.data.some(
                    (item: { attributes: { name: string; is_successful: boolean } }) =>
                        item.attributes.name === completedName && item.attributes.is_successful,
                );
            })
            .toBe(true);
        await page.reload();
        const toggle = page.getByRole('button', { name: `Actions for ${completedName}`, exact: true });
        await expect(toggle).toBeVisible({ timeout: 20000 });
        await toggle.focus();
        await page.keyboard.press('Shift+Tab');
        await page.keyboard.press('Tab');
        await expect(toggle).toBeFocused();
        await page.keyboard.press(key);
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).not.toBeVisible();
        await expect(toggle).toBeFocused();
        await page.keyboard.press(key);
        await page.keyboard.press('Tab');
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).toBeFocused();
        await page.keyboard.press('Escape');
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).not.toBeVisible();
        await expect(toggle).toBeFocused();
        await page.keyboard.press(key);
        await page.keyboard.press('Tab');
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).toBeFocused();
        for (const label of ['Preview', 'Download', 'Restore', 'Lock', 'Delete']) {
            await page.keyboard.press('Tab');
            await expect(page.getByRole('button', { name: label, exact: true })).toBeFocused();
        }
        await page.keyboard.press(key);
        await expect(dialog).toContainText(completedName);
        await dialog.getByRole('button', { name: 'Cancel', exact: true }).focus();
        await page.keyboard.press(key);
        await expect(dialog).not.toBeVisible();
        await expect(toggle).toBeVisible();

        await toggle.click();
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).toBeVisible();
        await page.getByText(completedName, { exact: true }).click();
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).not.toBeVisible();
        await page.getByText(completedName, { exact: true }).click({ button: 'right' });
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).not.toBeVisible();
        await expect(toggle).toBeFocused();
        await toggle.click();
        await page.getByRole('button', { name: 'Delete', exact: true }).click();
        await expect(dialog).toContainText(completedName);
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).not.toBeVisible();
        await dialog.getByRole('button', { name: 'Cancel', exact: true }).click();
        await expect(dialog).not.toBeVisible();
        await expect(toggle).toBeVisible();
        const backups = await (await page.request.get(api)).json();
        for (const name of [failedName, completedName]) {
            expect(backups.data.some((item: { attributes: { name: string } }) => item.attributes.name === name)).toBe(
                true,
            );
        }
    });
}
