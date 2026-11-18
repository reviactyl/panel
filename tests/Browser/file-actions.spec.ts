import { expect, test, type Locator } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';

const menuColors = (row: Locator) =>
    row.evaluate((element) => {
        const style = getComputedStyle(element);
        const root = getComputedStyle(document.documentElement);
        const channels = (value: string) =>
            value
                .match(/[\d.]+/g)!
                .slice(0, 3)
                .map(Number);
        const luminance = (color: number[]) =>
            color
                .map((value) => value / 255)
                .map((value) => (value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4))
                .reduce((sum, value, index) => sum + value * [0.2126, 0.7152, 0.0722][index]!, 0);
        const foreground = channels(style.color);
        const background = channels(style.backgroundColor);
        const values = [luminance(foreground), luminance(background)];
        return {
            background,
            foreground,
            expectedBackground: channels(root.getPropertyValue('--color-700')),
            expectedForeground: channels(root.getPropertyValue('--text-danger')),
            contrast: (Math.max(...values) + 0.05) / (Math.min(...values) + 0.05),
            outline: style.outlineStyle,
            outlineWidth: style.outlineWidth,
        };
    });

test.describe('shared file menu row contrast', () => {
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
                    entry: resolve('tests/Browser/fixtures/file-actions/main.tsx'),
                    name: 'FileMenuFixture',
                    formats: ['iife'],
                },
            },
        });
        const outputs = (Array.isArray(result) ? result : [result]).flatMap((output) =>
            'output' in output ? output.output : [],
        );
        const chunk = outputs.find((output) => output.type === 'chunk');
        const stylesheet = outputs.find((output) => output.type === 'asset' && output.fileName.endsWith('.css'));
        if (!chunk || stylesheet?.type !== 'asset') throw new Error('Missing file menu fixture assets');
        script = chunk.code;
        css = stylesheet.source.toString();
    });

    for (const theme of ['light', 'dark']) {
        test(`normal and danger hover/focus retain readable ${theme} surfaces`, async ({ page }) => {
            const dark = theme === 'dark';
            await page.setViewportSize({ width: dark ? 1280 : 390, height: 900 });
            await page.setContent(`<html class="${theme}"><style>:root {
                --color-100: ${dark ? '244 244 245' : '24 24 27'};
                --color-700: ${dark ? '63 63 70' : '212 212 216'};
                --color-800: ${dark ? '39 39 42' : '228 228 231'};
                --color-900: ${dark ? '24 24 27' : '244 244 245'};
                --color-950: ${dark ? '9 9 11' : '250 250 250'};
                --color-danger: 143 31 32;
            }</style><div id="root"></div></html>`);
            await page.addStyleTag({ content: css });
            await page.addScriptTag({ content: script });
            for (const name of ['Rename', 'Delete', 'Delete permanently', 'Empty']) {
                const row = page.getByRole('button', { name, exact: true });
                if (name !== 'Rename') {
                    const resting = await menuColors(row);
                    expect(resting.foreground).toEqual(resting.expectedForeground);
                }
                await row.hover();
                await expect
                    .poll(async () => (await menuColors(row)).background)
                    .toEqual((await menuColors(row)).expectedBackground);
                const hovered = await menuColors(row);
                expect(hovered.contrast).toBeGreaterThanOrEqual(4.5);
                await page.mouse.move(500, 500);
                await row.focus();
                await page.keyboard.press('Shift');
                await expect.poll(async () => (await menuColors(row)).background).toEqual(hovered.background);
                const focused = await menuColors(row);
                expect(focused.foreground).toEqual(hovered.foreground);
                expect(focused.contrast).toBeGreaterThanOrEqual(4.5);
                expect(focused.outline).toBe('solid');
                expect(focused.outlineWidth).toBe('2px');
            }
            const disabled = page.getByRole('button', { name: 'Unavailable' });
            await disabled.hover({ force: true });
            await expect(disabled).toBeDisabled();
            await expect(disabled).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');
        });
    }
});

for (const theme of ['light', 'dark']) {
    test(`file and recycle menus keep readable hover and keyboard focus in ${theme} mode`, async ({
        page,
    }, testInfo) => {
        const url = process.env.FILE_ACTIONS_DEMO_URL;
        test.skip(!url, 'Requires an isolated Panel and Agent demo.');
        test.setTimeout(60000);
        page.setDefaultTimeout(8000);
        await page.setViewportSize({ width: 1280, height: 900 });
        await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const id = servers.data[0].attributes.identifier;
        const filesUrl = `${url}/server/${id}/files`;
        const api = `${url}/api/client/servers/${id}/files`;
        const csrf = decodeURIComponent(
            (await page.context().cookies()).find((cookie) => cookie.name === 'XSRF-TOKEN')!.value,
        );
        const headers = { 'X-XSRF-TOKEN': csrf, Accept: 'application/json' };
        const filename = `000-menu-contrast-${theme}.txt`;
        const recycled = await (await page.request.get(`${api}/recycle-bin`)).json();
        for (const entry of recycled.data) {
            if (entry.original_path === `/${filename}`) {
                expect(
                    (await page.request.post(`${api}/recycle-bin/delete`, { headers, data: { id: entry.id } })).ok(),
                ).toBe(true);
            }
        }
        expect(
            (await page.request.post(`${api}/write?file=/${filename}`, { headers, data: 'contrast fixture' })).ok(),
        ).toBe(true);
        await page.goto(filesUrl);
        const toggle = page.getByRole('button', { name: `Action: ${filename}`, exact: true });
        const check = async (row: Locator, danger = false) => {
            await row.hover();
            await expect
                .poll(async () => (await menuColors(row)).background)
                .toEqual((await menuColors(row)).expectedBackground);
            const hovered = await menuColors(row);
            expect(hovered.contrast).toBeGreaterThanOrEqual(4.5);
            if (danger) expect(hovered.foreground).toEqual(hovered.expectedForeground);
            await page.mouse.move(0, 0);
            await row.focus();
            await page.keyboard.press('Shift');
            await expect(row).toBeFocused();
            await expect.poll(async () => (await menuColors(row)).background).toEqual(hovered.background);
            const focused = await menuColors(row);
            expect(focused.foreground).toEqual(hovered.foreground);
            expect(focused.outline).toBe('solid');
            expect(focused.outlineWidth).toBe('2px');
            expect(focused.contrast).toBeGreaterThanOrEqual(4.5);
        };
        await toggle.evaluate((element) => element.scrollIntoView({ block: 'start' }));
        await toggle.click();
        for (const name of [
            'Rename',
            'Move',
            'Permissions',
            'Copy',
            'Archive',
            'Archive as tar.gz',
            'Archive as zip',
            'Download',
        ]) {
            await check(page.getByRole('button', { name, exact: true }));
        }
        const remove = page.getByRole('button', { name: 'Delete', exact: true });
        await check(remove, true);
        await page.screenshot({ path: testInfo.outputPath(`${theme}-file-menu.png`) });
        await remove.press('Enter');
        const dialog = page.getByRole('dialog');
        await dialog.getByRole('button', { name: 'Delete', exact: true }).click();
        await expect(dialog).toHaveCount(0);
        await page.goto(`${filesUrl}#/.recycle-bin`);
        await page.getByRole('button', { name: `Action: ${filename}`, exact: true }).click();
        await check(page.getByRole('button', { name: 'Download', exact: true }));
        await check(page.getByRole('button', { name: 'Restore', exact: true }));
        await check(page.getByRole('button', { name: 'Delete', exact: true }), true);
        await page.screenshot({ path: testInfo.outputPath(`${theme}-recycle-menu.png`) });
        await page.keyboard.press('Escape');
        await page.goto(filesUrl);
        await page.getByRole('button', { name: 'Recycle Bin', exact: true }).last().click();
        await check(page.getByRole('button', { name: 'Empty', exact: true }), true);
    });
}

test('right-click replaces file menus instead of accumulating them', async ({ page }) => {
    const url = process.env.FILE_ACTIONS_DEMO_URL;
    test.skip(!url, 'Requires an isolated Panel and Agent demo.');
    test.setTimeout(60000);
    page.setDefaultTimeout(8000);
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto(`${url}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.getByRole('button', { name: 'Login', exact: true }).click();
    await page.waitForURL(`${url}/`);
    const servers = await (await page.request.get(`${url}/api/client`)).json();
    const server = servers.data.find(
        (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
    );
    await page.goto(`${url}/server/${server.attributes.identifier}/files`);
    const expanded = page.locator('button[aria-label^="Action:"][aria-expanded=true]');
    const files = ['demo-check.txt', 'sftp-check.txt'];
    const rightClick = async (filename: string, fraction: number) => {
        const link = page.getByRole('link', { name: filename, exact: false });
        const bounds = (await link.boundingBox())!;
        await link.click({ button: 'right', position: { x: bounds.width * fraction, y: 12 } });
    };
    for (let index = 0; index < 20; index++) {
        const filename = files[index % files.length];
        await rightClick(filename!, index % 2 === 0 ? 0.25 : 0.75);
        await expect(expanded).toHaveCount(1);
        await expect(expanded).toHaveAccessibleName(`Action: ${filename}`);
        await expect(page.getByRole('button', { name: 'Rename', exact: true })).toHaveCount(1);
    }
    await expect(page.locator('button').filter({ hasText: /^Rename$/ })).toHaveCount(1);

    const toggle = page.getByRole('button', { name: 'Action: sftp-check.txt', exact: true });
    const menu = page.locator(`[id="${await toggle.getAttribute('aria-controls')}"]`);
    const previousBox = await menu.boundingBox();
    await rightClick('sftp-check.txt', 0.95);
    await expect(expanded).toHaveCount(1);
    expect((await menu.boundingBox())!.x).toBeGreaterThan(previousBox!.x);
    await page.keyboard.press('Escape');
    await expect(expanded).toHaveCount(0);
    await expect(toggle).toBeFocused();

    await toggle.click();
    await expect(expanded).toHaveCount(1);
    await rightClick('demo-check.txt', 0.75);
    await expect(expanded).toHaveCount(1);
    await expect(expanded).toHaveAccessibleName('Action: demo-check.txt');
    await page.getByText('File Manager', { exact: true }).click({ button: 'right' });
    await expect(expanded).toHaveCount(0);
});

for (const key of ['Enter', 'Space']) {
    test(`opens file actions, Rename and Download with ${key}`, async ({ page }) => {
        const url = process.env.FILE_ACTIONS_DEMO_URL;
        test.skip(!url, 'Requires an isolated Panel and Agent demo.');
        await page.setViewportSize({ width: key === 'Enter' ? 1280 : 390, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        const base = `${url}/api/client/servers/${server.attributes.identifier}/files`;
        const filename = key === 'Enter' ? 'demo-check.txt' : 'sftp-check.txt';
        const original = await (await page.request.get(`${base}/contents?file=/${filename}`)).body();
        await page.goto(`${url}/server/${server.attributes.identifier}/files`);
        const file = page.getByRole('link', { name: filename, exact: false });
        await file.focus();
        await page.keyboard.press('Tab');
        const toggle = page.getByRole('button', { name: `Action: ${filename}`, exact: true });
        await expect(toggle).toBeFocused();
        await page.keyboard.press(key);
        await expect(toggle).toHaveAttribute('aria-expanded', 'true');
        const menu = page.locator(`[id="${await toggle.getAttribute('aria-controls')}"]`);
        const toggleBox = await toggle.boundingBox();
        const menuBox = await menu.boundingBox();
        const expectedRight = Math.min(
            Math.max(toggleBox!.x + toggleBox!.width, menuBox!.width + 8),
            page.viewportSize()!.width - 8,
        );
        expect(Math.abs(menuBox!.x + menuBox!.width - expectedRight)).toBeLessThan(3);

        for (const label of [
            'Rename',
            'Move',
            'Permissions',
            'Copy',
            'Archive',
            'Archive as tar.gz',
            'Archive as zip',
            'Download',
            'Delete',
        ]) {
            await page.keyboard.press('Tab');
            await expect(menu.getByRole('button', { name: label, exact: true })).toBeFocused();
        }
        await page.keyboard.press('Escape');
        await expect(toggle).toBeFocused();
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
        await page.keyboard.press(key);
        await page.keyboard.press('Tab');
        await expect(menu.getByRole('button', { name: 'Rename', exact: true })).toBeFocused();
        await page.keyboard.press(key);
        const name = page.getByRole('textbox', { name: 'File Name', exact: true });
        await expect(name).toBeFocused();
        await expect(name).toHaveValue(filename);
        await page.keyboard.press('Escape');
        await expect(name).toHaveCount(0);
        await expect(toggle).toBeFocused();
        await page.keyboard.press(key);
        for (let index = 0; index < 8; index++) await page.keyboard.press('Tab');
        await expect(menu.getByRole('button', { name: 'Download', exact: true })).toBeFocused();
        const downloaded = page.waitForEvent('download');
        await page.keyboard.press(key);
        const download = await downloaded;
        expect(download.suggestedFilename()).toBe(filename);
        const stream = await download.createReadStream();
        const chunks: Buffer[] = [];
        for await (const chunk of stream!) chunks.push(Buffer.from(chunk));
        expect(Buffer.concat(chunks)).toEqual(original);
        await expect(toggle).toBeFocused();
        await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    });
}
