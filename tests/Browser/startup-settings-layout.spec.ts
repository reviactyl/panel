import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Locator, type Page } from '@playwright/test';

const uuid = '12345678-1234-1234-1234-123456789abc';
const argument = 'x'.repeat(240);
const command = `java --argument=${argument}`;
let html: string;

test.beforeAll(() => {
    html = execFileSync('php', ['tests/Fixtures/auth-page.php', '/server/12345678/settings', 'admin'], {
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
    });
});

async function prepare(page: Page, permissions: string[]) {
    const publicPath = resolve('public');
    const mutations: { path: string; body: unknown }[] = [];
    let value = 'initial';
    const variable = () => ({
        attributes: {
            name: 'Regression value',
            description: '',
            env_variable: 'REGRESSION_VALUE',
            default_value: 'initial',
            server_value: value,
            is_editable: true,
            rules: 'required|string',
        },
    });
    await page.route('**/*', async (route) => {
        const url = new URL(route.request().url());
        if (url.origin !== 'http://panel.test') return route.abort();
        if (url.pathname.startsWith('/server/')) {
            return route.fulfill({ contentType: 'text/html', body: html });
        }
        if (url.pathname === '/locales/locale.json') {
            return route.fulfill({
                contentType: 'application/json',
                body: execFileSync('php', ['tests/Fixtures/auth-page.php', url.pathname + url.search, 'admin'], {
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
                }),
            });
        }
        if (url.pathname.startsWith('/api/client/servers/')) {
            if (route.request().method() !== 'GET') {
                const body = route.request().postDataJSON();
                mutations.push({ path: url.pathname, body });
                if (url.pathname.endsWith('/startup/variable')) {
                    value = body.value;
                    return route.fulfill({ json: { ...variable(), meta: { startup_command: `${command} ${value}` } } });
                }
                return route.fulfill({ status: 204 });
            }
            if (url.pathname.endsWith('/startup')) {
                return route.fulfill({
                    json: {
                        data: [variable()],
                        meta: {
                            startup_command: `${command} ${value}`,
                            docker_images: { Java: 'java:21', JavaNext: 'java:25' },
                        },
                    },
                });
            }
            if (/\/servers\/[^/]+$/.test(url.pathname)) {
                return route.fulfill({
                    json: {
                        attributes: {
                            identifier: '12345678',
                            __deprecated_uuid_short: '12345678',
                            uuid,
                            name: 'Layout regression',
                            node: 'Demo Node',
                            status: null,
                            invocation: `${command} ${value}`,
                            docker_image: 'java:21',
                            sftp_details: { ip: '127.0.0.1', port: 2022 },
                            limits: {},
                            feature_limits: {},
                            default_timezone: 'UTC',
                            relationships: { variables: { data: [variable()] } },
                        },
                        meta: { user_permissions: permissions },
                    },
                });
            }
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
    return mutations;
}

async function expectTextContained(text: Locator) {
    await expect(text).toBeVisible();
    expect(
        await text.evaluate((element) => {
            const bounds = element.getBoundingClientRect();
            const range = document.createRange();
            range.selectNodeContents(element);
            return (
                bounds.left >= 0 &&
                bounds.right <= innerWidth &&
                [...range.getClientRects()].every(
                    (line) => line.left >= bounds.left && line.right <= bounds.right && line.bottom <= bounds.bottom,
                )
            );
        }),
    ).toBe(true);
}

for (const width of [320, 390, 768, 1280]) {
    test(`startup command wraps and its controls remain usable at ${width}px`, async ({ page }) => {
        await page.setViewportSize({ width, height: 900 });
        const mutations = await prepare(page, ['startup.read', 'startup.update', 'startup.docker-image']);
        await page.goto('http://panel.test/server/12345678/startup');
        const invocation = page.locator('p.font-mono');
        await expectTextContained(invocation);
        await expect(invocation).toHaveText(`${command} initial`);
        const image = page.locator('select');
        await expect(image).toBeVisible();
        await image.selectOption('java:25');
        await expect
            .poll(() => mutations.some((mutation) => mutation.path.endsWith('/settings/docker-image')))
            .toBe(true);
        await page.getByRole('textbox', { name: 'Regression value', exact: true }).fill('updated');
        await expect
            .poll(() => mutations.find((mutation) => mutation.path.endsWith('/startup/variable'))?.body)
            .toEqual({
                key: 'REGRESSION_VALUE',
                value: 'updated',
            });
        await expect(invocation).toHaveText(`${command} updated`);
        await expectTextContained(invocation);
    });

    for (const settings of [
        [],
        ['settings.rename'],
        ['settings.timezone'],
        ['settings.rename', 'settings.timezone'],
        ['*'],
    ]) {
        test(`Settings contains UUID and allocates columns for ${settings.join(',') || 'SFTP only'} at ${width}px`, async ({
            page,
        }) => {
            await page.setViewportSize({ width, height: 900 });
            await prepare(page, settings.includes('*') ? ['*'] : ['file.sftp', ...settings]);
            await page.goto('http://panel.test/server/12345678/settings');
            const code = page.locator('code').filter({ hasText: uuid });
            await expectTextContained(code);
            const copy = code.locator('..');
            const label = copy.getByText('Server ID', { exact: true });
            expect(
                await label.evaluate((element) => {
                    const range = document.createRange();
                    range.selectNodeContents(element);
                    return range.getClientRects().length;
                }),
            ).toBe(1);
            await page.evaluate(() => {
                document.addEventListener(
                    'copy',
                    () => {
                        document.body.dataset.copied = document.getSelection()?.toString() ?? '';
                    },
                    true,
                );
            });
            for (const key of ['Enter', 'Space']) {
                await copy.focus();
                await copy.press(key);
                await expect(page.locator('body')).toHaveAttribute('data-copied', uuid);
                await expect(page.locator('#modal-portal')).toContainText(`Copied "${uuid}" to clipboard.`);
            }
            await copy.click();
            await expect(page.locator('body')).toHaveAttribute('data-copied', uuid);
            const rename = settings.includes('settings.rename') || settings.includes('*');
            const timezone = settings.includes('settings.timezone') || settings.includes('*');
            await expect(page.getByText('Change Server Details', { exact: true })).toHaveCount(rename ? 1 : 0);
            await expect(page.getByText('Server Timezone', { exact: true })).toHaveCount(timezone ? 1 : 0);
            const column = code.locator('../../../..');
            expect(await column.locator('..').evaluate((layout) => layout.children.length)).toBe(
                rename || timezone ? 2 : 1,
            );
            if (!rename && !timezone) {
                const widths = await column.evaluate((column) => ({
                    own: column.clientWidth,
                    parent: column.parentElement!.clientWidth,
                }));
                expect(widths.own).toBe(widths.parent);
            }
            const launch = page.getByRole('link', { name: 'Launch SFTP', exact: true });
            await expect(launch).toBeVisible();
            await expect(launch).toHaveAttribute('href', /^sftp:\/\/.*\.12345678@127\.0\.0\.1:2022$/);
        });
    }
}
