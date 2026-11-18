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
                entry: resolve('tests/Browser/fixtures/shared-controls/main.tsx'),
                name: 'Controls',
                formats: ['iife'],
            },
        },
    });
    const outputs = (Array.isArray(result) ? result : [result]).flatMap((output) =>
        'output' in output ? output.output : [],
    );
    const chunk = outputs.find((output) => output.type === 'chunk');
    const stylesheet = outputs.find((output) => output.type === 'asset' && output.fileName.endsWith('.css'));
    if (!chunk || stylesheet?.type !== 'asset') throw new Error('Missing shared control fixture assets');
    script = chunk.code;
    css = stylesheet.source.toString();
});

for (const theme of ['light', 'dark']) {
    for (const direction of ['ltr', 'rtl']) {
        test(`native control indicators and interactions in ${theme} ${direction}`, async ({ page }) => {
            const dark = theme === 'dark';
            await page.setViewportSize({ width: direction === 'ltr' ? 1280 : 390, height: 900 });
            await page.setContent(`<html class="${theme}" dir="${direction}"><style>:root {
                --color-200: ${dark ? '228 228 231' : '39 39 42'};
                --color-300: 161 161 170; --color-400: 113 113 122; --color-600: 82 82 91;
                --color-800: ${dark ? '39 39 42' : '228 228 231'};
                --color-950: ${dark ? '9 9 11' : '250 250 250'};
                --color-primary: 124 58 237; --color-danger: 185 28 28;
                --radius: 8px;
            }</style><div id="root"></div></html>`);
            await page.addStyleTag({ content: css });
            await page.addScriptTag({ content: script });
            const select = page.getByRole('combobox', { name: 'Action', exact: true });
            const start = page.getByRole('radio', { name: 'Start', exact: true });
            const stop = page.getByRole('radio', { name: 'Stop', exact: true });
            const checkbox = page.getByRole('checkbox', { name: 'Enabled' });
            const image = (locator: typeof select) =>
                locator.evaluate((element) => getComputedStyle(element).backgroundImage);
            await expect(select).toBeVisible();
            expect.soft(await image(select)).not.toBe('none');
            expect(await image(select)).toContain(await select.evaluate((element) => getComputedStyle(element).color));
            const position = await select.evaluate((element) => getComputedStyle(element).backgroundPositionX);
            expect(position).toBe(direction === 'rtl' ? '12px, 17px' : 'calc(100% - 17px), calc(100% - 12px)');
            expect(await image(page.getByRole('combobox', { name: 'Custom indicator' }))).toBe('none');
            expect.soft(decodeURIComponent(await image(start))).toContain('<circle');
            expect(decodeURIComponent(await image(checkbox))).toContain('<path');
            expect(await image(stop)).toBe('none');
            expect(
                await start.evaluate((element) => parseFloat(getComputedStyle(element).borderRadius)),
            ).toBeGreaterThanOrEqual(8);
            await page.getByText('Action', { exact: true }).click();
            await expect(select).toBeFocused();
            await page.keyboard.press('Escape');
            await select.press('ArrowDown');
            await select.press('Enter');
            await expect(select).toHaveValue('power');
            await select.press('Escape');
            await select.press('Tab');
            await expect(page.getByRole('combobox', { name: 'Custom indicator' })).toBeFocused();
            await page.keyboard.press('Tab');
            await expect(start).toBeFocused();
            await expect(start).toHaveCSS('outline-style', 'solid');
            await expect(start).toHaveCSS('outline-width', '2px');
            await start.press('ArrowDown');
            await expect(stop).toBeChecked();
            await expect(start).not.toBeChecked();
            expect(decodeURIComponent(await image(stop))).toContain('<circle');
            await stop.press('Space');
            await expect(stop).toBeChecked();
            await stop.press('Tab');
            await expect(checkbox).toBeFocused();
            await expect(page.getByRole('combobox', { name: 'Disabled select' })).toBeDisabled();
            expect(await image(page.getByRole('combobox', { name: 'Disabled select' }))).not.toBe('none');
            await expect(page.getByRole('radio', { name: 'Restart' })).toBeDisabled();
            await page.getByText('Start', { exact: true }).click();
            await expect(start).toBeChecked();
            const invalid = page.getByRole('textbox', { name: 'Required value' });
            await expect(invalid).toHaveAttribute('aria-describedby', 'error');
            await page.getByRole('button', { name: 'Save' }).click();
            await expect(invalid).toBeFocused();
            await expect(invalid).toHaveAttribute('data-has-error', 'true');
            expect(await invalid.evaluate((element: HTMLInputElement) => element.validity.valueMissing)).toBe(true);
            await invalid.fill('Valid');
            expect(
                await page.locator('form').evaluate((form: HTMLFormElement) => Object.fromEntries(new FormData(form))),
            ).toEqual({
                action: 'power',
                power: 'start',
                required: 'Valid',
            });
            await expect(select).toHaveJSProperty('tagName', 'SELECT');
        });
    }
}
