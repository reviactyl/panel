import { expect, test } from '@playwright/test';

for (const width of [1280, 390]) {
    test(`announces dashboard category keyboard toggles and restores collapse at ${width}px`, async ({ page }) => {
        const url = process.env.CATEGORY_STATE_DEMO_URL;
        test.skip(!url, 'Requires this task’s isolated Pi demo with category layout enabled.');
        await page.setViewportSize({ width, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const button = page.getByRole('button', { name: /^Primary/ });
        await expect(button).toBeVisible();
        await expect(button).toHaveAttribute('aria-expanded', 'true');
        const contentId = await button.getAttribute('aria-controls');
        expect(contentId).toBeTruthy();
        const content = page.locator(`[id="${contentId}"]`);
        await expect(content.getByRole('link', { name: /Demo Playground/ }).first()).toBeVisible();
        await button.focus();
        await page.keyboard.press('Space');
        await expect(button).toBeFocused();
        await expect(button).toHaveAttribute('aria-expanded', 'false');
        await expect(content).toBeEmpty();
        await expect(button).toHaveAttribute('aria-controls', contentId!);
        await page.reload();
        await expect(button).toHaveAttribute('aria-expanded', 'false');
        await button.focus();
        await page.keyboard.press('Enter');
        await expect(button).toBeFocused();
        await expect(button).toHaveAttribute('aria-expanded', 'true');
        const restoredId = await button.getAttribute('aria-controls');
        await expect(
            page
                .locator(`[id="${restoredId}"]`)
                .getByRole('link', { name: /Demo Playground/ })
                .first(),
        ).toBeVisible();
        await button.click();
        await expect(button).toHaveAttribute('aria-expanded', 'false');
        await button.click();
        await expect(button).toHaveAttribute('aria-expanded', 'true');
        await page.reload();
        await expect(button).toHaveAttribute('aria-expanded', 'true');
    });
}
