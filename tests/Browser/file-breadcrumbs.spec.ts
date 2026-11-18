import { expect, test } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';

let script: string;
let css: string;
const directory = '/world backups/plugins #1/configuration/generated/regions/active-directory';

test.beforeAll(async () => {
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        plugins: [tailwindcss()],
        define: { 'process.env.NODE_ENV': JSON.stringify('production') },
        resolve: {
            alias: [
                { find: '@/state/server', replacement: resolve('tests/Browser/fixtures/file-breadcrumbs/store.ts') },
                { find: '@', replacement: resolve('resources/scripts') },
            ],
        },
        build: {
            write: false,
            lib: {
                entry: resolve('tests/Browser/fixtures/file-breadcrumbs/main.tsx'),
                name: 'BreadcrumbFixture',
                formats: ['iife'],
            },
        },
    });
    const outputs = (Array.isArray(result) ? result : [result]).flatMap((result) =>
        'output' in result ? result.output : [],
    );
    const chunk = outputs.find((output) => output.type === 'chunk');
    const stylesheet = outputs.find((output) => output.type === 'asset' && output.fileName.endsWith('.css'));
    if (!chunk || stylesheet?.type !== 'asset') throw new Error('Missing breadcrumb fixture assets');
    script = chunk.code;
    css = stylesheet.source.toString();
});

for (const width of [390, 1280]) {
    for (const editor of [false, true]) {
        test(`deep ${editor ? 'editor' : 'directory'} path is reachable at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            const path = directory + (editor ? '/server settings.txt' : '');
            const hash = path.split('/').map(encodeURIComponent).join('/');
            await page.setContent(
                `<body data-route='/server/server/files${editor ? '/edit' : ''}#${hash}'><div id='root'></div></body>`,
            );
            await page.addStyleTag({ content: css });
            await page.addScriptTag({ content: script });
            const viewport = page.getByTestId('breadcrumbs').locator('[dir=ltr]');
            const active = page.getByText(editor ? 'server settings.txt' : 'active-directory', { exact: true });
            await expect(active).toBeVisible();
            const bounds = await viewport.boundingBox();
            const end = await active.boundingBox();
            expect(end!.x + end!.width).toBeLessThanOrEqual(bounds!.x + bounds!.width + 1);
            expect(await viewport.evaluate((element) => getComputedStyle(element).overflowX)).toBe('auto');
            expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
            if (editor) {
                const wrapper = await page.getByTestId('breadcrumbs').boundingBox();
                expect(bounds!.x).toBe(wrapper!.x);
                await expect(page.getByRole('checkbox')).toHaveCount(0);
            } else {
                const selectAll = page.getByRole('checkbox', { name: 'Select All' });
                await expect(selectAll).toBeInViewport();
                await selectAll.check();
                await expect(selectAll).toBeChecked();
            }
            await viewport.evaluate((element) => {
                element.scrollLeft = 0;
            });
            const parent = page.getByRole('link', { name: 'plugins #1', exact: true });
            await parent.focus();
            await expect(parent).toBeInViewport();
            await expect(parent).toHaveAttribute('href', '/server/server/files#/world%20backups/plugins%20%231');
            await page.keyboard.press('Enter');
            await expect(page.getByRole('link', { name: 'plugins #1', exact: true })).toHaveCount(0);
            await expect(page.getByText('plugins #1', { exact: true })).toBeVisible();
        });
    }
}
