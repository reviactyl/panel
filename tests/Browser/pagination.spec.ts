import { expect, test, type Locator } from '@playwright/test';
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
                entry: resolve('tests/Browser/fixtures/pagination/main.tsx'),
                name: 'Pagination',
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
    expect(css).not.toBe('');
});

async function expectButtonsToFit(section: Locator, width: number) {
    const boxes = await section.locator('button').evaluateAll((buttons) =>
        buttons.map((button) => {
            const box = button.getBoundingClientRect();
            return {
                left: box.left,
                right: box.right,
                width: box.width,
                height: box.height,
                fits: button.scrollWidth <= button.clientWidth,
            };
        }),
    );
    expect(boxes.length).toBeGreaterThan(0);
    for (const box of boxes) {
        expect(box.left).toBeGreaterThanOrEqual(0);
        expect(box.right).toBeLessThanOrEqual(width);
        expect(box.width).toBeGreaterThanOrEqual(32);
        expect(box.height).toBeGreaterThanOrEqual(32);
        expect(box.fits).toBe(true);
    }
}

for (const width of [320, 390, 1280]) {
    for (const direction of ['ltr', 'rtl']) {
        test(`pagination keeps multi-digit controls usable at ${width}px ${direction}`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.setContent(`<html dir='${direction}'><body><div id='root'></div></body></html>`);
            await page.addStyleTag({ content: css });
            await page.addScriptTag({ content: script });
            const numbered = page.getByTestId('numbered');
            const table = page.getByTestId('table');
            await expect(numbered.locator('[aria-current=page]')).toHaveText('96');
            await expect(numbered.locator('button:has(svg)')).toHaveCount(1);
            await expect(numbered.getByRole('button', { name: '101', exact: true })).toHaveCount(1);
            await expectButtonsToFit(numbered, width);
            await expectButtonsToFit(table, width);
            const last = numbered.getByRole('button', { name: '101', exact: true });
            await last.focus();
            await page.keyboard.press('Shift+Tab');
            await page.keyboard.press('Tab');
            await expect(last).toBeFocused();
            await page.keyboard.press('Enter');
            await expect(numbered).toContainText('Rows for page 101');
            await numbered.locator('button:has(svg)').click();
            await expect(numbered).toContainText('Rows for page 1');
            await numbered.locator('button:has(svg)').focus();
            await page.keyboard.press('Space');
            await expect(numbered).toContainText('Rows for page 101');

            await expect(table.locator('button:has(svg)').first()).toBeEnabled();
            await table.locator('button:has(svg)').first().focus();
            await page.keyboard.press('Enter');
            await expect(table.locator('[aria-current=page]')).toHaveText('1');
            await expect(table.locator('button:has(svg)').first()).toBeDisabled();
            await table.locator('button:has(svg)').last().click();
            await expect(table.locator('[aria-current=page]')).toHaveText('101');
            await expectButtonsToFit(table, width);
            await table.getByRole('button', { name: '100', exact: true }).click();
            await expect(table.locator('[aria-current=page]')).toHaveText('100');
            await expectButtonsToFit(table, width);
            await expect(table.locator('button:has(svg)').last()).toBeEnabled();
            await table.locator('button:has(svg)').last().focus();
            await page.keyboard.press('Space');
            await expect(table.locator('[aria-current=page]')).toHaveText('101');
        });
    }
}
