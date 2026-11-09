import { expect, test } from '@playwright/test';
import { build } from 'vite';
import { resolve } from 'node:path';

test('local account preference labels focus the production selects', async ({ page }) => {
    const fixture = resolve('tests/Browser/fixtures/account-preferences');
    const result = await build({
        configFile: false,
        logLevel: 'silent',
        define: { 'process.env.NODE_ENV': JSON.stringify('production') },
        resolve: {
            alias: [
                { find: 'react-i18next', replacement: `${fixture}/translation.ts` },
                { find: '@/i18n', replacement: `${fixture}/translation.ts` },
                { find: /^@\/api\/account\/updateAccount(Language|Editor)$/, replacement: `${fixture}/save.ts` },
                { find: '@', replacement: resolve('resources/scripts') },
            ],
        },
        build: { write: false, lib: { entry: `${fixture}/main.tsx`, name: 'Preferences', formats: ['iife'] } },
    });
    const outputs = Array.isArray(result) ? result : [result];
    const chunk = outputs
        .flatMap((output) => ('output' in output ? output.output : []))
        .find((output) => output.type === 'chunk');
    if (!chunk || chunk.type !== 'chunk') throw new Error('Missing fixture bundle');
    await page.setContent('<div id="root"></div>');
    await page.addScriptTag({ content: chunk.code });
    for (const name of ['Panel Language', 'Preferred File Editor']) {
        const select = page.getByRole('combobox', { name, exact: true });
        await expect(select).toBeVisible();
        await page
            .locator('label')
            .filter({ hasText: new RegExp(`^${name}$`) })
            .click();
        await expect(select).toBeFocused();
    }
});

test('account preference labels focus their selects and preserve keyboard and pointer saving', async ({ page }) => {
    const url = process.env.ACCOUNT_PREFERENCE_DEMO_URL;
    test.skip(!url, 'Requires this task’s isolated Pi demo URL.');
    await page.goto(`${url}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.locator('button[type=submit]').click();
    await page.waitForURL(`${url}/`);
    await page.goto(`${url}/account`);

    const language = page.getByRole('combobox', { name: 'Panel Language', exact: true });
    const editor = page.getByRole('combobox', { name: 'Preferred File Editor', exact: true });
    await expect(language).toBeVisible();
    await expect(editor).toBeVisible();
    for (const name of ['Panel Language', 'Preferred File Editor']) {
        await page
            .locator('label')
            .filter({ hasText: new RegExp(`^${name}$`) })
            .click();
        await expect(page.getByRole('combobox', { name, exact: true })).toBeFocused();
        await page.keyboard.press('Escape');
    }

    const save = (path: string) =>
        page.waitForResponse((response) => response.url().endsWith(path) && response.request().method() === 'PUT');
    const originalEditor = await editor.inputValue();
    const changedEditor = originalEditor === 'cm' ? 'mo' : 'cm';
    const keyboardSave = save('/api/client/account/file-editor');
    await editor.focus();
    await editor.press(changedEditor === 'mo' ? 'ArrowDown' : 'ArrowUp');
    await editor.press('Enter');
    expect((await keyboardSave).ok()).toBe(true);
    await expect(editor).toHaveValue(changedEditor);
    const pointerSave = save('/api/client/account/file-editor');
    await editor.selectOption(originalEditor);
    expect((await pointerSave).ok()).toBe(true);

    const languageSave = save('/api/client/account/language');
    await language.selectOption('de');
    expect((await languageSave).ok()).toBe(true);
    const translatedLanguage = page.locator('select').filter({ has: page.locator('option[value="de"]') });
    await expect(translatedLanguage).toHaveValue('de');
    const restoreLanguage = save('/api/client/account/language');
    await translatedLanguage.selectOption('en');
    expect((await restoreLanguage).ok()).toBe(true);
    await page.reload();
    await expect(language).toHaveValue('en');
    await expect(editor).toHaveValue(originalEditor);
});
