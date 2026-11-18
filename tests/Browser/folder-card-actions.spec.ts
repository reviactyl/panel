import { expect, test } from '@playwright/test';

const longName = 'Long folder name for production Minecraft servers and shared development environments';

for (const width of [1280, 768, 390]) {
    test.describe(`${width}px folder cards`, () => {
        test.use({ hasTouch: width === 390 });
        test('actions stay compact and clear of text', async ({ page }, testInfo) => {
            const url = process.env.FOLDER_ACTIONS_DEMO_URL;
            test.skip(!url, 'Requires an isolated Pi demo in folder mode with the long-name and Games fixtures.');
            await page.setViewportSize({ width, height: 900 });
            await page.goto(`${url}/auth/login`);
            await page.locator('input[name=username]').fill('demo');
            await page.locator('input[name=password]').fill('demo');
            await page.getByRole('button', { name: 'Login', exact: true }).click();
            await page.waitForURL(`${url}/`);

            for (const direction of ['ltr', 'rtl']) {
                await page.evaluate((dir) => (document.documentElement.dir = dir), direction);
                for (const name of [longName, 'Games']) {
                    const folder = page
                        .getByRole('button', { name, exact: false })
                        .filter({ has: page.getByText(name, { exact: true }) })
                        .first();
                    await expect(folder).toBeVisible();
                    const card = folder.locator('..');
                    await card.hover();
                    const edit = card.getByRole('button', { name: `Edit: ${name}`, exact: true });
                    const move = card.getByRole('button', { name: `Move Folder: ${name}`, exact: true });
                    const remove = card.getByRole('button', { name: `Delete: ${name}`, exact: true });
                    const actions = [edit, move, remove];
                    const boxes = await Promise.all(actions.map((action) => action.boundingBox()));
                    expect(boxes.every(Boolean)).toBe(true);
                    for (const box of boxes) {
                        expect(box!.width).toBeCloseTo(boxes[0]!.width, 0);
                        expect(box!.height).toBeCloseTo(boxes[0]!.height, 0);
                        expect(box!.width).toBeLessThanOrEqual(36);
                        expect(box!.height).toBeLessThanOrEqual(36);
                    }
                    for (const text of [folder.getByText(name, { exact: true }), folder.locator('span.truncate')]) {
                        const bounds = (await text.boundingBox())!;
                        for (const box of boxes) {
                            const overlaps =
                                bounds.x < box!.x + box!.width &&
                                bounds.x + bounds.width > box!.x &&
                                bounds.y < box!.y + box!.height &&
                                bounds.y + bounds.height > box!.y;
                            expect(overlaps, `${direction}: actions must not overlap ${name} or its description`).toBe(
                                false,
                            );
                        }
                    }
                    await page.mouse.move(0, 0);
                    await folder.focus();
                    await page.keyboard.press('Tab');
                    await expect(edit).toBeFocused();
                    await expect(edit.locator('..')).toHaveCSS('opacity', '1');
                    await page.keyboard.press('Enter');
                    await expect(page.getByRole('dialog').getByRole('heading')).toBeVisible();
                    await expect(page.locator('#folder-name')).toBeVisible();
                    await expect(page.locator('#folder-name')).toHaveValue(name);
                    await page.getByRole('button', { name: 'Close', exact: true }).click();
                    await move.focus();
                    await page.keyboard.press('Space');
                    await expect(page.getByRole('dialog').getByRole('heading')).toBeVisible();
                    await page.getByRole('button', { name: 'Close', exact: true }).click();
                    await remove.click();
                    await expect(page.getByRole('dialog').getByRole('heading')).toBeVisible();
                    await page.getByRole('button', { name: 'Cancel', exact: true }).click();
                    await folder.click();
                    await expect(page.getByText(name, { exact: true })).toBeVisible();
                    await page.getByRole('button', { name: 'Home', exact: true }).click();
                }
            }
            await page.screenshot({ path: testInfo.outputPath('folder-cards.png'), fullPage: true });
            await page.getByRole('button', { name: 'Create Folder', exact: true }).click();
            await expect(page.getByRole('dialog').getByRole('heading')).toBeVisible();
            await page.getByRole('button', { name: 'Close', exact: true }).click();
        });
    });
}
