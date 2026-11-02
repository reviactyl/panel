import { expect, test } from '@playwright/test';

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
    await page.evaluate(() => {
        const state = { failures: [] as string[], frames: 0, running: true };
        Object.assign(window, { fileNavigationTrace: state });
        const sample = () => {
            if (!state.running) return;
            const title = document.querySelector('.text-4xl');
            if (title?.textContent?.match(/File (Editor|Manager)/)) {
                state.frames++;
                for (let node: Element | null = title; node; node = node.parentElement) {
                    const opacity = Number(getComputedStyle(node).opacity);
                    if (opacity < 1) state.failures.push(`${title.textContent}: opacity ${opacity}`);
                }
            }
            const editor = document.querySelector('.cm-content');
            if (editor?.textContent && document.querySelector('.animate-spin')) {
                state.failures.push('Loaded editor is visible behind the loading overlay');
            }
            requestAnimationFrame(sample);
        };
        sample();
    });
    for (let cycle = 0; cycle < 3; cycle++) {
        await page.getByText('welcome.txt', { exact: true }).first().click();
        await page.locator('.cm-content, .monaco-editor').first().waitFor();
        await page.waitForTimeout(300);
        await page.getByRole('link', { name: 'container', exact: true }).click();
        await page.getByText('welcome.txt', { exact: true }).first().waitFor();
        await page.waitForTimeout(300);
    }
    const trace = await page.evaluate(() => {
        const state = (
            window as unknown as { fileNavigationTrace: { failures: string[]; frames: number; running: boolean } }
        ).fileNavigationTrace;
        state.running = false;
        return state;
    });
    expect(trace.frames).toBeGreaterThan(10);
    expect(trace.failures).toEqual([]);
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
    await page.evaluate(() => {
        const state = { failures: [] as string[], frames: 0, running: true };
        Object.assign(window, { serverNavigationTrace: state });
        const sample = () => {
            if (!state.running) return;
            const title = document.querySelector('.text-4xl');
            if (title) {
                state.frames++;
                for (let node: Element | null = title; node; node = node.parentElement) {
                    const opacity = Number(getComputedStyle(node).opacity);
                    if (opacity < 1) state.failures.push(`${title.textContent}: opacity ${opacity}`);
                }
            }
            requestAnimationFrame(sample);
        };
        sample();
    });
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
    const trace = await page.evaluate(() => {
        const state = (
            window as unknown as { serverNavigationTrace: { failures: string[]; frames: number; running: boolean } }
        ).serverNavigationTrace;
        state.running = false;
        return state;
    });
    expect(trace.frames).toBeGreaterThan(10);
    expect(trace.failures).toEqual([]);
});
