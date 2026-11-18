import { expect, test } from '@playwright/test';

const longCategoryName = 'W'.repeat(191);

for (const width of [1280, 768, 390, 320]) {
    test(`keeps category names and controls separate at ${width}px`, async ({ page }, testInfo) => {
        const url = process.env.CATEGORY_HEADER_DEMO_URL;
        test.skip(!url, 'Requires an isolated Pi demo with a server assigned to a 191-character W category.');
        await page.setViewportSize({ width, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);

        for (const name of [longCategoryName, 'Games']) {
            if (name === 'Games') {
                await page.route('**/api/client/account/categories', async (route) => {
                    const response = await route.fetch();
                    const body = await response.json();
                    for (const category of body.data) {
                        if (category.attributes.name === longCategoryName) category.attributes.name = name;
                    }
                    await route.fulfill({ response, json: body });
                });
                await page.reload();
            }

            const button = page.locator('button[aria-controls][aria-expanded]').filter({ hasText: name });
            await expect(button).toBeVisible();
            for (const direction of ['ltr', 'rtl']) {
                await page.evaluate((dir) => (document.documentElement.dir = dir), direction);
                const layout = await button.evaluate((header, label) => {
                    const spans = Array.from(header.querySelectorAll('span'));
                    const text = spans.find((span) =>
                        Array.from(span.childNodes).some(
                            (node) => node.nodeType === Node.TEXT_NODE && node.textContent === label,
                        ),
                    )!;
                    const count = spans.find((span) => span.textContent === '1')!;
                    const box = (element: Element) => {
                        const rect = element.getBoundingClientRect();
                        return { left: rect.left, right: rect.right, width: rect.width };
                    };
                    return {
                        header: box(header),
                        text: box(text),
                        count: box(count),
                        icons: Array.from(header.querySelectorAll('svg')).map(box),
                        ellipsis: getComputedStyle(text).textOverflow,
                        truncated: text.scrollWidth > text.clientWidth,
                    };
                }, name);
                expect(layout.ellipsis).toBe('ellipsis');
                expect(layout.truncated).toBe(name === longCategoryName);
                expect(layout.icons).toHaveLength(2);
                for (const control of [layout.count, ...layout.icons]) {
                    expect(control.width).toBeGreaterThanOrEqual(16);
                    expect(control.left).toBeGreaterThanOrEqual(layout.header.left);
                    expect(control.right).toBeLessThanOrEqual(layout.header.right);
                    expect(control.right <= layout.text.left || control.left >= layout.text.right).toBe(true);
                }
                await button.focus();
                await page.keyboard.press('Space');
                await expect(button).toHaveAttribute('aria-expanded', 'false');
                await expect(button).toBeFocused();
                await page.keyboard.press('Enter');
                await expect(button).toHaveAttribute('aria-expanded', 'true');
                const contentId = await button.getAttribute('aria-controls');
                await expect(page.locator(`[id="${contentId}"]`).getByRole('link').first()).toBeVisible();
                await button.click();
                await expect(button).toHaveAttribute('aria-expanded', 'false');
                await button.click();
                await expect(button).toHaveAttribute('aria-expanded', 'true');
            }
            await page.screenshot({
                path: testInfo.outputPath(`${name === longCategoryName ? 'long' : 'ordinary'}-category.png`),
            });
            await page.getByRole('button', { name: 'Manage', exact: true }).click();
            await expect(page.getByRole('dialog').getByRole('heading')).toHaveText('Manage Server Categories');
            await page.getByRole('button', { name: 'Close', exact: true }).click();
            const filter = page.getByRole('button', { name: 'Server Filter', exact: true });
            await filter.click();
            await expect(page.getByRole('combobox', { name: 'All Categories', exact: true })).toBeVisible();
            await filter.click();
        }
    });
}

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
