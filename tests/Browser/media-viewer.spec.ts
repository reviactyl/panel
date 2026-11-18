import { expect, test } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';

let script: string;
let css: string;
test.beforeAll(async () => {
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        plugins: [tailwindcss()],
        define: { 'process.env.NODE_ENV': JSON.stringify('production') },
        resolve: { alias: { '@': resolve('resources/scripts') } },
        build: {
            write: false,
            lib: {
                entry: resolve('tests/Browser/fixtures/media-viewer/main.tsx'),
                name: 'MediaViewer',
                formats: ['iife'],
            },
        },
    });
    const outputs = (Array.isArray(result) ? result : [result]).flatMap((output) =>
        'output' in output ? output.output : [],
    );
    script = outputs
        .filter((output) => output.type === 'chunk')
        .map((chunk) => chunk.code)
        .join('\n');
    css = outputs
        .flatMap((output) => (output.type === 'asset' && output.fileName.endsWith('.css') ? [output.source] : []))
        .join('\n');
});

for (const [width, height] of [
    [320, 667],
    [667, 375],
    [1280, 800],
] as const) {
    test.describe(`${width}x${height} media previews`, () => {
        test.beforeEach(async ({ page }) => {
            await page.setViewportSize({ width, height });
            await page.setContent('<div id="root"></div>');
            await page.addStyleTag({ content: css });
            await page.addScriptTag({ content: script });
        });
        for (const type of ['mp4', 'webm']) {
            test(`${type} uses its source type and the available player width`, async ({ page }) => {
                await page.getByRole('button', { name: type, exact: true }).click();
                const dialog = page.getByRole('dialog', { name: `clip.${type}` });
                await expect(dialog.locator('video source')).toHaveAttribute('type', `video/${type}`);
                const player = dialog.locator('.plyr');
                await expect(player).toBeVisible();
                await expect
                    .poll(() =>
                        player.evaluate((element) => {
                            const box = element.getBoundingClientRect();
                            const parent = element.parentElement!.getBoundingClientRect();
                            return box.width / parent.width;
                        }),
                    )
                    .toBeGreaterThan(0.95);
                const box = await player.boundingBox();
                expect(box!.x).toBeGreaterThanOrEqual(0);
                expect(box!.x + box!.width).toBeLessThanOrEqual(width);
                expect(box!.height).toBeLessThan(height);
                await page.getByRole('button', { name: 'Close', exact: true }).click();
                await expect(dialog).toHaveCount(0);
            });
        }
        test('expanded image stays inside the dialog and returns focus when closed', async ({ page }) => {
            await page.getByRole('button', { name: 'image', exact: true }).click();
            const dialog = page.getByRole('dialog', { name: 'large image.png' });
            const image = dialog.getByRole('img', { name: 'large image.png', exact: true });
            await expect(image).toBeVisible();
            const box = await image.boundingBox();
            expect(box!.width).toBeLessThanOrEqual(width);
            expect(box!.height).toBeLessThan(height);
            await image.click();
            await expect(dialog).toBeVisible();
            const expanded = dialog.locator('.viewer-container');
            await expect(expanded).toBeVisible();
            const expandedImage = expanded.locator('.viewer-canvas img');
            await expect(expandedImage).toBeVisible();
            await expect(expanded.locator('.viewer-title')).toContainText('1600 × 1200');
            const beforeZoom = await expandedImage.evaluate((element) => element.style.width);
            await expanded.locator('.viewer-zoom-in').click();
            await expect.poll(() => expandedImage.evaluate((element) => element.style.width)).not.toBe(beforeZoom);
            await expanded.locator('.viewer-rotate-right').click();
            await expect(expandedImage).toHaveAttribute('style', /rotate\(90deg\)/);
            await page.keyboard.press('Escape');
            await expect(expanded).toHaveCount(0);
            await expect(dialog).toBeVisible();
            const trigger = dialog.getByRole('button', { name: 'large image.png', exact: true });
            await expect(trigger).toBeFocused();
            await page.keyboard.press('Enter');
            await expect(expanded).toBeVisible();
            await expanded.locator('.viewer-button').click();
            await expect(expanded).toHaveCount(0);
            await expect(trigger).toBeFocused();
            await page.keyboard.press('Space');
            await expect(expanded).toBeVisible();
            await page.keyboard.press('Escape');
            await expect(expanded).toHaveCount(0);
            await page.keyboard.press('Escape');
            await expect(dialog).toHaveCount(0);
            await expect(page.getByRole('button', { name: 'image', exact: true })).toBeFocused();
        });
        test('unmounting an expanded image removes the viewer and body lock', async ({ page }) => {
            await page.getByRole('button', { name: 'image', exact: true }).click();
            await page.getByRole('img', { name: 'large image.png', exact: true }).click();
            await expect(page.locator('.viewer-container')).toBeVisible();
            await page.evaluate(() => window.dispatchEvent(new Event('unmount-media-fixture')));
            await expect(page.locator('.viewer-container')).toHaveCount(0);
            await expect(page.locator('body')).not.toHaveClass(/viewer-open/);
        });
    });
}
