import { expect, test } from '@playwright/test';

for (const width of [1280, 390]) {
    test(`contains Create Backup focus and restores its opener at ${width}px`, async ({ page }) => {
        const url = process.env.MODAL_FOCUS_DEMO_URL;
        test.skip(!url, 'Requires an isolated Panel and Agent demo.');
        await page.setViewportSize({ width, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        await page.goto(`${url}/server/${server.attributes.identifier}/backups`);
        const trigger = page.locator('button').filter({ hasText: /^Create server backup$/ });
        for (const method of ['escape', 'close', 'background']) {
            await trigger.focus();
            await page.keyboard.press('Enter');
            const dialog = page.getByRole('dialog', { name: 'Create server backup', exact: true });
            await expect(dialog).toBeVisible();
            await expect(dialog).toHaveAttribute('aria-modal', 'true');
            await expect.poll(() => dialog.evaluate((node) => node.contains(document.activeElement))).toBe(true);
            await expect(trigger).not.toBeFocused();
            expect(await trigger.evaluate((node) => !!node.closest('[inert]'))).toBe(true);
            for (const key of ['Tab', 'Shift+Tab']) {
                for (let index = 0; index < 12; index++) {
                    await page.keyboard.press(key);
                    expect(await dialog.evaluate((node) => node.contains(document.activeElement))).toBe(true);
                }
            }
            await trigger.evaluate((node) => node.focus());
            expect(await dialog.evaluate((node) => node.contains(document.activeElement))).toBe(true);
            if (method === 'escape') await page.keyboard.press('Escape');
            else if (method === 'close') {
                await dialog.getByRole('button', { name: 'Close', exact: true }).focus();
                await page.keyboard.press('Enter');
            } else await dialog.locator('.fixed').click({ position: { x: 4, y: 4 } });
            await expect(dialog).toHaveCount(0);
            await expect(trigger).toBeFocused();
            expect(await trigger.evaluate((node) => !!node.closest('[inert]'))).toBe(false);
        }
    });
}
