import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test, type Page } from '@playwright/test';

const fixtures = new Map<string, string>();
const renderFixture = (path: string, authenticated = false) => {
    const key = `${authenticated}:${path}`;
    if (!fixtures.has(key)) {
        fixtures.set(
            key,
            execFileSync('php', ['tests/Fixtures/auth-page.php', path, authenticated ? 'admin' : 'guest'], {
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

async function openFixture(
    page: Page,
    path: string,
    language: string,
    layout = 'modern',
    settings: Record<string, string> = {},
    responses: Record<string, unknown> = {},
) {
    const authenticated = !path.startsWith('/auth/');
    const html = renderFixture(path, authenticated).replace(
        '</body>',
        `<script>
            window.SiteConfiguration.locale = ${JSON.stringify(language)};
            window.PanelConfiguration.layoutType = ${JSON.stringify(layout)};
            Object.assign(window.PanelConfiguration, ${JSON.stringify(settings)});
            if (window.PanelUser) window.PanelUser.language = ${JSON.stringify(language)};
        </script></body>`,
    );
    const publicPath = resolve('public');
    await page.route('**/*', async (route) => {
        const url = new URL(route.request().url());
        if (url.origin !== 'http://panel.test') return route.abort();
        if (url.pathname === path) return route.fulfill({ contentType: 'text/html', body: html });
        if (url.pathname === '/locales/locale.json' || url.pathname === '/locales/list.json') {
            return route.fulfill({ contentType: 'application/json', body: renderFixture(url.pathname + url.search) });
        }
        if (url.pathname in responses) return route.fulfill({ json: responses[url.pathname] });
        if (url.pathname.startsWith('/api/') || url.pathname === '/auth/alerts') {
            return route.fulfill({ json: { data: [], categories: [], unread: 0, last_page: 1 } });
        }
        const file = resolve(publicPath, `.${url.pathname}`);
        if (!file.startsWith(publicPath + sep)) return route.abort();
        try {
            await route.fulfill({ path: file });
        } catch {
            await route.abort();
        }
    });
    await page.goto(`http://panel.test${path}`);
    await expect(page.locator('html')).toHaveAttribute('dir', language === 'ar' ? 'rtl' : 'ltr');
}

for (const layout of ['modern', 'classic', 'compact', 'accent']) {
    for (const language of ['ar', 'en']) {
        test(`${layout} keeps ${language} sidebar and account controls separate on desktop and mobile`, async ({
            page,
        }) => {
            await page.setViewportSize({ width: 1280, height: 900 });
            await openFixture(page, '/account', language, layout);
            const sidebar = page.locator('div[class*="self-start"][class*="lg:fixed"]');
            const select = page.locator('select:has(option[value=ar])');
            await expect(select).toBeVisible();
            await expect(sidebar).toBeVisible();
            for (const width of [1280, 1600]) {
                await page.setViewportSize({ width, height: 900 });
                const sidebarBox = (await sidebar.boundingBox())!;
                const contentBox = (await page.locator('div[class*="lg:ps-62.5"] > div.min-w-0').boundingBox())!;
                if (language === 'ar') {
                    expect(sidebarBox.x).toBeGreaterThanOrEqual(width - 280);
                    expect(contentBox.x + contentBox.width).toBeLessThanOrEqual(sidebarBox.x);
                } else {
                    expect(sidebarBox.x + sidebarBox.width).toBeLessThanOrEqual(contentBox.x);
                }
            }
            await select.scrollIntoViewIfNeeded();
            await select.click();
            await expect(select).toBeFocused();
            await page.keyboard.press('Escape');

            await page.setViewportSize({ width: 390, height: 844 });
            await expect(sidebar).toBeHidden();
            await (
                layout === 'modern'
                    ? page.getByRole('navigation').getByRole('button')
                    : page.locator('div[class="lg:hidden"] button')
            ).click();
            await expect(sidebar).toBeVisible();
            const mobileBox = (await sidebar.boundingBox())!;
            expect(language === 'ar' ? mobileBox.x : 390 - mobileBox.x - mobileBox.width).toBeGreaterThan(150);
            expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(390);
        });
    }
}

test('Arabic selects reserve space for the arrow at the inline end', async ({ page }) => {
    await openFixture(page, '/account', 'ar');
    const select = page.locator('select:has(option[value=ar])');
    await expect(select).toBeVisible();
    const style = await select.evaluate((element) => {
        const style = getComputedStyle(element);
        return {
            left: parseFloat(style.paddingLeft),
            right: parseFloat(style.paddingRight),
            arrow: style.backgroundPositionX,
        };
    });
    expect(style.left).toBeGreaterThan(style.right);
    expect(style.arrow).toBe('12px');
});

for (const language of ['ar', 'en']) {
    for (const path of ['/auth/login', '/auth/register']) {
        test(`${language} ${path} joins field icons and reserves password reveal space`, async ({ page }) => {
            await page.setViewportSize({ width: 390, height: 844 });
            await openFixture(page, path, language);
            const input = page.locator('input[name=password]');
            const reveal = input.locator('xpath=../../..').getByRole('button');
            await expect(input).toBeVisible();
            const inputBox = (await input.boundingBox())!;
            const revealBox = (await reveal.boundingBox())!;
            expect(revealBox.x).toBeGreaterThanOrEqual(inputBox.x);
            expect(revealBox.x + revealBox.width).toBeLessThanOrEqual(inputBox.x + inputBox.width);
            const reservedSpace =
                language === 'ar'
                    ? revealBox.x + revealBox.width - inputBox.x
                    : inputBox.x + inputBox.width - revealBox.x;
            await expect
                .poll(() => input.evaluate((element) => parseFloat(getComputedStyle(element).paddingInlineEnd)))
                .toBeGreaterThanOrEqual(reservedSpace);
            await expect(input).toHaveCSS('border-start-start-radius', '0px');
            await expect
                .poll(() => input.evaluate((element) => parseFloat(getComputedStyle(element).borderStartEndRadius)))
                .toBeGreaterThan(0);
            const password = 'long-password-to-check-reveal-button-spacing';
            await input.fill(password);
            await reveal.click();
            await expect(input).toHaveAttribute('type', 'text');
            await expect(input).toHaveValue(password);
        });
    }
}

for (const width of [390, 1280]) {
    test(`Arabic server paths, dialogs and commands remain readable and usable at ${width}px`, async ({ page }) => {
        const url = process.env.RTL_DEMO_URL;
        test.skip(!url, 'Requires this task’s isolated Panel and Agent demo.');
        await page.setViewportSize({ width, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.locator('button[type=submit]').click();
        await page.waitForURL(`${url}/`);
        await page.goto(`${url}/account`);
        await page.locator('select:has(option[value=ar])').selectOption('ar');
        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        expect(server).toBeTruthy();
        const base = `${url}/server/${server.attributes.identifier}`;
        const navigateServer = async (path: string) => {
            const link = page.locator(`a[href="/server/${server.attributes.identifier}/${path}"]`).first();
            if (!(await link.isVisible())) {
                await page.getByRole('button', { name: 'عرض الشريط الجانبي', exact: true }).last().click();
            }
            await link.click();
            await page.waitForURL(`${base}/${path}`);
        };
        await page.goto(`${base}/files`);
        const container = page.getByRole('link', { name: 'container', exact: true });
        await expect(container).toBeVisible();
        const home = container.locator('..').getByText('home', { exact: true });
        expect((await home.boundingBox())!.x).toBeLessThan((await container.boundingBox())!.x);

        await page.getByRole('button', { name: 'انشاء مجلد', exact: true }).click();
        const dialog = page.getByRole('dialog', { name: 'انشاء مجلد', exact: true });
        const close = dialog.getByRole('button', { name: 'إغلاق', exact: true });
        const heading = dialog.getByRole('heading');
        await expect(close).toBeVisible();
        const closeBox = (await close.boundingBox())!;
        expect(closeBox.x + closeBox.width).toBeLessThanOrEqual((await heading.boundingBox())!.x);
        const name = dialog.getByRole('textbox');
        await name.fill('مجلد تجريبي');
        await expect(name).toBeFocused();
        await page.keyboard.press('Escape');
        await expect(dialog).toBeHidden();

        await navigateServer('backups');
        await page.getByRole('button', { name: 'إنشاء نسخة احتياطية للخادم', exact: true }).click();
        const format = page.locator('#backup-format');
        await expect(format).toBeVisible();
        const formatBox = (await format.boundingBox())!;
        const arrowBox = (await format.locator('..').locator('svg').boundingBox())!;
        expect(arrowBox.x + arrowBox.width).toBeLessThan(formatBox.x + formatBox.width / 2);
        await format.selectOption('zip');
        await expect(format).toHaveValue('zip');
        const locked = page.locator('input[name=isLocked]');
        await locked.focus();
        await locked.press('Space');
        await expect(locked).toBeChecked();
        const knob = locked.locator('..').locator('label');
        await expect
            .poll(() => knob.evaluate((element) => parseFloat(getComputedStyle(element, '::before').translate)))
            .toBeLessThan(0);
        await page.getByText('Locked', { exact: true }).click();
        await expect(locked).not.toBeChecked();
        await page.keyboard.press('Escape');
        await expect(format).toBeHidden();

        await navigateServer('');
        const command = page.getByRole('textbox', { name: 'Console command input.' });
        await expect(command).toBeEnabled();
        await command.scrollIntoViewIfNeeded();
        const commandBox = (await command.boundingBox())!;
        expect(commandBox.x).toBeGreaterThanOrEqual(0);
        expect(commandBox.x + commandBox.width).toBeLessThanOrEqual(width);
        await expect(command).toHaveCSS('direction', 'ltr');
        await command.fill('say rtl-layout-check');
        await command.press('Enter');
        await expect(command).toHaveValue('');
        await expect(page.locator('.xterm-rows')).toContainText('rtl-layout-check');
        await command.press('ArrowUp');
        await expect(command).toHaveValue('say rtl-layout-check');
    });
}

const serverName = 'خادم المشروعات العربية الطويل للتطوير والاختبار Minecraft';
const allocation = {
    object: 'allocation',
    attributes: { id: 1, ip: '192.0.2.1', ip_alias: null, port: 25565, is_default: true, notes: null },
};
const serverFixture = {
    object: 'server',
    attributes: {
        identifier: 'rtl-test',
        server_identifier: 'srv_rtl-test',
        uuid: '00000000-0000-4000-8000-000000000001',
        name: serverName,
        description: 'وصف طويل لخادم المشروعات مع اسم تقني Minecraft وإعدادات مشتركة',
        node: 'Test Node',
        status: null,
        sftp_details: { ip: '192.0.2.1', port: 2022 },
        limits: { memory: 1024, disk: 2048, cpu: 200 },
        feature_limits: { databases: 1, allocations: 2, backups: 2 },
        relationships: { allocations: { data: [allocation] } },
    },
    meta: { is_server_owner: true },
};
const folderName = 'مجلد المشروعات العربية للتطوير';
const dashboardResponses = {
    '/api/client': {
        data: [serverFixture],
        meta: { pagination: { total: 1, count: 1, per_page: 50, current_page: 1, total_pages: 1 } },
    },
    '/api/client/account/categories': {
        data: [{ attributes: { uuid: 'folder-one', name: folderName, color: '#3b82f6', parent_uuid: null } }],
    },
    '/api/client/servers/00000000-0000-4000-8000-000000000001/resources': {
        attributes: {
            current_state: 'running',
            is_suspended: false,
            resources: { memory_bytes: 1024, cpu_absolute: 5, disk_bytes: 2048, uptime: 1000 },
        },
    },
};

for (const layout of ['modern', 'classic', 'compact', 'accent']) {
    for (const language of ['ar', 'en']) {
        for (const card of ['row', 'classic', 'background', 'enlarged']) {
            test(`${layout} ${language} ${card} dashboard keeps folders and server cards readable`, async ({
                page,
            }) => {
                await page.emulateMedia({ colorScheme: 'dark' });
                await openFixture(
                    page,
                    '/',
                    language,
                    layout,
                    {
                        serverOrganization: 'folders',
                        cardType: card === 'row' ? 'row' : 'grid',
                        cardIconStyle: card === 'classic' ? 'classic' : 'background',
                        cardIconSize: card === 'enlarged' ? 'enlarged' : 'standard',
                    },
                    dashboardResponses,
                );
                const folder = page.getByRole('button', { name: folderName, exact: true });
                const server = page.locator('a[href^="/server/rtl-test"]');
                await expect(folder).toBeVisible();
                await expect(server).toBeVisible();
                for (const width of [1280, 1600, 390]) {
                    await page.setViewportSize({ width, height: 900 });
                    await folder.scrollIntoViewIfNeeded();
                    const folderBox = (await folder.boundingBox())!;
                    const actions = folder.locator('..').locator('div.absolute');
                    const actionsBox = (await actions.boundingBox())!;
                    expect(
                        language === 'ar'
                            ? actionsBox.x + actionsBox.width < folderBox.x + folderBox.width / 2
                            : actionsBox.x > folderBox.x + folderBox.width / 2,
                    ).toBe(true);
                    const title = server.getByText(serverName, { exact: true });
                    const titleBox = (await title.boundingBox())!;
                    const serverBox = (await server.boundingBox())!;
                    expect(titleBox.x).toBeGreaterThanOrEqual(serverBox.x);
                    expect(titleBox.x + titleBox.width).toBeLessThanOrEqual(serverBox.x + serverBox.width);
                    if (card === 'row' && width >= 640) {
                        await expect(server.getByText('of 1 GiB', { exact: true })).toHaveCSS('direction', 'ltr');
                    }
                    if (card === 'classic') {
                        const imageBox = (await server.locator('img').boundingBox())!;
                        expect(
                            language === 'ar'
                                ? imageBox.x + imageBox.width / 2 < serverBox.x + serverBox.width / 2
                                : imageBox.x + imageBox.width / 2 > serverBox.x + serverBox.width / 2,
                        ).toBe(true);
                    }
                    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
                }
                const contrast = await folder.evaluate((element) => {
                    const channel = (value: number) =>
                        value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4;
                    const luminance = (color: string) => {
                        const [r = 0, g = 0, b = 0] = color
                            .match(/[\d.]+/g)!
                            .slice(0, 3)
                            .map((value) => channel(Number(value) / 255));
                        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
                    };
                    const foreground = luminance(getComputedStyle(element).color);
                    const background = luminance(getComputedStyle(element.parentElement!).backgroundColor);
                    return (Math.max(foreground, background) + 0.05) / (Math.min(foreground, background) + 0.05);
                });
                expect(contrast).toBeGreaterThanOrEqual(4.5);
            });
        }
    }
}

for (const layout of ['modern', 'classic', 'compact', 'accent']) {
    for (const headerStyle of ['classic', 'modern']) {
        for (const fileLayout of ['list', 'grid']) {
            test(`${layout} ${headerStyle} header and ${fileLayout} files keep Arabic controls accessible`, async ({
                page,
            }) => {
                const fileName = 'إعدادات Minecraft.txt';
                const base = '/server/rtl-test';
                const responses = {
                    ...dashboardResponses,
                    '/api/client/servers/rtl-test': serverFixture,
                    [`/api/client/servers/${serverFixture.attributes.uuid}/files/list`]: {
                        data: [
                            {
                                attributes: {
                                    name: fileName,
                                    mode: '-rw-r--r--',
                                    mode_bits: '644',
                                    size: 32,
                                    is_file: true,
                                    is_symlink: false,
                                    mimetype: 'text/plain',
                                    created_at: '2026-01-01T00:00:00Z',
                                    modified_at: '2026-01-01T00:00:00Z',
                                },
                            },
                        ],
                    },
                };
                await openFixture(page, `${base}/files`, 'ar', layout, { headerStyle, fileLayout }, responses);
                const checkbox = page.getByRole('checkbox', { name: fileName, exact: true });
                const file = page.getByRole('link', { name: new RegExp(fileName) });
                await expect(file).toBeVisible();
                for (const width of [1280, 1600, 390]) {
                    await page.setViewportSize({ width, height: 900 });
                    await file.scrollIntoViewIfNeeded();
                    const row = file.locator('..');
                    const rowBox = (await row.boundingBox())!;
                    const checkboxBox = (await checkbox.boundingBox())!;
                    expect(checkboxBox.x).toBeGreaterThanOrEqual(rowBox.x);
                    expect(checkboxBox.x + checkboxBox.width).toBeLessThanOrEqual(rowBox.x + rowBox.width);
                    const actions = row.getByRole('button', { name: new RegExp(fileName) });
                    const actionsBox = (await actions.boundingBox())!;
                    expect(actionsBox.x + actionsBox.width).toBeLessThanOrEqual(checkboxBox.x);
                    await checkbox.check();
                    await expect(checkbox).toBeChecked();
                    await checkbox.uncheck();
                    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
                }
                const home = page.getByText('home', { exact: true });
                const container = page.getByRole('link', { name: 'container', exact: true });
                expect((await home.boundingBox())!.x).toBeLessThan((await container.boundingBox())!.x);
            });
        }
    }
}

test('Arabic console detail cards separate icons and preserve technical value order', async ({ page }) => {
    await openFixture(
        page,
        '/server/rtl-test/',
        'ar',
        'classic',
        { headerStyle: 'classic' },
        {
            ...dashboardResponses,
            '/api/client/servers/rtl-test': serverFixture,
        },
    );
    const value = page.getByText('192.0.2.1:25565', { exact: true }).last();
    await expect(value).toBeVisible();
    await expect(value.locator('..')).toHaveCSS('direction', 'ltr');
    const block = value.locator('xpath=ancestor::div[contains(@class, "_stat_block_")]');
    const icon = block.locator('div[class*="_icon_"]');
    const title = block.locator('p');
    const iconBox = (await icon.boundingBox())!;
    const titleBox = (await title.boundingBox())!;
    expect(titleBox.x + titleBox.width).toBeLessThan(iconBox.x);
});

for (const language of ['ar', 'en']) {
    for (const path of ['/auth/register', '/auth/password', '/auth/password/reset/test-token']) {
        test(`${language} ${path} keeps form fields usable at narrow and desktop widths`, async ({ page }) => {
            await openFixture(page, path, language);
            const inputs = page.locator('form input:not([type=hidden]):not([type=checkbox])');
            await expect(inputs.first()).toBeVisible();
            for (const width of [320, 390, 1280]) {
                await page.setViewportSize({ width, height: 900 });
                for (const input of await inputs.all()) {
                    await input.scrollIntoViewIfNeeded();
                    const box = (await input.boundingBox())!;
                    expect(box.x).toBeGreaterThanOrEqual(0);
                    expect(box.x + box.width).toBeLessThanOrEqual(width);
                    if (await input.isEnabled()) {
                        await input.click();
                        await expect(input).toBeFocused();
                    }
                }
                expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBe(width);
            }
        });
    }
}
