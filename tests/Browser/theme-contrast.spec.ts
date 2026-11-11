import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Locator } from '@playwright/test';

const fixtures = new Map<string, string>();
const renderFixture = (path: string) => {
    if (!fixtures.has(path)) {
        fixtures.set(
            path,
            execFileSync('php', ['tests/Fixtures/auth-page.php', path], {
                env: {
                    ...process.env,
                    APP_ENV: 'testing',
                    APP_ENVIRONMENT_ONLY: 'false',
                    APP_URL: 'http://panel.test',
                    APP_KEY: 'SomeRandomString3232RandomString',
                    CACHE_STORE: 'array',
                    SESSION_DRIVER: 'array',
                    REDIS_CLIENT: 'predis',
                },
                encoding: 'utf8',
                maxBuffer: 10 * 1024 * 1024,
            }),
        );
    }
    return fixtures.get(path)!;
};

const contrast = (locator: Locator, placeholder = false) =>
    locator.evaluate((element, placeholder) => {
        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = 1;
        const context = canvas.getContext('2d', { willReadFrequently: true })!;
        const rgba = (color: string) => {
            context.clearRect(0, 0, 1, 1);
            context.fillStyle = color;
            context.fillRect(0, 0, 1, 1);
            return [...context.getImageData(0, 0, 1, 1).data].map((value, index) =>
                index === 3 ? value / 255 : value,
            );
        };
        const blend = (foreground: number[], background: number[]) =>
            [0, 1, 2]
                .map((index) => foreground[index]! * foreground[3]! + background[index]! * (1 - foreground[3]!))
                .concat(1);
        const luminance = (color: number[]) =>
            color
                .slice(0, 3)
                .map((value) => value / 255)
                .map((value) => (value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4))
                .reduce((sum, value, index) => sum + value * [0.2126, 0.7152, 0.0722][index]!, 0);
        const ancestors: Element[] = [];
        for (let node: Element | null = element; node; node = node.parentElement) ancestors.unshift(node);
        const background = ancestors.reduce(
            (background, node) => blend(rgba(getComputedStyle(node).backgroundColor), background),
            [255, 255, 255, 1],
        );
        const foreground = blend(
            rgba(getComputedStyle(element, placeholder ? '::placeholder' : null).color),
            background,
        );
        const values = [luminance(foreground), luminance(background)];
        return (Math.max(...values) + 0.05) / (Math.min(...values) + 0.05);
    }, placeholder);

for (const theme of ['light', 'dark']) {
    test(`authentication keeps readable controls, validation and focus in ${theme} mode`, async ({ page }) => {
        await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
        const publicPath = resolve('public');
        await page.route('**/*', async (route) => {
            const url = new URL(route.request().url());
            if (url.origin !== 'http://panel.test') return route.abort();
            if (url.pathname === '/auth/login' || url.pathname === '/locales/locale.json') {
                return route.fulfill({
                    contentType: url.pathname === '/auth/login' ? 'text/html' : 'application/json',
                    body: renderFixture(url.pathname + url.search),
                });
            }
            const path = resolve(publicPath, `.${url.pathname}`);
            if (!path.startsWith(publicPath + sep)) return route.abort();
            try {
                await route.fulfill({ path });
            } catch {
                await route.abort();
            }
        });
        await page.goto('http://panel.test/auth/login');
        const submit = page.locator('button[type=submit]');
        await expect(submit).toBeVisible();
        await expect(page.locator('html')).toHaveClass(new RegExp(theme));
        for (const locator of [submit, page.getByRole('button', { name: 'Login with Passkey', exact: true })]) {
            await expect.poll(() => contrast(locator)).toBeGreaterThanOrEqual(4.5);
        }
        const username = page.locator('input[name=username]');
        await expect.poll(() => contrast(username, true)).toBeGreaterThanOrEqual(4.5);
        const recovery = page.getByRole('link', { name: 'Forgot Password?', exact: true });
        await expect.poll(() => contrast(recovery)).toBeGreaterThanOrEqual(4.5);
        await recovery.hover();
        await expect.poll(() => contrast(recovery)).toBeGreaterThanOrEqual(4.5);
        await username.focus();
        await page.keyboard.press('Tab');
        await expect(page.locator('input[name=password]')).toHaveCSS('outline-style', 'solid');
        await submit.click();
        const validation = page.getByText('A username or email must be provided.', { exact: true });
        await expect(validation).toBeVisible();
        await expect.poll(() => contrast(validation)).toBeGreaterThanOrEqual(4.5);

        for (const channels of ['255 230 30', '90 20 140', '10 160 120']) {
            await page.evaluate((channels) => {
                document.documentElement.style.setProperty('--color-primary', channels);
                window.dispatchEvent(new Event('designifychange'));
            }, channels);
            await expect.poll(() => contrast(submit)).toBeGreaterThanOrEqual(4.5);
        }
    });
}

for (const width of [1280, 390]) {
    test(`console selection stays readable and copyable across theme changes at ${width}px`, async ({ page }) => {
        const url = process.env.THEME_DEMO_URL;
        test.skip(!url, 'Requires this task’s isolated Panel and Agent demo.');
        await page.setViewportSize({ width, height: 900 });
        await page.addInitScript(() => localStorage.setItem('theme', 'light'));
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        await page.goto(`${url}/server/${server.attributes.identifier}`);
        const rows = page.locator('.xterm-rows');
        await expect(rows).toContainText('Demo server ready');
        await rows.scrollIntoViewIfNeeded();
        const firstLine = await rows.locator(':scope > div').nth(1).boundingBox();
        const lastLine = await rows.locator(':scope > div').nth(2).boundingBox();
        expect(firstLine).toBeTruthy();
        expect(lastLine).toBeTruthy();
        await page.evaluate(() => {
            document.addEventListener('copy', (event) => {
                (window as Window & { consoleCopied?: string }).consoleCopied =
                    event.clipboardData?.getData('text/plain');
            });
        });
        await page.mouse.move(firstLine!.x + 1, firstLine!.y + firstLine!.height / 2);
        await page.mouse.down();
        await page.mouse.move(lastLine!.x + 120, lastLine!.y + lastLine!.height / 2, { steps: 8 });
        await page.mouse.up();
        const selection = rows.locator('.xterm-decoration-top').first();
        await expect(selection).toBeVisible();
        const content = await rows.innerText();
        for (const theme of ['light', 'dark', 'light']) {
            await page.evaluate((theme) => {
                localStorage.setItem('theme', theme);
                window.dispatchEvent(new Event('themechange'));
            }, theme);
            await expect(page.locator('html')).toHaveClass(new RegExp(theme));
            await expect.poll(() => contrast(selection)).toBeGreaterThanOrEqual(4.5);
            const background = await selection.evaluate((element) => getComputedStyle(element).backgroundColor);
            const primary = await page.evaluate(() => {
                const channels = getComputedStyle(document.documentElement).getPropertyValue('--color-primary');
                return `rgb(${channels.trim().split(/\s+/).join(', ')})`;
            });
            expect(background).toBe(primary);
            expect(await rows.innerText()).toBe(content);
            await page.keyboard.press('Control+c');
            await expect
                .poll(() => page.evaluate(() => (window as Window & { consoleCopied?: string }).consoleCopied))
                .toContain('Demo server ready');
        }
    });
}
