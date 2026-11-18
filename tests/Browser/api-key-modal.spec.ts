import { expect, test, type Page } from '@playwright/test';
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'node:path';
import { mkdir } from 'node:fs/promises';

const token = `fixture_${'A'.repeat(40)}`;
let javascript: string;
let css: string;

test.beforeAll(async () => {
    const fixture = resolve('tests/Browser/fixtures/api-key-modal');
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        publicDir: false,
        plugins: [tailwindcss()],
        define: { 'process.env.NODE_ENV': JSON.stringify('production'), 'process.env': '{}' },
        resolve: {
            alias: [
                {
                    find: 'react-i18next',
                    replacement: resolve('tests/Browser/fixtures/account-preferences/translation.ts'),
                },
                { find: '@/api/account/createApiKey', replacement: `${fixture}/create.ts` },
                { find: '@', replacement: resolve('resources/scripts') },
            ],
        },
        build: { write: false, lib: { entry: `${fixture}/main.tsx`, name: 'ApiKeyModal', formats: ['iife'] } },
    });
    const outputs = (Array.isArray(result) ? result : [result]).flatMap((output) =>
        'output' in output ? output.output : [],
    );
    const chunk = outputs.find((output) => output.type === 'chunk');
    const stylesheet = outputs.find((output) => output.type === 'asset' && output.fileName.endsWith('.css'));
    if (!chunk || chunk.type !== 'chunk' || !stylesheet || stylesheet.type !== 'asset') {
        throw new Error('Missing API key modal fixture bundle or stylesheet');
    }
    javascript = chunk.code;
    css = String(stylesheet.source);
});

const observeCopy = (page: Page) =>
    page.evaluate(() => {
        document.addEventListener(
            'copy',
            () => {
                document.body.dataset.copied = document.getSelection()?.toString() ?? '';
            },
            true,
        );
    });

const checkToken = async (page: Page) => {
    const code = page.locator('pre code');
    await expect(code).toHaveText(token);
    const bounds = await code.evaluate((node) => {
        const pre = node.parentElement!;
        const range = document.createRange();
        range.selectNodeContents(node);
        const surface = pre.getBoundingClientRect();
        return {
            left: surface.left,
            right: surface.right,
            scroll: pre.scrollWidth,
            client: pre.clientWidth,
            background: getComputedStyle(pre).backgroundColor,
            parentBackground: getComputedStyle(pre.parentElement!).backgroundColor,
            fragments: [...range.getClientRects()].map(({ left, right }) => ({ left, right })),
        };
    });
    expect(bounds.left).toBeGreaterThanOrEqual(0);
    expect(bounds.right).toBeLessThanOrEqual(page.viewportSize()!.width);
    expect(bounds.scroll).toBeLessThanOrEqual(bounds.client);
    expect(bounds.background).not.toBe(bounds.parentBackground);
    expect(bounds.background).not.toBe('rgba(0, 0, 0, 0)');
    for (const fragment of bounds.fragments) {
        expect(fragment.left).toBeGreaterThanOrEqual(bounds.left);
        expect(fragment.right).toBeLessThanOrEqual(bounds.right);
    }
    await code.click();
    await expect(page.locator('body')).toHaveAttribute('data-copied', token);
    for (const key of ['Enter', 'Space']) {
        await page.evaluate(() => delete document.body.dataset.copied);
        await code.focus();
        await expect(code).toBeFocused();
        await code.press(key);
        await expect(page.locator('body')).toHaveAttribute('data-copied', token);
    }
    await code.press('Tab');
    await page.keyboard.press('Shift+Tab');
    await expect(code).toBeFocused();
    expect(await code.evaluate((node) => parseFloat(getComputedStyle(node).outlineWidth))).toBeGreaterThanOrEqual(2);
};

for (const width of [320, 390, 1280]) {
    for (const theme of ['light', 'dark']) {
        test(`contains and copies a newly created token at ${width}px in ${theme} mode`, async ({ page }) => {
            await page.setViewportSize({ width, height: 900 });
            await page.route('https://api-key.test/', (route) =>
                route.fulfill({
                    contentType: 'text/html',
                    body: '<div id="root"></div><div id="modal-portal"></div>',
                }),
            );
            await page.goto('https://api-key.test/');
            await page.addStyleTag({ content: css });
            await page.evaluate((theme) => {
                const colors =
                    theme === 'dark'
                        ? { 200: '228 228 231', 800: '39 39 42', 900: '24 24 27', 950: '9 9 11' }
                        : { 200: '39 39 42', 800: '228 228 231', 900: '244 244 245', 950: '250 250 250' };
                for (const [step, color] of Object.entries(colors)) {
                    document.documentElement.style.setProperty(`--color-${step}`, color);
                }
            }, theme);
            await page.addScriptTag({ content: javascript });
            await observeCopy(page);
            await page.getByLabel('api.create.description-label', { exact: true }).fill('Fixture API key');
            await page.getByRole('button', { name: 'api.create.button', exact: true }).click();
            const dialog = page.getByRole('dialog', { name: 'api.modal.your-api-key', exact: true });
            await expect(dialog).toBeVisible();
            await checkToken(page);
            await dialog.getByRole('button', { name: 'api.modal.close', exact: true }).click();
            await expect(dialog).toHaveCount(0);
            await expect(page.getByText('Key added to list')).toBeVisible();
            await expect(page.getByLabel('api.create.description-label', { exact: true })).toHaveValue('');
        });
    }
}

test('creates a key through the isolated Pi workflow and preserves full token copying', async ({ page }) => {
    const url = process.env.API_KEY_DEMO_URL;
    test.skip(!url, 'Requires this task’s isolated Pi demo.');
    await page.setViewportSize({ width: 320, height: 900 });
    await page.goto(`${url}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.getByRole('button', { name: 'Login', exact: true }).click();
    await page.waitForURL(`${url}/`);
    await page.goto(`${url}/account/api`);
    await page.route('**/api/client/account/api-keys', async (route) => {
        if (route.request().method() !== 'POST') return route.continue();
        const response = await route.fetch();
        expect(response.ok()).toBe(true);
        const body = await response.json();
        body.attributes.identifier = 'fixture_';
        body.meta.secret_token = 'A'.repeat(40);
        await route.fulfill({ response, json: body });
    });
    await observeCopy(page);
    await page.locator('input[name=description]').fill('REV-157 responsive fixture');
    await page.getByRole('button', { name: 'Create', exact: true }).click();
    await expect(page.getByRole('dialog')).toBeVisible();
    for (const width of [320, 390, 1280]) {
        await page.setViewportSize({ width, height: 900 });
        await checkToken(page);
        const evidence = process.env.API_KEY_EVIDENCE_DIR;
        if (evidence) {
            await mkdir(evidence, { recursive: true });
            await page.screenshot({ path: resolve(evidence, `pi-token-${width}.png`) });
        }
    }
    await page.getByRole('dialog').getByRole('button', { name: 'Close', exact: true }).first().click();
    await expect(page.getByRole('dialog')).toHaveCount(0);
    await expect(page.getByText('REV-157 responsive fixture', { exact: true })).toBeVisible();
});
