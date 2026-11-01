import { expect, test } from '@playwright/test';

const baseURL = process.env.EMAIL_DEMO_URL;
test.skip(!baseURL, 'Requires this task’s isolated Pi demo URL.');

test('Designify email canvas, HTML, placeholders and header actions share one draft', async ({ page }) => {
    const errors: string[] = [];
    page.on('pageerror', (error) => errors.push(error.message));
    await page.goto(`${baseURL}/auth/login`);
    await page.locator('input[name=username]').fill('demo-admin');
    await page.locator('input[name=password]').fill('demo-admin');
    await page.getByRole('button', { name: 'Login', exact: true }).click();
    await page.waitForURL(`${baseURL}/`);
    await page.evaluate(async () => {
        const token =
            document.cookie
                .split('; ')
                .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
                ?.split('=')
                .slice(1)
                .join('=') || '';
        await fetch('/api/client/account/file-editor', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(token),
                Accept: 'application/json',
            },
            body: JSON.stringify({ fileEditor: 'cm' }),
        });
    });
    await page.goto(`${baseURL}/admin/designify`);
    await page.getByRole('tab', { name: 'Emails', exact: true }).click();
    await page.getByRole('combobox', { name: 'Email Type', exact: true }).selectOption('MailTested');
    await expect(page.getByLabel('Subject', { exact: true })).toHaveValue('Panel Test Message');
    await page.getByRole('button', { name: 'Email HTML', exact: true }).click();
    await expect(page.getByRole('dialog')).toHaveCSS('animation-name', 'email-dialog-enter');
    const source =
        '<h2>REV-10 heading</h2><p>Hello {{ recipient_name }}</p><table><tbody><tr><td>Custom layout</td></tr></tbody></table>';
    const htmlCode = page.locator('.email-html-editor .cm-content');
    await expect(htmlCode).toBeVisible();
    await htmlCode.fill(source);
    await page.getByRole('dialog').getByRole('button', { name: 'Save changes', exact: true }).click();
    await page.getByRole('button', { name: 'Email HTML', exact: true }).click();
    await htmlCode.fill('<p>Cancelled edit</p>');
    await page.getByRole('dialog').getByRole('button', { name: 'Cancel', exact: true }).click();
    await expect(page.getByRole('dialog')).toHaveAttribute('data-closing', 'true');
    await expect(page.getByRole('dialog')).toHaveCSS('animation-name', 'email-dialog-leave');
    await expect(page.getByRole('dialog')).toHaveCount(0);
    const canvas = page.frameLocator('iframe[title="Rich Text Email Editor"]');
    await expect(canvas.getByText('REV-10 heading')).toBeVisible();
    await expect(canvas.getByText('Hello {{ recipient_name }}')).toBeVisible();
    await canvas.locator('[data-email-content]').click();
    await canvas.locator('h2').fill('REV-10 canvas edit');
    await expect.poll(() => canvas.locator('[data-email-content]').innerHTML()).toMatch(/REV-10 canvas edit/);
    await expect.poll(() => canvas.locator('[data-email-content]').innerHTML()).toMatch(/{{ recipient_name }}/);
    await canvas.locator('h2').evaluate((heading) => {
        const selection = heading.ownerDocument.getSelection()!;
        selection.selectAllChildren(heading);
        heading.ownerDocument.dispatchEvent(new Event('selectionchange'));
    });
    await page.getByRole('textbox', { name: 'Font Size', exact: true }).fill('24');
    await page.getByLabel('Text Color', { exact: true }).click();
    await expect.poll(() => canvas.locator('[data-email-content]').innerHTML()).toMatch(/font-size: 24px/);
    await page.getByLabel('Text Color', { exact: true }).fill('#ff0000');
    await expect.poll(() => canvas.locator('[data-email-content]').innerHTML()).toMatch(/color: rgb\(255, 0, 0\)/);
    await canvas.locator('h2').evaluate((heading) => {
        const selection = heading.ownerDocument.getSelection()!;
        selection.selectAllChildren(heading);
        selection.collapseToEnd();
        heading.ownerDocument.dispatchEvent(new Event('selectionchange'));
    });
    await page.getByRole('textbox', { name: 'Font Size', exact: true }).fill('19');
    await page.getByLabel('Subject', { exact: true }).click();
    await page.keyboard.press('Tab');
    await page.locator('iframe[title="Rich Text Email Editor"]').evaluate((frame: HTMLIFrameElement) => {
        const content = frame.contentDocument!.querySelector<HTMLElement>('[data-email-content]')!;
        content.focus();
    });
    await page.keyboard.type(' typed');
    await page.getByRole('button', { name: 'Panel Name', exact: false }).click();
    await page.keyboard.type('-after');
    await expect
        .poll(() => canvas.locator('[data-email-content]').innerHTML())
        .toMatch(/typed.*{{ panel_name }}-after/);
    expect(
        await canvas.locator('h2').evaluate((heading) => {
            const walker = heading.ownerDocument.createTreeWalker(heading, NodeFilter.SHOW_TEXT);
            while (walker.nextNode()) {
                const text = walker.currentNode;
                if (text.textContent?.includes(' typed'))
                    return heading.ownerDocument.defaultView!.getComputedStyle(text.parentElement!).fontSize;
            }
            return null;
        }),
    ).toBe('19px');
    await expect(page.getByRole('button', { name: 'Button text', exact: false })).toHaveCount(0);
    await page.getByRole('button', { name: 'Image', exact: true }).click();
    await page.getByRole('button', { name: 'Panel logo', exact: true }).click();
    await expect(page.getByLabel('URL', { exact: true })).toHaveValue(`${baseURL}/reviactyl/logo.png`);
    await page.getByLabel('URL', { exact: true }).fill(`${baseURL}/reviactyl/logo.png`);
    await page.getByRole('dialog').getByRole('button', { name: 'Insert', exact: true }).click();
    await expect(page.getByText('Enter an HTTPS URL', { exact: true })).toHaveCount(0);
    const size = page.getByRole('textbox', { name: 'Font Size', exact: true });
    await size.fill('20');
    await page.getByLabel('Subject', { exact: true }).click();
    const plus = page.getByRole('button', { name: 'Increase Font Size', exact: true });
    await plus.hover();
    await page.mouse.down();
    await expect.poll(async () => Number(await size.inputValue())).toBeGreaterThan(23);
    await page.mouse.up();
    const stoppedSize = await size.inputValue();
    await expect(size).toHaveValue(stoppedSize);
    const variable = canvas.locator('p').filter({ hasText: 'Hello {{ recipient_name }}' });
    await variable.evaluate((p) => {
        const text = p.firstChild!;
        const selection = p.ownerDocument.getSelection()!;
        selection.setBaseAndExtent(text, 6, text, text.textContent!.length);
    });
    await page.keyboard.type('replacement');
    await expect(variable).toHaveCount(0);
    await expect(canvas.getByText('Hello replacement', { exact: true })).toBeVisible();
    await canvas.getByText('Hello replacement', { exact: true }).evaluate((p) => {
        p.ownerDocument.getSelection()!.selectAllChildren(p);
        p.ownerDocument.dispatchEvent(new Event('selectionchange'));
    });
    await page.getByRole('button', { name: 'Link', exact: true }).click();
    await expect(page.getByRole('dialog')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Panel logo', exact: true })).toHaveCount(0);
    await page.getByLabel('URL', { exact: true }).fill('https://example.com/custom');
    await page.getByRole('dialog').getByRole('button', { name: 'Insert', exact: true }).click();
    await expect(canvas.getByRole('link', { name: 'Hello replacement' })).toHaveAttribute(
        'href',
        'https://example.com/custom',
    );
    await expect(size).toHaveValue(stoppedSize);

    let warningType = '';
    page.once('dialog', async (dialog) => {
        warningType = dialog.type();
        await dialog.dismiss();
    });
    await page.locator('.designify-editor__back').click({ noWaitAfter: true });
    await expect.poll(() => warningType).toBe('beforeunload');
    await page.getByRole('button', { name: 'Save changes', exact: true }).click();
    await expect(page.getByText('Email saved.', { exact: true })).toBeVisible();
    await page.reload();
    await page.getByRole('combobox', { name: 'Email Type', exact: true }).selectOption('MailTested');
    await expect(canvas.getByText('REV-10 canvas edit')).toBeVisible();
    await expect(canvas.locator('h2')).toContainText('{{ panel_name }}');
    await expect(page.getByRole('button', { name: 'Visual blocks', exact: true })).toHaveCount(0);
    await expect(
        page.locator('.email-template-editor__preview').getByRole('button', { name: 'Settings', exact: true }),
    ).toHaveCount(0);
    const inspector = await page.locator('.email-template-editor__inspector').boundingBox();
    const original = await page.locator('.designify-editor__controls').boundingBox();
    expect(Math.round(inspector!.x + inspector!.width)).toBe(Math.round(original!.x + original!.width));
    await page.screenshot({ path: '/tmp/rev10-design-desktop.png', fullPage: true });
    await page.setViewportSize({ width: 390, height: 844 });
    await page.screenshot({ path: '/tmp/rev10-design-mobile.png', fullPage: true });
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth)).toBe(true);
    expect(errors).toEqual([]);
    await page.getByRole('button', { name: 'Reset', exact: true }).click();
    const resetDialog = page.getByRole('alertdialog', { name: 'Reset Designify?' });
    await expect(resetDialog.getByRole('heading', { name: 'Reset Designify?' })).toBeVisible();
    await Promise.all([
        page.waitForEvent('load'),
        resetDialog.getByRole('button', { name: 'Confirm', exact: true }).click(),
    ]);
    await expect(page.getByRole('combobox', { name: 'Email Type', exact: true })).toBeEnabled();
    await page.getByRole('combobox', { name: 'Email Type', exact: true }).selectOption('MailTested');
    await expect(canvas.getByText('This is a test of the Reviactyl mail system. You’re good to go!')).toBeVisible();
});

test('HTML popup uses Monaco when selected and formats the source', async ({ page }) => {
    await page.goto(`${baseURL}/auth/login`);
    await page.locator('input[name=username]').fill('demo-admin');
    await page.locator('input[name=password]').fill('demo-admin');
    await page.getByRole('button', { name: 'Login', exact: true }).click();
    await page.waitForURL(`${baseURL}/`);
    await page.goto(`${baseURL}/admin/designify?tab=emails`);
    await page.getByRole('tab', { name: 'Emails', exact: true }).click();
    const host = page.locator('.email-html-editor');
    const response = await page.evaluate(async () => {
        const token =
            document.cookie
                .split('; ')
                .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
                ?.split('=')
                .slice(1)
                .join('=') || '';
        const response = await fetch('/api/client/account/file-editor', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(token),
                Accept: 'application/json',
            },
            body: JSON.stringify({ fileEditor: 'mo' }),
        });
        return response.status;
    });
    expect(response).toBe(200);
    await page.getByRole('button', { name: 'Email HTML', exact: true }).click();
    await expect(host.locator('.monaco-editor')).toBeVisible({ timeout: 20000 });
    await expect(host.locator('.view-line')).not.toHaveCount(1);
    await expect(host.locator('.view-lines')).toContainText('recipient_name');
    await page.getByRole('dialog').getByRole('button', { name: 'Cancel', exact: true }).click();
    await expect(page.getByRole('dialog')).toHaveCount(0);
    await page.evaluate(async () => {
        const token =
            document.cookie
                .split('; ')
                .find((cookie) => cookie.startsWith('XSRF-TOKEN='))
                ?.split('=')
                .slice(1)
                .join('=') || '';
        await fetch('/api/client/account/file-editor', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(token),
                Accept: 'application/json',
            },
            body: JSON.stringify({ fileEditor: 'cm' }),
        });
    });
});

test('typing inherits selected text and respects explicit formatting', async ({ page }) => {
    await page.goto(`${baseURL}/auth/login`);
    await page.locator('input[name=username]').fill('demo-admin');
    await page.locator('input[name=password]').fill('demo-admin');
    await page.getByRole('button', { name: 'Login', exact: true }).click();
    await page.waitForURL(`${baseURL}/`);
    await page.goto(`${baseURL}/admin/designify?tab=emails`);
    await page.getByRole('tab', { name: 'Emails', exact: true }).click();
    await page.getByRole('combobox', { name: 'Email Type', exact: true }).selectOption('MailTested');
    await page.getByRole('button', { name: 'Email HTML', exact: true }).click();
    await page
        .locator('.cm-content')
        .fill(
            '<p><span style="color:#ff0000;font-size:12px">Previous </span><span style="color:#0000ff;font-size:28px;font-weight:bold">Selected</span></p>',
        );
    await page.getByRole('dialog').getByRole('button', { name: 'Save changes', exact: true }).click();
    const canvas = page.frameLocator('iframe[title="Rich Text Email Editor"]');
    await expect(canvas.getByText('Selected', { exact: true })).toBeVisible();
    await canvas.getByText('Selected', { exact: true }).evaluate((el) => {
        el.closest<HTMLElement>('[data-email-content]')!.focus();
        el.ownerDocument.getSelection()!.selectAllChildren(el);
        el.ownerDocument.dispatchEvent(new Event('selectionchange'));
    });
    await page.keyboard.type('Replacement');
    const replacement = canvas.getByText('Replacement', { exact: true });
    await expect(replacement).toHaveCSS('color', 'rgb(0, 0, 255)');
    await expect(replacement).toHaveCSS('font-size', '28px');
    await expect(replacement).toHaveCSS('font-weight', '700');
    await page.getByLabel('Text Color', { exact: true }).fill('#00ff00');
    await page.keyboard.type('Green');
    const green = canvas.locator('span').filter({ hasText: /Green/ }).last();
    await expect(green).toHaveCSS('color', 'rgb(0, 255, 0)');
    await expect(green).toHaveCSS('font-size', '28px');
    await page.getByRole('textbox', { name: 'Font Size', exact: true }).fill('32');
    await page.getByRole('textbox', { name: 'Font Size', exact: true }).press('Enter');
    await page.keyboard.type('Larger');
    await expect(
        canvas
            .locator('span')
            .filter({ hasText: /Larger/ })
            .last(),
    ).toHaveCSS('font-size', '32px');
    await expect(
        canvas
            .locator('span')
            .filter({ hasText: /Larger/ })
            .last(),
    ).toHaveCSS('color', 'rgb(0, 255, 0)');
    await page.getByRole('button', { name: 'Bold', exact: true }).click();
    await page.keyboard.type('Normal');
    const normal = canvas
        .locator('span')
        .filter({ hasText: /Normal/ })
        .last();
    await expect(normal).toHaveCSS('font-weight', '400');
    await expect(normal).toHaveCSS('color', 'rgb(0, 255, 0)');
    await expect(normal).toHaveCSS('font-size', '32px');
});
