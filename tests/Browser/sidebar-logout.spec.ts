import { expect, test } from '@playwright/test';

for (const key of ['Enter', 'Space']) {
    test(`modern sidebar logout activates with ${key}`, async ({ page }) => {
        const url = process.env.SIDEBAR_LOGOUT_DEMO_URL;
        test.skip(!url, 'Requires an isolated Pi demo with modern sidebar logout enabled.');
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.locator('button[type=submit]').click();
        await page.waitForURL(`${url}/`);

        const logout = page.getByRole('button', { name: 'Logout', exact: true });
        await expect(logout).toBeVisible();
        await page.getByRole('link', { name: 'Demo User', exact: true }).focus();
        await page.keyboard.press('Tab');
        await expect(logout).toBeFocused();
        const focusStyle = await logout.evaluate((button) => {
            const style = getComputedStyle(button);
            return { visible: button.matches(':focus-visible'), width: style.outlineWidth, style: style.outlineStyle };
        });
        expect(focusStyle.visible).toBe(true);
        expect(parseFloat(focusStyle.width)).toBeGreaterThanOrEqual(2);
        expect(focusStyle.style).toBe('solid');

        let release!: () => void;
        const pending = new Promise<void>((resolve) => (release = resolve));
        await page.route('**/auth/logout', async (route) => {
            await pending;
            await route.continue();
        });
        const request = page.waitForRequest((request) => request.url() === `${url}/auth/logout`);
        await page.keyboard.press(key);
        expect((await request).method()).toBe('POST');
        await expect(page.locator('.absolute.top-0.left-0.z-30')).toBeVisible();
        release();
        await page.waitForURL(`${url}/auth/login`);
    });
}
