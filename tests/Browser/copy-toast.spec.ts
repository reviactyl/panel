import { expect, test } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';

let javascript: string;
let css: string;
test.beforeAll(async () => {
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        publicDir: false,
        plugins: [tailwindcss()],
        define: { 'process.env.NODE_ENV': JSON.stringify('production'), 'process.env': '{}' },
        resolve: { alias: { '@': resolve('resources/scripts') } },
        build: {
            write: false,
            lib: { entry: resolve('tests/Browser/fixtures/copy-toast/main.tsx'), name: 'CopyToast', formats: ['iife'] },
        },
    });
    const outputs = (Array.isArray(result) ? result : [result]).flatMap((output) =>
        'output' in output ? output.output : [],
    );
    const chunk = outputs.find((output) => output.type === 'chunk');
    const stylesheet = outputs.find((output) => output.type === 'asset' && output.fileName.endsWith('.css'));
    if (!chunk || chunk.type !== 'chunk' || !stylesheet || stylesheet.type !== 'asset') {
        throw new Error('Missing copy toast fixture bundle or stylesheet');
    }
    javascript = chunk.code;
    css = String(stylesheet.source);
});

test.beforeEach(async ({ page, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write'], { origin: 'https://copy-toast.test' });
    await page.route('https://copy-toast.test/', (route) =>
        route.fulfill({ contentType: 'text/html', body: '<div id="root"></div><div id="modal-portal"></div>' }),
    );
    await page.goto('https://copy-toast.test/');
    await page.addStyleTag({ content: css });
    await page.addScriptTag({ content: javascript });
    await page.evaluate(() => {
        document.addEventListener(
            'copy',
            () => {
                document.body.dataset.copied = document.getSelection()?.toString() ?? '';
            },
            true,
        );
    });
});

test('copies numeric zero with pointer, Enter and Space and expires notifications', async ({ page }) => {
    const zero = page.getByRole('button', { name: 'Copy zero', exact: true });
    await zero.click();
    expect(await page.evaluate(() => navigator.clipboard.readText())).toBe('0');
    await expect(page.locator('#modal-portal')).toContainText('Copied "0" to clipboard.');
    for (const key of ['Enter', 'Space']) {
        await zero.focus();
        await zero.press(key);
    }
    await expect(page.locator('#modal-portal p')).toHaveCount(3);
    await expect(page.locator('#modal-portal p')).toHaveCount(0, { timeout: 5000 });
});

for (const width of [320, 390, 1023, 1024, 1280]) {
    test(`wraps long values and clears navigation at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 900 });
        await page.getByRole('button', { name: 'Copy long value', exact: true }).click();
        await expect(page.locator('body')).toHaveAttribute('data-copied', 'x'.repeat(300));
        const toast = page.locator('#modal-portal p');
        await expect(toast).toContainText('x'.repeat(300));
        const bounds = await toast.evaluate((node) => {
            const card = node.parentElement!;
            const rect = card.getBoundingClientRect();
            const nav = document.querySelector('nav')!.getBoundingClientRect();
            return {
                left: rect.left,
                right: rect.right,
                bottom: rect.bottom,
                height: rect.height,
                scroll: node.scrollWidth,
                client: node.clientWidth,
                navTop: nav.top,
            };
        });
        expect(bounds.left).toBeGreaterThanOrEqual(0);
        expect(bounds.right).toBeLessThanOrEqual(width);
        expect(bounds.scroll).toBeLessThanOrEqual(bounds.client);
        expect(bounds.height).toBeGreaterThan(70);
        if (width < 1024) {
            expect(bounds.bottom).toBeLessThan(bounds.navTop);
            const menu = page.getByRole('button', { name: 'Show sidebar', exact: true });
            await menu.click();
            await expect(page.getByRole('button', { name: 'Close sidebar', exact: true })).toHaveAttribute(
                'aria-expanded',
                'true',
            );
            await expect(page.getByRole('link', { name: 'Home', exact: true })).toBeVisible();
            await expect(page.getByRole('link', { name: 'Account', exact: true })).toBeVisible();
        } else {
            expect(bounds.bottom).toBeGreaterThan(850);
        }
    });
}
