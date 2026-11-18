import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Locator, type Page } from '@playwright/test';

const fixtures = new Map<string, string>();
const renderFixture = (layout: string, path: string) => {
    const key = layout + path;
    if (!fixtures.has(key)) {
        fixtures.set(
            key,
            execFileSync('php', ['tests/Fixtures/auth-page.php', path, layout], {
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
    return fixtures.get(key)!;
};

const visibleFocus = async (link: Locator) => {
    await expect(link).toBeFocused();
    expect(await link.evaluate((element) => element.matches(':focus-visible'))).toBe(true);
    await expect(link).toHaveCSS('outline-style', 'solid');
    await expect(link).toHaveCSS('outline-width', '2px');
};
const openProfile = async (page: Page) => {
    const toggle = page.getByRole('button', { name: 'Avatar', exact: true });
    await toggle.focus();
    await page.keyboard.press('Enter');
    return toggle;
};

for (const layout of ['compact', 'accent']) {
    test(`${layout} navigation has one link per action and preserves keyboard and pointer destinations`, async ({
        page,
    }) => {
        const demo = process.env.NAVIGATION_LINKS_DEMO_URL;
        const base = demo || 'http://panel.test';
        if (!demo) {
            const publicPath = resolve('public');
            await page.context().route('**/*', async (route) => {
                const url = new URL(route.request().url());
                if (url.origin !== base) return route.abort();
                if (url.pathname === '/api/client/account/social-logins') {
                    return route.fulfill({ json: { data: [] } });
                }
                if (url.pathname.startsWith('/account') || url.pathname === '/locales/locale.json') {
                    return route.fulfill({
                        contentType: url.pathname.startsWith('/account') ? 'text/html' : 'application/json',
                        body: renderFixture(layout, url.pathname + url.search),
                    });
                }
                if (url.pathname === '/admin' || url.pathname.startsWith('/auth/login/')) {
                    return route.fulfill({ body: 'Destination reached' });
                }
                const path = resolve(publicPath, `.${url.pathname}`);
                if (!path.startsWith(publicPath + sep)) return route.abort();
                try {
                    await route.fulfill({ path });
                } catch {
                    await route.abort();
                }
            });
        } else {
            await page.context().route(
                (url) => ['accounts.google.com', 'github.com', 'discord.com'].includes(url.hostname),
                (route) => route.fulfill({ body: 'OAuth destination reached' }),
            );
            test.skip(process.env.NAVIGATION_LINKS_LAYOUT !== layout, 'Demo must use the selected layout.');
            await page.goto(`${base}/auth/login`);
            await page.locator('input[name=username]').fill('demo-admin');
            await page.locator('input[name=password]').fill('demo-admin');
            await page.locator('button[type=submit]').click();
            await page.waitForURL(`${base}/`);
        }
        await page.goto(`${base}/account`);
        await openProfile(page);
        const profile = page.getByRole('link', { name: 'Profile', exact: true });
        const admin = page.getByRole('link', { name: 'Admin', exact: true });
        await expect(profile).toHaveAttribute('href', '/account');
        await expect(admin).toHaveAttribute('href', '/admin');
        await expect(admin).toHaveAttribute('target', '_blank');
        await expect(admin).toHaveAttribute('rel', /noreferrer/);
        await expect(profile.locator('button, a, [tabindex]')).toHaveCount(0);
        await expect(admin.locator('button, a, [tabindex]')).toHaveCount(0);
        await page.keyboard.press('Tab');
        await visibleFocus(profile);
        await page.keyboard.press('Tab');
        await visibleFocus(admin);
        await page.keyboard.press('Tab');
        await expect(profile.locator('..').getByRole('button', { name: 'Logout', exact: true })).toBeFocused();

        await admin.focus();
        await page.keyboard.press('Space');
        await expect(page).toHaveURL(`${base}/account`);
        expect(page.context().pages()).toHaveLength(1);
        const keyboardPopup = page.waitForEvent('popup');
        await page.keyboard.press('Enter');
        const openedByKeyboard = await keyboardPopup;
        await openedByKeyboard.waitForURL(`${base}/admin`);
        await openedByKeyboard.close();
        await expect(page).toHaveURL(`${base}/account`);
        await openProfile(page);
        const pointerPopup = page.waitForEvent('popup');
        await admin.click();
        const openedByPointer = await pointerPopup;
        await openedByPointer.waitForURL(`${base}/admin`);
        await openedByPointer.close();

        await page.goto(`${base}/account/api`);
        await openProfile(page);
        await profile.focus();
        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(`${base}/account`);
        await expect(profile).toBeHidden();
        await page.goto(`${base}/account/api`);
        await openProfile(page);
        await profile.click();
        await expect(page).toHaveURL(`${base}/account`);
        await expect(profile).toBeHidden();

        for (const provider of ['google', 'discord', 'github']) {
            const link = page.locator(`a[href='/auth/login/${provider}']`);
            await expect(link).toHaveCount(1);
            await expect(link).toHaveAccessibleName('Link');
            await expect(link.locator('button, a, [tabindex]')).toHaveCount(0);
            await link.focus();
            await page.keyboard.press('Shift+Tab');
            await page.keyboard.press('Tab');
            await visibleFocus(link);
            await page.keyboard.press('Tab');
            await expect(link).not.toBeFocused();
            await link.focus();
            await page.keyboard.press('Space');
            await expect(page).toHaveURL(`${base}/account`);
            for (const activate of [() => page.keyboard.press('Enter'), () => link.click()]) {
                await link.focus();
                const destination = page.waitForRequest(`${base}/auth/login/${provider}`);
                const providerHost = { google: 'accounts.google.com', discord: 'discord.com', github: 'github.com' }[
                    provider
                ]!;
                const navigation = page.waitForURL(
                    demo ? (url) => url.hostname === providerHost : `${base}/auth/login/${provider}`,
                );
                await activate();
                expect((await destination).method()).toBe('GET');
                await navigation;
                await page.goto(`${base}/account`);
                await expect(link).toBeVisible();
            }
        }
    });
}
