import { expect, test, type Locator } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';

const reachable = async (button: Locator) => {
    await expect(button).toBeVisible();
    await expect
        .poll(() =>
            button.evaluate((element) => {
                const rect = element.getBoundingClientRect();
                const hit = document.elementFromPoint(rect.x + rect.width / 2, rect.y + rect.height / 2);
                return hit === element || element.contains(hit);
            }),
        )
        .toBe(true);
};

// Exercise actual browser geometry: jsdom cannot detect stacking or fixed navigation collisions.
test.describe('archive menu responsive bounds', () => {
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
                    entry: resolve('tests/Browser/fixtures/archive-menu/main.tsx'),
                    name: 'ArchiveFixture',
                    formats: ['iife'],
                },
            },
        });
        const outputs = (Array.isArray(result) ? result : [result]).flatMap((output) =>
            'output' in output ? output.output : [],
        );
        const chunk = outputs.find((output) => output.type === 'chunk');
        const stylesheet = outputs.find((output) => output.type === 'asset' && output.fileName.endsWith('.css'));
        if (!chunk || stylesheet?.type !== 'asset') throw new Error('Missing archive fixture assets');
        script = chunk.code;
        css = stylesheet.source.toString();
    });
    for (const width of [320, 390, 639, 640, 768, 1280]) {
        test(`stays clear of actions, rows and navigation across scroll/resize at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 600 });
            await page.setContent('<div id="root"></div>');
            await page.addStyleTag({ content: css });
            await page.addScriptTag({ content: script });
            const archive = page.getByRole('button', { name: 'Archive', exact: true });
            await archive.evaluate((element) => element.scrollIntoView({ block: 'center' }));
            await archive.focus();
            const menu = page.getByRole('group', { name: 'Formats' });
            const check = async () => {
                await expect
                    .poll(async () => {
                        const bounds = (await menu.boundingBox())!;
                        return bounds.x >= 8 && bounds.x + bounds.width <= page.viewportSize()!.width - 8;
                    })
                    .toBe(true);
                const bounds = (await menu.boundingBox())!;
                expect(bounds.x).toBeGreaterThanOrEqual(8);
                expect(bounds.x + bounds.width).toBeLessThanOrEqual(page.viewportSize()!.width - 8);
                const remove = (await page.getByRole('button', { name: 'Delete', exact: true }).boundingBox())!;
                expect(bounds.y >= remove.y + remove.height || bounds.y + bounds.height <= remove.y).toBe(true);
                await reachable(page.getByRole('button', { name: 'tar.gz', exact: true }));
                await reachable(page.getByRole('button', { name: 'zip', exact: true }));
            };
            await check();
            await page.evaluate(() => window.scrollBy(0, -160));
            await check();
            await page.setViewportSize({ width: Math.max(320, width - 60), height: 520 });
            await check();
        });
    }
});

for (const [width, view] of [
    [1280, 'list'],
    [390, 'list'],
    [1280, 'grid'],
    [390, 'grid'],
] as const) {
    test(`Pi bulk and row archive actions stay reachable in ${view} at ${width}px`, async ({ page }, testInfo) => {
        const url = process.env.FILE_ACTIONS_DEMO_URL;
        test.skip(!url, 'Requires an isolated Panel and Agent demo.');
        test.setTimeout(90000);
        await page.addInitScript((view) => {
            let configuration: Record<string, unknown>;
            Object.defineProperty(window, 'PanelConfiguration', {
                get: () => configuration,
                set: (value: Record<string, unknown>) => {
                    configuration = { ...value, fileLayout: view };
                },
            });
            localStorage.setItem('theme', view === 'grid' ? 'light' : 'dark');
        }, view);
        await page.setViewportSize({ width, height: 600 });
        await page.goto(`${url}/auth/login`);
        await page.locator('[name=username]').fill('demo');
        await page.locator('[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const id = servers.data[0].attributes.identifier;
        const filesUrl = `${url}/server/${id}/files`;
        const api = `${url}/api/client/servers/${servers.data[0].attributes.uuid}/files`;
        await page.goto(filesUrl);
        const archive = page.getByRole('button', { name: 'Archive', exact: true });
        const formats = page.getByRole('button', { name: 'Archive as tar.gz', exact: true });
        await page.locator('input[name=selectedFiles]').first().check();
        await archive.evaluate((element) => element.scrollIntoView({ block: 'center' }));
        await archive.hover();
        await reachable(formats);
        await reachable(page.getByRole('button', { name: 'Delete', exact: true }));
        const panel = (await formats.locator('..').boundingBox())!;
        const trigger = (await archive.boundingBox())!;
        expect(panel.y >= trigger.y + trigger.height || panel.y + panel.height <= trigger.y).toBe(true);
        await page.mouse.move(0, 0);
        await archive.focus();
        await page.keyboard.press('Tab');
        await expect(formats).toBeFocused();
        await page.keyboard.press('Tab');
        await expect(page.getByRole('button', { name: 'Archive as zip', exact: true })).toBeFocused();
        await page.keyboard.press('Escape');
        await expect(archive).toBeFocused();
        await expect(formats).toBeHidden();
        await page.getByRole('button', { name: 'Delete', exact: true }).click();
        await expect(page.getByRole('dialog').getByRole('button', { name: 'Cancel', exact: true })).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(page.getByRole('dialog')).toHaveCount(0);
        await page.getByRole('button', { name: 'Move', exact: true }).click();
        await expect(page.getByRole('dialog').getByRole('button', { name: 'Cancel', exact: true })).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(page.getByRole('dialog')).toHaveCount(0);

        for (const [format, key] of [
            ['tar.gz', 'Enter'],
            ['zip', 'Space'],
        ] as const) {
            if (!(await page.locator('input[name=selectedFiles]:checked').count()))
                await page.locator('input[name=selectedFiles]').first().check();
            await archive.focus();
            await page.keyboard.press('Tab');
            if (format === 'zip') await page.keyboard.press('Tab');
            const choice = page.getByRole('button', { name: `Archive as ${format}`, exact: true });
            await expect(choice).toBeFocused();
            const response = page.waitForResponse(
                (response) => response.url() === `${api}/compress` && response.request().method() === 'POST',
            );
            await choice.press(key);
            const result = await response;
            expect(result.ok()).toBe(true);
            expect(result.request().postDataJSON().format).toBe(format);
            const payload = await result.json();
            expect(payload.attributes.name.endsWith(`.${format}`)).toBe(true);
            const download = await (await page.request.get(`${api}/download?file=/${payload.attributes.name}`)).json();
            const bytes = await (await page.request.get(download.attributes.url)).body();
            expect([...bytes.subarray(0, 2)]).toEqual(format === 'zip' ? [80, 75] : [31, 139]);
            await expect(page.locator('input[name=selectedFiles]:checked')).toHaveCount(0);
        }
        await page.getByRole('button', { name: 'Action: welcome.txt', exact: true }).click();
        await archive.focus();
        await reachable(formats);
        await reachable(page.getByRole('button', { name: 'Archive as zip', exact: true }));
        await page.screenshot({ path: testInfo.outputPath(`archive-${width}.png`) });
        const result = page.waitForResponse(
            (response) => response.url() === `${api}/compress` && response.request().method() === 'POST',
        );
        await page.getByRole('button', { name: 'Archive as zip', exact: true }).click();
        expect((await result).ok()).toBe(true);
    });
}
