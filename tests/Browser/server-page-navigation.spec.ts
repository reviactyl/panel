import { expect, test } from '@playwright/test';

function startNavigationTrace() {
    const state = { failures: [] as string[], frames: 0, fileFrames: 0, running: true };
    Object.assign(window, { navigationTrace: state });
    const sample = () => {
        if (!state.running) return;
        const targets = document.querySelectorAll('.text-4xl, a[href*="/files/edit#"]');
        if (targets.length) state.frames++;
        if (document.querySelector('a[href*="/files/edit#"]')) state.fileFrames++;
        for (const target of targets) {
            for (let node: Element | null = target; node; node = node.parentElement) {
                const opacity = Number(getComputedStyle(node).opacity);
                if (opacity < 1) state.failures.push(`${target.textContent}: opacity ${opacity}`);
            }
        }
        const editor = document.querySelector('.cm-content');
        if (editor?.textContent && document.querySelector('.animate-spin')) {
            state.failures.push('Loaded editor is visible behind the loading overlay');
        }
        requestAnimationFrame(sample);
    };
    sample();
}

function stopNavigationTrace() {
    const state = (
        window as unknown as {
            navigationTrace: { failures: string[]; frames: number; fileFrames: number; running: boolean };
        }
    ).navigationTrace;
    state.running = false;
    return state;
}

for (const path of ['/welcome.txt', '/nested/welcome.txt']) {
    test(`detects a file-list fade independently of the heading (${path})`, async ({ page }) => {
        await page.setContent(`
            <div class="text-4xl">File Manager</div>
            <div style="opacity: 0.5"><div><a href="/server/demo/files/edit#${path}">welcome.txt</a></div></div>
        `);
        await page.evaluate(startNavigationTrace);
        await page.waitForTimeout(100);
        const trace = await page.evaluate(stopNavigationTrace);
        expect(trace.fileFrames).toBeGreaterThan(0);
        expect(trace.failures).toContain('welcome.txt: opacity 0.5');
        expect(trace.failures.some((failure) => failure.startsWith('File Manager:'))).toBe(false);
    });
}

test('opening and closing a file keeps the file pages visible', async ({ page }) => {
    const url = process.env.FILE_EDITOR_DEMO_URL;
    test.skip(!url, 'Requires an isolated Panel and Agent demo.');
    await page.goto(`${url}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.locator('button[type=submit]').click();
    await page.waitForURL(`${url}/`);
    const response = await page.request.get(`${url}/api/client`);
    const servers = await response.json();
    const server = servers.data.find(
        (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
    );
    expect(server).toBeTruthy();
    await page.goto(`${url}/server/${server.attributes.identifier}/files`);
    await page.getByText('welcome.txt', { exact: true }).first().waitFor();
    await page.waitForTimeout(300);
    await page.evaluate(startNavigationTrace);
    for (let cycle = 0; cycle < 3; cycle++) {
        await page.getByText('welcome.txt', { exact: true }).first().click();
        await page.locator('.cm-content, .monaco-editor').first().waitFor();
        await page.waitForTimeout(300);
        await page.getByRole('link', { name: 'container', exact: true }).click();
        await page.getByText('welcome.txt', { exact: true }).first().waitFor();
        await page.waitForTimeout(300);
    }
    const search = page.getByRole('textbox', { name: 'Search files...' });
    await search.fill('welcome.txt');
    await page.waitForResponse((response) => response.url().includes('/files/list?'));
    await page.locator('a[href*="/files/edit#"]', { hasText: 'welcome.txt' }).first().waitFor();
    await page.waitForTimeout(300);
    await search.fill('');
    await page.waitForTimeout(600);
    const trace = await page.evaluate(stopNavigationTrace);
    expect(trace.frames).toBeGreaterThan(10);
    expect(trace.fileFrames).toBeGreaterThan(0);
    expect(trace.failures).toEqual([]);
});

test('fresh in-app login can open File Manager controls without refreshing the document', async ({ page }) => {
    const url = process.env.FILE_EDITOR_DEMO_URL;
    test.skip(!url, 'Requires an isolated Panel and Agent demo.');
    await page.goto(`${url}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.locator('button[type=submit]').click();
    await page.locator('a[href^="/server/"]').first().click();
    await page.getByRole('link', { name: 'Files', exact: true }).click();
    await expect(page.getByRole('textbox', { name: 'Search files...' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'New File', exact: true })).toBeVisible();
    await expect(
        page.getByText('An error was encountered by the application while rendering this view.', {
            exact: false,
        }),
    ).toHaveCount(0);
    await expect(page.locator('#modal-portal')).toHaveCount(1);
});

test('switching server tabs keeps each page visible', async ({ page }) => {
    const url = process.env.FILE_EDITOR_DEMO_URL;
    test.skip(!url, 'Requires an isolated Panel and Agent demo.');
    await page.goto(`${url}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.locator('button[type=submit]').click();
    await page.waitForURL(`${url}/`);
    const response = await page.request.get(`${url}/api/client`);
    const servers = await response.json();
    const server = servers.data.find(
        (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
    );
    expect(server).toBeTruthy();
    const serverPath = `/server/${server.attributes.identifier}`;
    await page.goto(`${url}${serverPath}/files`);
    await page.getByText('welcome.txt', { exact: true }).first().waitFor();
    await page.waitForTimeout(300);
    await page.evaluate(startNavigationTrace);
    for (const tab of [
        'startup',
        'network',
        'settings',
        'backups',
        'schedules',
        'databases',
        'users',
        'startup',
        'network',
        'files',
    ]) {
        await page.locator(`a[href="${serverPath}/${tab}"]`).first().click();
        await page.waitForURL(`${url}${serverPath}/${tab}`);
        await page.locator('.text-4xl').waitFor();
        await page.waitForTimeout(250);
    }
    const trace = await page.evaluate(stopNavigationTrace);
    expect(trace.frames).toBeGreaterThan(10);
    expect(trace.fileFrames).toBeGreaterThan(0);
    expect(trace.failures).toEqual([]);
});

for (const editor of ['cm', 'mo']) {
    test(`unsaved file drafts are protected across navigation (${editor})`, async ({ page }) => {
        const url = process.env.FILE_EDITOR_DEMO_URL;
        test.skip(!url, 'Requires an isolated Panel and Agent demo.');
        const errors: string[] = [];
        page.on('pageerror', (error) => errors.push(error.message));
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.locator('button[type=submit]').click();
        await page.waitForURL(`${url}/`);
        await page.evaluate(async (fileEditor) => {
            const token = document.cookie.split('; ').find((cookie) => cookie.startsWith('XSRF-TOKEN='))!;
            const response = await fetch('/api/client/account/file-editor', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(token.slice('XSRF-TOKEN='.length)),
                    Accept: 'application/json',
                },
                body: JSON.stringify({ fileEditor }),
            });
            if (!response.ok) throw new Error('Could not select editor');
        }, editor);
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        const base = `${url}/server/${server.attributes.identifier}`;
        await page.goto(`${base}/files`);
        await page.getByText('welcome.txt', { exact: true }).first().click();
        const input = page.locator(editor === 'cm' ? '.cm-content' : '.monaco-editor');
        await expect(input).toBeVisible();
        const original = await (
            await page.request.get(
                `${base.replace('/server/', '/api/client/servers/')}/files/contents?file=/welcome.txt`,
            )
        ).text();
        const draft = `REV-55 draft ${editor}`;
        await input.click();
        await page.keyboard.press('ControlOrMeta+A');
        await page.keyboard.insertText(draft);
        const dialogs: string[] = [];
        let discard = false;
        page.on('dialog', async (dialog) => {
            dialogs.push(dialog.type());
            if (discard) await dialog.accept();
            else await dialog.dismiss();
        });
        const text = () => page.locator(editor === 'cm' ? '.cm-content' : '.monaco-editor .view-lines');
        const breadcrumb = page.getByRole('link', { name: 'container', exact: true });
        await breadcrumb.click();
        await expect(text()).toContainText(draft);
        await expect.poll(() => dialogs).toEqual(['confirm']);
        await page.locator(`a[href="/server/${server.attributes.identifier}/startup"]`).first().click();
        await expect(text()).toContainText(draft);
        await expect.poll(() => dialogs.length).toBe(2);
        await page.goBack();
        await expect(text()).toContainText(draft);
        await expect.poll(() => dialogs.length).toBe(3);
        await page.evaluate(() => window.location.reload());
        await expect(text()).toContainText(draft);
        await expect.poll(() => dialogs).toEqual(['confirm', 'confirm', 'confirm', 'beforeunload']);
        await page
            .locator('a[href="/"]')
            .filter({ has: page.locator('img') })
            .filter({ visible: true })
            .first()
            .click();
        await expect(text()).toContainText(draft);
        await expect.poll(() => dialogs.length).toBe(5);
        discard = true;
        await breadcrumb.click();
        await page.getByText('welcome.txt', { exact: true }).first().click();
        await expect(input).toBeVisible();
        await expect(text()).not.toContainText(draft);
        expect(
            await (
                await page.request.get(
                    `${base.replace('/server/', '/api/client/servers/')}/files/contents?file=/welcome.txt`,
                )
            ).text(),
        ).toBe(original);
        const afterDiscard = dialogs.length;
        await input.click();
        await page.keyboard.press('ControlOrMeta+A');
        await page.keyboard.insertText(`REV-55 saved ${editor}`);
        await page.getByRole('button', { name: 'Save Content', exact: true }).click();
        await expect(page.getByRole('button', { name: 'Save Content', exact: true })).toBeEnabled();
        await breadcrumb.click();
        await page.getByText('welcome.txt', { exact: true }).first().waitFor();
        expect(dialogs).toHaveLength(afterDiscard);
        await page.getByRole('button', { name: 'New File', exact: true }).click();
        await expect(input).toBeVisible();
        await input.click();
        await page.keyboard.insertText('new unsaved file');
        discard = false;
        await breadcrumb.click();
        await expect(text()).toContainText('new unsaved file');
        await expect.poll(() => dialogs.length).toBe(afterDiscard + 1);
        discard = true;
        await breadcrumb.click();
        expect(errors).toEqual([]);
    });
}

test('logout waits for an older session response and keeps the next login logout valid', async ({ page }) => {
    const url = process.env.FILE_EDITOR_DEMO_URL;
    test.skip(!url, 'Requires an isolated Panel and Agent demo.');
    await page.goto(`${url}/auth/login`, { waitUntil: 'domcontentloaded' });
    const login = async () => {
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.locator('button[type=submit]').click();
        await page.locator('a[href="/account"]').filter({ visible: true }).first().waitFor();
    };
    await login();
    await page.evaluate(() => Object.assign(window, { SocialLoginConfiguration: { google: true } }));
    let release!: () => void;
    const held = new Promise<void>((resolve) => {
        release = resolve;
    });
    let arrived!: () => void;
    const received = new Promise<void>((resolve) => {
        arrived = resolve;
    });
    await page.route('**/api/client/account/social-logins', async (route) => {
        const response = await route.fetch();
        arrived();
        await held;
        await route.fulfill({ response });
    });
    await page.locator('a[href="/account"]').filter({ visible: true }).first().click();
    await received;
    let logoutRequests = 0;
    page.on('request', (request) => {
        if (request.method() === 'POST' && new URL(request.url()).pathname === '/auth/logout') logoutRequests++;
    });
    await page.getByRole('button', { name: 'Logout', exact: true }).focus();
    await page.keyboard.press('Enter');
    await page.waitForTimeout(250);
    expect(logoutRequests).toBe(0);
    release();
    await page.locator('input[name=username]').waitFor();
    expect((await page.request.get(`${url}/api/client`, { headers: { Accept: 'application/json' } })).status()).toBe(
        401,
    );
    await page.unroute('**/api/client/account/social-logins');
    await login();
    await page.locator('a[href="/account"]').filter({ visible: true }).first().click();
    await page.getByRole('button', { name: 'Logout', exact: true }).click();
    await page.locator('input[name=username]').waitFor();
    expect((await page.request.get(`${url}/api/client`, { headers: { Accept: 'application/json' } })).status()).toBe(
        401,
    );
});
