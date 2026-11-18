import { resolve } from 'node:path';
import { expect, test } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';

let script: string;
let css: string;
test.beforeAll(async () => {
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        plugins: [tailwindcss()],
        define: { 'process.env.NODE_ENV': JSON.stringify('production'), 'process.env': '{}' },
        resolve: { alias: { '@': resolve('resources/scripts') } },
        build: {
            write: false,
            lib: {
                entry: resolve('tests/Browser/fixtures/schedule-layout/main.tsx'),
                name: 'Schedules',
                formats: ['iife'],
            },
        },
    });
    const outputs = (Array.isArray(result) ? result : [result]).flatMap((output) =>
        'output' in output ? output.output : [],
    );
    script = outputs
        .filter((output) => output.type === 'chunk')
        .map((output) => output.code)
        .join('\n');
    css = outputs
        .flatMap((output) => (output.type === 'asset' && output.fileName.endsWith('.css') ? [output.source] : []))
        .join('\n');
});

for (const width of [320, 390, 640, 768, 1280]) {
    test.describe(`${width}px`, () => {
        test.beforeEach(async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.setContent(
                `<html><head><style>${css}</style></head><body><div id='root'></div><div id='modal-portal'></div></body></html>`,
            );
            await page.addScriptTag({ content: script });
        });
        test('shows the correct visible schedule status', async ({ page }) => {
            for (const [name, status] of [
                ['Schedule layout regression', 'Processing'],
                ['Inactive processing schedule', 'Processing'],
                ['Inactive schedule', 'Inactive'],
                ['Active schedule', 'Active'],
            ]) {
                await expect(
                    page.getByTestId(name!).getByText(status!, { exact: true }).filter({ visible: true }),
                ).toHaveCount(1);
            }
        });
        test('keeps every long cron value readable within its column', async ({ page }) => {
            const sections = [page.getByTestId('Schedule layout regression'), page.getByTestId('cron-detail')];
            for (const section of sections) {
                await expect(section).toBeVisible();
                const values = section.locator('p.font-medium').filter({ visible: true });
                await expect(values).toHaveCount(5);
                await expect(values.first()).toHaveText(Array.from({ length: 30 }, (_, index) => index * 2).join(','));
                for (const value of await values.all()) {
                    expect(
                        await value.evaluate((value) => {
                            const bounds = value.getBoundingClientRect();
                            const section = value.closest('[data-testid]')!.getBoundingClientRect();
                            const range = document.createRange();
                            range.selectNodeContents(value);
                            const tokens = [...value.childNodes].filter((node) => node.nodeType === Node.TEXT_NODE);
                            const numbersStayTogether = tokens.every((node) => {
                                const tokenRange = document.createRange();
                                tokenRange.selectNodeContents(node);
                                return [...tokenRange.getClientRects()].filter((rect) => rect.width > 0).length <= 1;
                            });
                            return (
                                numbersStayTogether &&
                                [...range.getClientRects()]
                                    .filter((rect) => rect.width > 0 && rect.height > 0)
                                    .every(
                                        (line) =>
                                            line.left >= bounds.left - 1 &&
                                            line.right <= bounds.right + 1 &&
                                            line.left >= section.left - 1 &&
                                            line.right <= section.right + 1 &&
                                            line.bottom <= bounds.bottom + 1,
                                    )
                            );
                        }),
                    ).toBe(true);
                }
            }
        });
        test('stacks task fields on phones and keeps desktop fields side by side', async ({ page }) => {
            await page.getByRole('button', { name: 'Open task form' }).click();
            const dialog = page.getByRole('dialog');
            const action = dialog.locator('select[name=action]');
            const offset = dialog.getByRole('textbox', { name: 'Time offset (in seconds)', exact: true });
            await expect(action).toBeVisible();
            await expect(offset).toBeVisible();
            const actionBounds = (await action.boundingBox())!;
            const offsetBounds = (await offset.boundingBox())!;
            if (width < 640) {
                expect(offsetBounds.y).toBeGreaterThan(actionBounds.y + actionBounds.height);
                expect(actionBounds.width).toBeGreaterThan(width * 0.65);
            } else {
                expect(Math.abs(offsetBounds.y - actionBounds.y)).toBeLessThan(2);
            }
            await offset.fill('60');
            await expect(offset).toHaveValue('60');
            await action.selectOption('power');
            await expect(dialog.locator('select[name=payload]')).toHaveValue('start');
            await expect(dialog.locator('form')).toBeVisible();
            expect(await dialog.locator('form').evaluate((form) => form.scrollWidth <= form.clientWidth)).toBe(true);
        });
    });
}
