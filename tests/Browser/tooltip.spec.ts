import { expect, test } from '@playwright/test';
import { build } from 'vite';
import { resolve } from 'node:path';
import { readFileSync } from 'node:fs';

let script: string;
let css: string;
test.beforeAll(async () => {
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        define: { 'process.env.NODE_ENV': JSON.stringify('production') },
        resolve: { alias: { '@': resolve('resources/scripts') } },
        build: {
            write: false,
            lib: {
                entry: resolve('tests/Browser/fixtures/tooltip/main.tsx'),
                name: 'TooltipFixture',
                formats: ['iife'],
            },
        },
    });
    const outputs = Array.isArray(result) ? result : [result];
    const chunk = outputs
        .flatMap((output) => ('output' in output ? output.output : []))
        .find((output) => output.type === 'chunk');
    if (!chunk || chunk.type !== 'chunk') throw new Error('Missing tooltip fixture bundle');
    script = chunk.code;
    const manifest = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));
    css = manifest['resources/scripts/index.tsx'].css
        .map((path: string) => readFileSync(resolve('public/build', path), 'utf8'))
        .join('\n');
});

test.beforeEach(async ({ page }) => {
    await page.route('http://tooltip.test/**', (route) =>
        route.fulfill({
            contentType: 'text/html',
            body: '<style>:root { --color-900: 24 24 27; --color-950: 9 9 11; --color-800: 39 39 42; } .custom-tooltip { background-color: rgb(40, 70, 100) !important; }</style><div id="root"></div>',
        }),
    );
});

for (const placement of ['top', 'left', 'bottom', 'right', 'top-start', 'left-end']) {
    for (const edge of [false, true]) {
        test(`${placement}${edge ? ' at viewport edge' : ''} points toward its trigger`, async ({ page }) => {
            await page.goto(`http://tooltip.test/?placement=${placement}${edge ? '&edge' : ''}`);
            await page.addStyleTag({ content: css });
            await page.addScriptTag({ content: script });
            await page.getByRole('button').hover();
            const tooltip = page.getByRole('tooltip');
            await expect(tooltip).toBeVisible();
            await expect(tooltip).toHaveCSS('opacity', '1');
            const resolved =
                edge && ['top', 'left'].includes(placement.split('-')[0]!)
                    ? placement.startsWith('top')
                        ? 'bottom'
                        : 'right'
                    : placement.split('-')[0];
            await expect
                .poll(() =>
                    tooltip.evaluate((body, side) => {
                        const trigger = document.querySelector('button')!.getBoundingClientRect();
                        const rect = body.getBoundingClientRect();
                        const arrow = body.lastElementChild as HTMLElement;
                        const tip = arrow.getBoundingClientRect();
                        const expected =
                            side === 'top'
                                ? rect.bottom
                                : side === 'bottom'
                                  ? rect.top
                                  : side === 'left'
                                    ? rect.right
                                    : rect.left;
                        const actual =
                            side === 'top' || side === 'bottom'
                                ? (tip.top + tip.bottom) / 2
                                : (tip.left + tip.right) / 2;
                        const correctSide =
                            side === 'top'
                                ? rect.bottom < trigger.top
                                : side === 'bottom'
                                  ? rect.top > trigger.bottom
                                  : side === 'left'
                                    ? rect.right < trigger.left
                                    : rect.left > trigger.right;
                        return correctSide && Math.abs(expected - actual) < 2;
                    }, resolved),
                )
                .toBe(true);
            await expect
                .poll(() =>
                    tooltip.evaluate(
                        (body) =>
                            getComputedStyle(body.lastElementChild!).backgroundColor ===
                            getComputedStyle(body).backgroundColor,
                    ),
                )
                .toBe(true);
        });
    }
}

test('matches dark, light and customized tooltip surfaces', async ({ page }) => {
    for (const [query, color] of [
        ['', 'rgb(24, 24, 27)'],
        ['light', 'rgb(244, 244, 245)'],
        ['custom', 'rgb(40, 70, 100)'],
    ]) {
        await page.goto(`http://tooltip.test/?${query}`);
        await page.addStyleTag({ content: css });
        if (query === 'light') {
            await page.evaluate(() => document.documentElement.style.setProperty('--color-900', '244 244 245'));
        }
        await page.addScriptTag({ content: script });
        await page.getByRole('button').hover();
        const tooltip = page.getByRole('tooltip');
        await expect(tooltip).toBeVisible();
        await expect(tooltip).toHaveCSS('background-color', color!);
        await expect(tooltip.locator('div')).toHaveCSS('background-color', color!);
    }
});

test('moves the arrow to the opposite edge when an open tooltip flips', async ({ page }) => {
    await page.goto('http://tooltip.test/');
    await page.addStyleTag({ content: css });
    await page.addScriptTag({ content: script });
    await page.getByRole('button').waitFor();
    await page.keyboard.press('Tab');
    const tooltip = page.getByRole('tooltip');
    await expect(tooltip).toBeVisible();
    const arrow = tooltip.locator('div');
    await expect(arrow).toHaveCSS('bottom', '-6px');
    for (const top of ['2px', '50%']) {
        await page.getByRole('button').evaluate((button, top) => {
            button.parentElement!.style.top = top;
            window.dispatchEvent(new Event('resize'));
        }, top);
        await expect(arrow).toHaveCSS(top === '2px' ? 'top' : 'bottom', '-6px');
        await expect
            .poll(() =>
                arrow.evaluate(
                    (element, side) => element.style.getPropertyValue(side),
                    top === '2px' ? 'bottom' : 'top',
                ),
            )
            .toBe('');
    }
});

test('preserves focus, Escape, click handlers, no-arrow and disabled tooltips', async ({ page }) => {
    for (const query of ['no-arrow', 'click', 'disabled']) {
        await page.goto(`http://tooltip.test/?${query}`);
        await page.addStyleTag({ content: css });
        await page.addScriptTag({ content: script });
        const trigger = page.getByRole('button');
        await trigger.waitFor();
        await page.keyboard.press('Tab');
        await expect(trigger).toBeFocused();
        if (query === 'no-arrow') {
            await expect(page.getByRole('tooltip')).toBeVisible();
            await expect(page.getByRole('tooltip').locator('div')).toHaveCount(0);
            await page.keyboard.press('Escape');
            await expect(page.getByRole('tooltip')).toHaveCount(0);
        }
        await page.keyboard.press('Enter');
        await expect(trigger).toHaveText('Activated');
        if (query === 'click') {
            await expect(page.getByRole('tooltip')).toBeVisible();
            await page.keyboard.press('Escape');
            await expect(page.getByRole('tooltip')).toHaveCount(0);
            await trigger.click();
            await expect(page.getByRole('tooltip')).toBeVisible();
            await trigger.click();
        }
        if (query === 'disabled') await expect(page.getByRole('tooltip')).toHaveCount(0);
    }
});
