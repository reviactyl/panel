import { resolve } from 'node:path';
import { expect, test, type Page } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';

const name = `s1_${'long_database_name_'.repeat(3).slice(0, 48)}`;
const endpoint = `${'long-endpoint.'.repeat(12)}example.com`;
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
                entry: resolve('tests/Browser/fixtures/database-layout/main.tsx'),
                name: 'Databases',
                formats: ['iife'],
            },
        },
    });
    const outputs = (Array.isArray(result) ? result : [result]).flatMap((result) =>
        'output' in result ? result.output : [],
    );
    script = outputs
        .filter((output) => output.type === 'chunk')
        .map((output) => output.code)
        .join('\n');
    css = outputs
        .flatMap((output) => (output.type === 'asset' && output.fileName.endsWith('.css') ? [output.source] : []))
        .join('\n');
});

const render = async (page: Page, width: number, restricted = false, running = false) => {
    await page.setViewportSize({ width, height: 900 });
    await page.route('http://panel.test/**', (route) => {
        if (route.request().url().includes('/api/client/servers/')) {
            return route.fulfill({
                json: {
                    data: [
                        {
                            attributes: {
                                id: 'database-id',
                                name,
                                username: 'u1_database_user',
                                host: { address: endpoint, port: 3306 },
                                connections_from: '%',
                                relationships: {},
                                import: running ? { state: 'running', statements: 0 } : null,
                            },
                        },
                    ],
                },
            });
        }
        return route.fulfill({
            contentType: 'text/html',
            body: `<html class="dark"><head><style>${css}</style></head><body><div id="root" style="padding:16px;max-width:1100px;margin:auto"></div><div id="modal-portal"></div></body></html>`,
        });
    });
    await page.goto(`http://panel.test/${restricted ? '?restricted' : ''}`);
    await page.addScriptTag({ content: script });
};

for (const width of [320, 390, 768, 1024, 1440]) {
    test(`long database identifiers preserve row actions and footer alignment at ${width}px`, async ({ page }) => {
        await render(page, width);
        const details = page.getByRole('button', { name: 'connection-title', exact: true });
        await expect(details).toBeVisible();
        const row = details.locator('../..');
        const nameControl = page.getByRole('button', { name, exact: true });
        await expect(nameControl).toBeVisible();
        const bounds = await row.boundingBox();
        expect(bounds).toBeTruthy();
        for (const label of ['connection-title', 'import-title', 'export-title', 'delete-database']) {
            const action = page.getByRole('button', { name: label, exact: true });
            await expect(action).toBeVisible();
            const box = (await action.boundingBox())!;
            expect(box.x).toBeGreaterThanOrEqual(bounds!.x);
            expect(box.x + box.width).toBeLessThanOrEqual(bounds!.x + bounds!.width + 1);
            expect(box.y + box.height).toBeLessThanOrEqual(bounds!.y + bounds!.height + 1);
            await action.click({ trial: true });
        }
        const identifiers =
            width >= 768
                ? [nameControl, page.getByRole('button', { name: `${endpoint}:3306`, exact: true })]
                : [nameControl];
        for (const identifier of identifiers) {
            const box = (await identifier.boundingBox())!;
            expect(box.x + box.width).toBeLessThanOrEqual(bounds!.x + bounds!.width + 1);
            expect(await identifier.evaluate((element) => element.scrollWidth > element.clientWidth)).toBe(true);
        }
        await details.focus();
        await page.keyboard.press('Enter');
        const dialog = page.getByRole('dialog');
        await expect(dialog.getByLabel('endpoint', { exact: true })).toHaveValue(`${endpoint}:3306`);
        await page.keyboard.press('Escape');
        await expect(dialog).not.toBeVisible();
        const create = page.getByRole('button', { name: 'New Database', exact: true });
        const count = page.getByText('1 of 5 databases', { exact: true });
        const buttonBox = (await create.boundingBox())!;
        const countBox = (await count.boundingBox())!;
        expect(Math.abs(buttonBox.y + buttonBox.height / 2 - countBox.y - countBox.height / 2)).toBeLessThan(1);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    });
}

for (const restricted of [false, true]) {
    test(`preserves database permissions and import guards (restricted=${restricted})`, async ({ page }) => {
        await render(page, 390, restricted, true);
        await expect(page.getByRole('button', { name: 'connection-title', exact: true })).toBeEnabled();
        for (const label of ['import-title', 'export-title', 'delete-database']) {
            const action = page.getByRole('button', { name: label, exact: true });
            if (restricted) await expect(action).toHaveCount(0);
            else await expect(action).toBeDisabled();
        }
        await expect(page.getByRole('button', { name: 'New Database', exact: true })).toHaveCount(restricted ? 0 : 1);
    });
}
