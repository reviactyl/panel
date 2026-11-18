import { execFileSync } from 'node:child_process';
import { resolve } from 'node:path';
import { expect, test } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';

let script: string;
let css: string;
let tokens: string;

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
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        plugins: [tailwindcss()],
        define: { 'process.env.NODE_ENV': JSON.stringify('production') },
        resolve: { alias: { '@': resolve('resources/scripts') } },
        build: {
            write: false,
            lib: { entry: resolve('tests/Browser/fixtures/console-stats/main.tsx'), name: 'Stats', formats: ['iife'] },
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
    for (const width of [320, 390, 768, 1280]) {
        test(`console stat values stay inside cards in ${theme} at ${width}px`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.setContent(`<html class='${theme}'><head><style>${tokens}\n${css}</style></head>
                <body><div id='root' style='margin:16px'></div><div id='modal-portal'></div></body></html>`);
            await page.addScriptTag({ content: script });
            for (const title of ['Uptime', 'Address']) {
                const card = page.getByText(title, { exact: true }).locator('../..');
                await expect(card).toBeVisible();
                const fits = await card.evaluate((card) => {
                    const bounds = card.getBoundingClientRect();
                    const range = document.createRange();
                    range.selectNodeContents(card.querySelector('bdi')!);
                    return [...range.getClientRects()].every(
                        (line) =>
                            line.left >= bounds.left && line.right <= bounds.right && line.bottom <= bounds.bottom,
                    );
                });
                expect.soft(fits, `${title} must fit without hiding any value`).toBe(true);
            }
            await expect(page.getByText('123456d 23h 59m', { exact: true })).toBeVisible();

            if (width >= 640) {
                const icon = page.getByText('Offline uptime', { exact: true }).locator('../..').locator('svg');
                const contrast = await icon.evaluate((icon) => {
                    const luminance = (color: string) =>
                        color
                            .match(/[\d.]+/g)!
                            .slice(0, 3)
                            .map(Number)
                            .map((channel) => channel / 255)
                            .map((value) => (value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4))
                            .reduce((sum, value, index) => sum + value * [0.2126, 0.7152, 0.0722][index]!, 0);
                    const values = [
                        luminance(getComputedStyle(icon).color),
                        luminance(getComputedStyle(icon.parentElement!).backgroundColor),
                    ];
                    return (Math.max(...values) + 0.05) / (Math.min(...values) + 0.05);
                });
                expect(contrast).toBeGreaterThanOrEqual(3);
            }

            const address = page.getByRole('button');
            let copies = 0;
            for (const key of ['Enter', 'Space']) {
                await address.focus();
                await page.keyboard.press(key);
                await expect(page.getByText(/^Copied "very-long-hostname/)).toHaveCount(++copies);
            }
            await address.click();
            await expect(page.getByText(/^Copied "very-long-hostname/)).toHaveCount(++copies);
        });
    }
}
