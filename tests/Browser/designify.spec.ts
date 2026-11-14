import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

test.beforeEach(async ({ page }) => {
    const html = execFileSync('php', ['tests/Fixtures/designify-page.php'], {
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
    const publicPath = resolve('public');
    await page.route('**/*', async (route) => {
        const url = new URL(route.request().url());
        if (url.origin !== 'http://panel.test') return route.abort();
        if (url.pathname === '/admin/designify') return route.fulfill({ contentType: 'text/html', body: html });
        if (url.pathname === '/admin') {
            return route.fulfill({ contentType: 'text/html', body: '<h1>Dashboard</h1>' });
        }
        if (/\/livewire(?:\.min)?\.js$/.test(url.pathname)) {
            return route.fulfill({
                contentType: 'application/javascript',
                body: readFileSync('vendor/livewire/livewire/dist/livewire.js'),
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

    await page.goto('http://panel.test/admin/designify');
});

test('canceling the leave warning preserves an edited Designify setting', async ({ page }) => {
    const field = page.getByRole('textbox', { name: 'Copyright Text' });
    await field.fill('Unsaved copyright');
    const back = page.getByRole('link', { name: 'Return to administration' });
    const warning = page.waitForEvent('dialog').then(async (dialog) => {
        expect(dialog.type()).toBe('beforeunload');
        await dialog.dismiss();
    });
    await back.click();
    await warning;
    await expect(page).toHaveURL('http://panel.test/admin/designify');
    await expect(field).toHaveValue('Unsaved copyright');

    const leaveWarning = page.waitForEvent('dialog').then((dialog) => dialog.accept());
    await back.click();
    await leaveWarning;
    await expect(page).toHaveURL('http://panel.test/admin');
});

test('restoring the original image path clears unsaved state without a server response', async ({ page }) => {
    await page.getByRole('tab', { name: 'Meta Options', exact: true }).click();
    const field = page.getByRole('textbox', { name: 'Site Image*', exact: true });
    const original = await field.inputValue();
    const status = page.locator('.designify-editor__save-state:visible');
    await expect(status).toHaveAttribute('data-dirty', 'false');

    await field.press('ControlOrMeta+A');
    await field.press('ControlOrMeta+X');
    await expect(field).toHaveValue('');
    await expect(status).toHaveAttribute('data-dirty', 'true');
    await field.press('ControlOrMeta+V');
    await expect(field).toHaveValue(original);
    await expect(status).toHaveAttribute('data-dirty', 'false');
    await expect(status).toHaveText('Saved');

    await field.fill(original + '?changed');
    await expect(status).toHaveAttribute('data-dirty', 'true');
    await field.fill(original);
    await expect(status).toHaveAttribute('data-dirty', 'false');
    await page.getByRole('link', { name: 'Return to administration' }).click();
    await expect(page).toHaveURL('http://panel.test/admin');
});

test('restoring one field keeps other unsaved changes and ignores stale preview events', async ({ page }) => {
    await page.getByRole('tab', { name: 'Meta Options', exact: true }).click();
    const field = page.getByRole('textbox', { name: 'Site Image*', exact: true });
    const original = await field.inputValue();
    const status = page.locator('.designify-editor__save-state:visible');
    await page.getByRole('textbox', { name: 'Site Title*', exact: true }).fill('Unsaved title');
    await field.fill('');
    await field.fill(original);
    await expect(status).toHaveAttribute('data-dirty', 'true');

    await page.evaluate(() => {
        window.dispatchEvent(new CustomEvent('designify-preview-updated', { detail: { settings: {} } }));
    });
    await expect(status).toHaveAttribute('data-dirty', 'true');
});

test('a save acknowledgement preserves newer edits and updates the saved comparison', async ({ page }) => {
    const field = page.getByRole('textbox', { name: 'Copyright Text' });
    const status = page.locator('.designify-editor__save-state:visible');
    await field.fill('Saved copyright');
    const savedFormData = await page.locator('.designify-editor').evaluate((editor) => {
        const id = editor.closest('[wire\\:id]')!.getAttribute('wire:id')!;
        const livewire = (window as unknown as { Livewire: { find(id: string): { data: unknown } } }).Livewire;
        return JSON.parse(JSON.stringify(livewire.find(id).data));
    });
    await field.fill('Newer unsaved copyright');
    await page.evaluate((formData) => {
        window.dispatchEvent(new CustomEvent('designify-saved', { detail: { settings: {}, formData } }));
    }, savedFormData);
    await expect(status).toHaveAttribute('data-dirty', 'true');

    await field.fill('Saved copyright');
    await expect(status).toHaveAttribute('data-dirty', 'false');
});
