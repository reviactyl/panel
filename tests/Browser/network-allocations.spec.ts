import { execFileSync } from 'node:child_process';
import { resolve } from 'node:path';
import { expect, test } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';

let script: string;
let css: string;
let tokens: string;
const alias = 'long-unbroken-alias-'.repeat(15) + '.example.test';

test.beforeAll(async () => {
    const html = execFileSync('php', ['tests/Fixtures/auth-page.php', '/auth/login'], {
        env: {
            ...process.env,
            APP_ENV: 'testing',
            APP_ENVIRONMENT_ONLY: 'false',
            APP_KEY: 'SomeRandomString3232RandomString',
            CACHE_STORE: 'array',
            SESSION_DRIVER: 'array',
            REDIS_CLIENT: 'predis',
        },
        encoding: 'utf8',
    });
    tokens = [...html.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map((match) => match[1]).join('\n');
    const mocks = resolve('tests/Browser/fixtures/network-allocations/mocks.ts');
    const replacements: Record<string, string> = {
        '@/state/server': 'ServerContext',
        '@/plugins/usePermissions': 'usePermissions',
        '@/plugins/useFlash': 'useFlash',
        '@/api/swr/getServerAllocations': 'getServerAllocations',
        '@/api/server/network/setServerAllocationNotes': 'setServerAllocationNotes',
        '@/api/server/network/setPrimaryServerAllocation': 'setPrimaryServerAllocation',
        '@/api/server/network/deleteServerAllocation': 'deleteServerAllocation',
    };
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        plugins: [
            {
                name: 'network-fixture-api',
                enforce: 'pre',
                resolveId: (id) => {
                    const source = id.replace(resolve('resources/scripts'), '@');
                    return source in replacements ? `\0mock:${source}` : undefined;
                },
                load: (id) => {
                    if (!id.startsWith('\0mock:')) return;
                    const name = replacements[id.slice(6)];
                    return `export { ${name} as default, ServerContext, usePermissions, useFlashKey } from ${JSON.stringify(mocks)};`;
                },
            },
            tailwindcss(),
        ],
        define: { 'process.env.NODE_ENV': JSON.stringify('production') },
        resolve: { alias: { '@': resolve('resources/scripts') } },
        build: {
            write: false,
            lib: {
                entry: resolve('tests/Browser/fixtures/network-allocations/main.tsx'),
                name: 'Network',
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

for (const theme of ['light', 'dark']) {
    for (const width of [320, 390, 768, 1024, 1280]) {
        test(`long aliases keep ports, notes and actions usable in ${theme} at ${width}px`, async ({ page }) => {
            page.on('pageerror', (error) => {
                throw error;
            });
            await page.setViewportSize({ width, height: 900 });
            await page.setContent(`<html class='${theme}'><head><style>${tokens}\n${css}</style></head>
                <body><div id='root' style='margin:16px'></div><div id='modal-portal'></div></body></html>`);
            await page.addScriptTag({ content: script });
            const notes = page.getByRole('textbox', { name: 'Notes' });
            await expect(notes).toHaveValue('Saved notes');
            const bounds = await notes.boundingBox();
            expect(bounds!.width).toBeGreaterThanOrEqual(150);
            const port = page.getByText('65535', { exact: true });
            await expect(port).toBeVisible();
            expect(
                await port.evaluate((port) => {
                    const range = document.createRange();
                    range.selectNodeContents(port);
                    const row = document.querySelector('#root > div')!.getBoundingClientRect();
                    return [...range.getClientRects()].every(
                        (line) => line.left >= row.left && line.right <= row.right,
                    );
                }),
            ).toBe(true);
            expect(await page.locator('#root > div').evaluate((row) => row.scrollWidth <= row.clientWidth)).toBe(true);
            const copy = page.getByRole('button', { name: alias, exact: true });
            for (const key of ['Enter', 'Space']) {
                await copy.focus();
                await page.keyboard.press(key);
                await expect(page.getByText(`Copied "${alias}" to clipboard.`, { exact: true }).first()).toBeVisible();
            }
            const remove = page.getByRole('button', { name: 'Remove Allocation', exact: true });
            await remove.focus();
            await page.keyboard.press('Enter');
            await expect(page.getByRole('heading', { name: 'Remove Allocation', exact: true })).toBeVisible();
            await page.keyboard.press('Escape');
            await expect(page.getByRole('dialog')).toHaveCount(0);
            await expect(remove).toBeFocused();
            await expect(notes).toHaveValue('Saved notes');
            await expect(page.getByRole('button', { name: 'Make Primary' })).toBeVisible();
        });
    }
}
