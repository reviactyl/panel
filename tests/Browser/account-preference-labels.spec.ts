import { expect, test } from '@playwright/test';

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
