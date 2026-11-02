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
