import { expect, test } from '@playwright/test';

test('recycle bin uses file controls and supports browsing, editing, nested restore, delete and empty confirmation', async ({
    page,
}) => {
    const url = process.env.RECYCLE_BIN_DEMO_URL;
    test.skip(!url, 'Requires the task-owned isolated Panel and Agent demo.');
    test.setTimeout(90000);
    page.setDefaultTimeout(15000);
    await page.goto(`${url}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.getByRole('button', { name: 'Login', exact: true }).click();
    await page.waitForURL(`${url}/`);
    const servers = await (await page.request.get(`${url}/api/client`)).json();
    const id = servers.data[0].attributes.identifier;
    const api = `${url}/api/client/servers/${id}/files`;
    const filesUrl = `${url}/server/${id}/files`;
    const csrf = decodeURIComponent(
        (await page.context().cookies()).find((cookie) => cookie.name === 'XSRF-TOKEN')!.value,
    );
    const headers = { 'X-XSRF-TOKEN': csrf, Accept: 'application/json' };
    const post = async (route: string, data: unknown) => {
        const response = await page.request.post(`${api}/${route}`, { headers, data });
        expect(response.ok(), `${route}: ${await response.text()}`).toBe(true);
        return response;
    };
    const previous = await (await page.request.get(`${api}/recycle-bin`)).json();
    for (const item of previous.data) {
        if (item.original_path === '/bin-live-check') await post('recycle-bin/delete', { id: item.id });
    }
    await post('create-folder', { root: '/', name: 'bin-live-check' });
    await post('create-folder', { root: '/bin-live-check', name: 'deep' });
    await post('create-folder', { root: '/bin-live-check', name: 'remove-me' });
    const write = await page.request.post(`${api}/write?file=/bin-live-check/deep/example.txt`, {
        headers: { ...headers, 'Content-Type': 'text/plain' },
        data: 'before editing',
    });
    expect(write.ok()).toBe(true);
    await page.goto(filesUrl);
    await page.getByRole('button', { name: 'Action: bin-live-check', exact: true }).click();
    await page.getByRole('button', { name: 'Delete', exact: true }).click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toContainText('permanently deleted after 31 days');
    const actions = dialog.locator('button').filter({ hasText: /^(Cancel|Delete|Delete permanently)$/ });
    await expect(actions).toHaveText(['Cancel', 'Delete', 'Delete permanently']);
    await dialog.getByRole('button', { name: 'Delete permanently', exact: true }).click();
    await expect(dialog).toContainText('cannot be restored');
    await dialog.getByRole('button', { name: 'Go back', exact: true }).click();
    await dialog.getByRole('button', { name: 'Delete', exact: true }).click();
    await expect(dialog).toHaveCount(0);
    const bin = page.getByRole('button', { name: /Recycle Bin/ }).filter({ hasText: 'Recycle Bin' });
    await expect(bin).toHaveCSS('cursor', 'pointer');
    await bin.click();
    await expect(page.getByText('Recycled files still use server storage.')).toHaveCount(0);
    await page
        .getByRole('button', { name: /bin-live-check/ })
        .filter({ hasText: 'bin-live-check' })
        .click();
    await page.getByRole('button', { name: /^deep/ }).click();
    await page
        .getByRole('button', { name: /example.txt/ })
        .filter({ hasText: 'example.txt' })
        .click();
    await expect(page).toHaveURL(/\/files\/edit#/);
    await expect(page.getByText('example.txt', { exact: true })).toBeVisible();
    await page.locator('.cm-content, .monaco-editor').first().waitFor();
    const cm = page.locator('.cm-content');
    if (await cm.count()) {
        await cm.click();
        await page.keyboard.press('ControlOrMeta+A');
        await page.keyboard.insertText('edited while recycled');
    } else {
        await page.locator('.monaco-editor').first().click();
        await page.keyboard.press('ControlOrMeta+A');
        await page.keyboard.insertText('edited while recycled');
    }
    await page.getByRole('button', { name: 'Save Content', exact: true }).click();
    const listing = await (await page.request.get(`${api}/recycle-bin`)).json();
    const entry = listing.data.find((item: { original_path: string }) => item.original_path === '/bin-live-check');
    await expect
        .poll(async () =>
            (
                await page.request.get(`${api}/contents`, {
                    params: { file: `${entry.storage_path}/deep/example.txt` },
                })
            ).text(),
        )
        .toBe('edited while recycled');
    await page.goto(`${filesUrl}#/.recycle-bin/${entry.id}/bin-live-check/deep`);
    await page.getByRole('checkbox', { name: '/bin-live-check/deep/example.txt', exact: true }).check();
    await page.getByRole('button', { name: 'Restore', exact: true }).click();
    await expect(dialog).toContainText('/bin-live-check/deep/example.txt');
    await dialog.getByRole('button', { name: 'Restore', exact: true }).click();
    await expect(dialog).toHaveCount(0);
    expect(await (await page.request.get(`${api}/contents?file=/bin-live-check/deep/example.txt`)).text()).toBe(
        'edited while recycled',
    );
    await page.goto(`${filesUrl}#/.recycle-bin/${entry.id}/bin-live-check`);
    await page.getByRole('button', { name: 'Action: remove-me', exact: true }).click();
    await expect(page.getByRole('button', { name: 'Open', exact: true })).toHaveCount(0);
    await page.getByRole('button', { name: 'Delete', exact: true }).click();
    await dialog.getByRole('button', { name: 'Delete permanently', exact: true }).click();
    await dialog.getByRole('button', { name: 'Delete permanently', exact: true }).click();
    await expect(page.getByRole('checkbox', { name: '/bin-live-check/remove-me', exact: true })).toHaveCount(0);
    await page.goto(`${filesUrl}#/.recycle-bin`);
    await page.getByRole('checkbox', { name: '/bin-live-check', exact: true }).check();
    const restore = page.getByRole('button', { name: 'Restore', exact: true });
    await expect(restore.locator('svg')).toHaveCSS('width', '20px');
    await expect(page.getByRole('button', { name: 'Archive', exact: true })).toHaveCount(0);
    await page.setViewportSize({ width: 390, height: 844 });
    await expect.poll(() => page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    await page.screenshot({ path: '/tmp/rev117-bin-mobile.png' });
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.screenshot({ path: '/tmp/rev117-bin-desktop.png' });
    await page.goto(filesUrl);
    await bin.click({ button: 'right' });
    await page.getByRole('button', { name: 'Empty', exact: true }).click();
    await dialog.getByRole('button', { name: 'Delete permanently', exact: true }).click();
    await expect(dialog).toContainText('cannot be restored');
    await dialog.getByRole('button', { name: 'Go back', exact: true }).click();
    expect((await (await page.request.get(`${api}/recycle-bin`)).json()).data.length).toBeGreaterThan(0);
    await dialog.getByRole('button', { name: 'Cancel', exact: true }).click();
});
