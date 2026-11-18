import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Locator } from '@playwright/test';

test.use({ locale: 'en' });

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

const expectCentered = async (input: Locator, button: Locator) => {
    const [field, control] = await button.evaluate(
        (button, input) => [input!.getBoundingClientRect().toJSON(), button.getBoundingClientRect().toJSON()],
        await input.elementHandle(),
    );
    expect(Math.abs(field.y + field.height / 2 - control.y - control.height / 2)).toBeLessThan(1);
    expect(control.x).toBeGreaterThanOrEqual(field.x);
    expect(control.x + control.width).toBeLessThanOrEqual(field.x + field.width);
    expect(control.width).toBeGreaterThanOrEqual(40);
    expect(control.height).toBeGreaterThanOrEqual(40);
};

for (const width of [1280, 390]) {
    for (const theme of ['light', 'dark']) {
        test(`Login and Register password controls support pointer and keyboard at ${width}px in ${theme}`, async ({
            page,
        }, testInfo) => {
            const demoURL = process.env.AUTH_PASSWORD_DEMO_URL;
            const baseURL = demoURL || 'http://panel.test';
            await page.setViewportSize({ width, height: 900 });
            await page.addInitScript((theme) => localStorage.setItem('theme', theme), theme);
            if (!demoURL) {
                const publicPath = resolve('public');
                await page.route('**/*', async (route) => {
                    const url = new URL(route.request().url());
                    if (url.origin !== baseURL) return route.abort();
                    if (['/auth/login', '/auth/register', '/locales/locale.json'].includes(url.pathname)) {
                        return route.fulfill({
                            contentType: url.pathname.startsWith('/auth/') ? 'text/html' : 'application/json',
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
            }
            const submissions: unknown[] = [];
            for (const path of ['/auth/login', '/auth/register']) {
                await page.route(`**${path}`, async (route) => {
                    if (route.request().method() !== 'POST') return route.fallback();
                    submissions.push(route.request().postDataJSON());
                    return route.fulfill({ json: { data: { complete: false } } });
                });
            }
            let hiddenIcon: string | null = null;
            let visibleIcon: string | null = null;
            for (const route of ['login', 'register']) {
                await page.goto(`${baseURL}/auth/${route}`);
                const password = page.locator('input[name=password]');
                const inputs = route === 'login' ? [password] : [password, page.getByLabel('Confirm Password')];
                const show = page.getByRole('button', { name: 'Show password', exact: true });
                const hide = page.getByRole('button', { name: 'Hide password', exact: true });
                await expect(show).toBeVisible();
                await expect(show).toHaveAttribute('title', 'Show password');
                await expect(page.locator('html')).toHaveClass(new RegExp(theme));
                await expectCentered(password, show);
                const icon = await show.locator('svg').innerHTML();
                if (hiddenIcon === null) hiddenIcon = icon;
                else expect(icon).toBe(hiddenIcon);
                for (const input of inputs) {
                    await input.fill('ValidPassword123!');
                    await expect(input).toHaveAttribute('type', 'password');
                }
                const ids = await Promise.all(inputs.map((input) => input.getAttribute('id')));
                await password.focus();
                await page.keyboard.press('Tab');
                await expect(show).toBeFocused();
                await expect(show).toHaveCSS('outline-style', 'solid');
                await page.keyboard.press('Enter');
                await expect(hide).toBeFocused();
                await expect(hide).toHaveAttribute('title', 'Hide password');
                for (const input of inputs) await expect(input).toHaveAttribute('type', 'text');
                const revealedIcon = await hide.locator('svg').innerHTML();
                expect(revealedIcon).not.toBe(icon);
                if (visibleIcon === null) visibleIcon = revealedIcon;
                else expect(revealedIcon).toBe(visibleIcon);
                await page.keyboard.press('Space');
                await expect(show).toBeFocused();
                for (const input of inputs) await expect(input).toHaveAttribute('type', 'password');
                await show.click();
                for (const [index, input] of inputs.entries()) {
                    await expect(input).toHaveAttribute('type', 'text');
                    await expect(input).toHaveValue('ValidPassword123!');
                    await expect(input).toHaveAttribute('id', ids[index]!);
                }
                await hide.click();
                expect(submissions).toHaveLength(0);
                await password.fill('');
                await page.locator('button[type=submit]').click();
                await expect(password).toHaveAttribute('data-has-error', 'true');
                await expectCentered(password, show);
                await show.click();
                await expect(password).toHaveAttribute('type', 'text');
                await expectCentered(password, hide);
                expect(submissions).toHaveLength(0);
                await page.screenshot({ path: testInfo.outputPath(`${route}-${theme}-${width}.png`), fullPage: true });
            }
        });
    }
}
