import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Locator } from '@playwright/test';

const name = 'SearchRegression' + 'VeryLongServerLabel'.repeat(8);
const alias = 'very-long-allocation-alias-'.repeat(6) + '.example.test';
const node = 'LongNodeLabel'.repeat(6);
const fixtures = new Map<string, string>();
const renderFixture = (path: string) => {
    if (!fixtures.has(path)) {
        fixtures.set(
            path,
            execFileSync('php', ['tests/Fixtures/auth-page.php', path, 'admin'], {
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

const contrast = (locator: Locator, foreground = 'color') =>
    locator.evaluate((element, foreground) => {
        const style = getComputedStyle(element);
        const luminance = (color: string) =>
            color
                .match(/[\d.]+/g)!
                .slice(0, 3)
                .map(Number)
                .map((channel) => channel / 255)
                .map((channel) => (channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4))
                .reduce((sum, channel, index) => sum + channel * [0.2126, 0.7152, 0.0722][index]!, 0);
        let surface: Element = element;
        while (getComputedStyle(surface).backgroundColor === 'rgba(0, 0, 0, 0)' && surface.parentElement) {
            surface = surface.parentElement;
        }
        const values = [
            luminance(style.getPropertyValue(foreground)),
            luminance(getComputedStyle(surface).backgroundColor),
        ];
        return (Math.max(...values) + 0.05) / (Math.min(...values) + 0.05);
    }, foreground);

for (const theme of ['light', 'dark']) {
    for (const [width, platform] of [
        [1280, 'Linux'],
        [1280, 'MacIntel'],
        [390, 'Linux'],
    ] as const) {
        test(`search keeps long results contained and readable in ${theme} at ${width}px on ${platform}`, async ({
            page,
        }, testInfo) => {
            await page.setViewportSize({ width, height: 900 });
            await page.addInitScript(
                ({ theme, platform }) => {
                    localStorage.setItem('theme', theme);
                    Object.defineProperty(navigator, 'platform', { value: platform });
                },
                { theme, platform },
            );
            const demo = process.env.SEARCH_DEMO_URL;
            if (demo) {
                await page.goto(`${demo}/auth/login`);
                await page.locator('input[name=username]').fill('demo');
                await page.locator('input[name=password]').fill('demo');
                await page.locator('button[type=submit]').click();
                await page.waitForURL(`${demo}/`);
            } else {
                const publicPath = resolve('public');
                await page.route('**/*', async (route) => {
                    const url = new URL(route.request().url());
                    if (url.origin !== 'http://panel.test') return route.abort();
                    if (url.pathname === '/' || url.pathname === '/locales/locale.json') {
                        return route.fulfill({
                            contentType: url.pathname === '/' ? 'text/html' : 'application/json',
                            body: renderFixture(url.pathname + url.search),
                        });
                    }
                    if (url.pathname === '/api/client') {
                        return route.fulfill({
                            json: {
                                data: [
                                    {
                                        attributes: {
                                            identifier: 'abcdef12',
                                            uuid: 'search-server',
                                            name,
                                            node,
                                            sftp_details: { ip: '127.0.0.1', port: 2022 },
                                            relationships: {
                                                allocations: {
                                                    data: [
                                                        {
                                                            attributes: {
                                                                id: 1,
                                                                ip: '127.0.0.1',
                                                                ip_alias: alias,
                                                                port: 25565,
                                                                is_default: true,
                                                            },
                                                        },
                                                    ],
                                                },
                                            },
                                        },
                                    },
                                ],
                                meta: {
                                    pagination: { total: 1, count: 1, per_page: 50, current_page: 1, total_pages: 1 },
                                },
                            },
                        });
                    }
                    if (url.pathname.startsWith('/api/')) return route.fulfill({ json: { data: [], categories: [] } });
                    const path = resolve(publicPath, `.${url.pathname}`);
                    if (!path.startsWith(publicPath + sep)) return route.abort();
                    try {
                        await route.fulfill({ path });
                    } catch {
                        await route.abort();
                    }
                });
                await page.goto('http://panel.test/');
            }
            await expect(page.locator('html')).toHaveClass(new RegExp(theme));
            const trigger = page.getByRole('button', { name: 'Search', exact: true }).first();
            await expect(trigger).toBeVisible();
            if (width === 1280) {
                await expect(trigger.locator('kbd').first()).toHaveText(platform === 'MacIntel' ? '⌘' : 'Ctrl');
                for (const keycap of await trigger.locator('kbd').all()) {
                    await expect(keycap).toHaveCSS('border-top-width', '1px');
                    expect(await contrast(keycap)).toBeGreaterThanOrEqual(4.5);
                    expect(await contrast(keycap, 'border-top-color')).toBeGreaterThanOrEqual(3);
                    expect(await keycap.evaluate((el) => getComputedStyle(el).backgroundColor)).not.toBe(
                        await trigger.evaluate((el) => getComputedStyle(el).backgroundColor),
                    );
                }
            }
            await trigger.focus();
            await page.keyboard.press(platform === 'MacIntel' ? 'Meta+k' : 'Control+k');
            const dialog = page.getByRole('dialog');
            const input = dialog.locator('input[name=term]');
            await expect(input).toBeFocused();
            await input.fill('SearchRegression');
            const result = dialog.getByRole('link', { name: new RegExp('SearchRegression') });
            await expect(result).toBeVisible();
            const href = await result.getAttribute('href');
            expect(href).toMatch(/^\/server\//);
            for (const label of [
                result.getByText(name, { exact: true }),
                result.getByText(node, { exact: true }),
                result.locator('span').filter({ hasText: alias }),
            ]) {
                await expect(label).toHaveAttribute('title', (await label.textContent()) as string);
                await expect(label).toHaveCSS('text-overflow', 'ellipsis');
                expect(await label.evaluate((el) => el.scrollWidth > el.clientWidth)).toBe(true);
                expect(await contrast(label)).toBeGreaterThanOrEqual(4.5);
            }
            const layout = await result.evaluate((el) => {
                const bounds = el.getBoundingClientRect();
                const content = el.closest('[role=dialog]')!;
                return {
                    overflow: el.scrollWidth > el.clientWidth,
                    contained: bounds.left >= 0 && bounds.right <= innerWidth,
                    dialogOverflow: content.scrollWidth > content.clientWidth,
                };
            });
            expect(layout).toEqual({ overflow: false, contained: true, dialogOverflow: false });
            const badge = result.getByText(node, { exact: true });
            for (const channels of ['255 230 30', '90 20 140', '10 160 120']) {
                await page.evaluate((channels) => {
                    document.documentElement.style.setProperty('--color-primary', channels);
                    window.dispatchEvent(new Event('designifychange'));
                }, channels);
                await expect(badge).toHaveCSS('background-color', `rgb(${channels.split(' ').join(', ')})`);
                await expect.poll(() => contrast(badge)).toBeGreaterThanOrEqual(4.5);
                const accent = await page.evaluate(
                    () =>
                        `rgb(${getComputedStyle(document.documentElement).getPropertyValue('--text-primary').trim().split(/\s+/).join(', ')})`,
                );
                await result.hover();
                await expect(result).toHaveCSS('border-left-color', accent);
                await page.mouse.move(0, 0);
                await input.focus();
                await input.press('Tab');
                await page.keyboard.press('Tab');
                await expect(result).toBeFocused();
                await expect(result).toHaveCSS('border-left-color', accent);
                await expect(result).toHaveCSS('outline-style', 'solid');
            }
            await page.screenshot({ path: testInfo.outputPath('search-results.png') });
            await page.keyboard.press('Escape');
            await expect(dialog).toHaveCount(0);
            await expect(trigger).toBeFocused();
            await page.keyboard.press('Space');
            await expect(input).toBeFocused();
            await input.fill('SearchRegression');
            await expect(result).toBeVisible();
            await input.press('Tab');
            await page.keyboard.press('Tab');
            await expect(result).toBeFocused();
            await page.keyboard.press('Enter');
            await expect(page).toHaveURL(new RegExp(`${href}$`));
            await expect(dialog).toHaveCount(0);
        });
    }
}
