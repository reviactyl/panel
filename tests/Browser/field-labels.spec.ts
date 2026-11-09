import { expect, test } from '@playwright/test';

test('schedule and backup labels name and focus their fields', async ({ page }) => {
    const url = process.env.FIELD_LABEL_DEMO_URL;
    test.skip(!url, 'Requires this task’s isolated Pi demo URL.');
    await page.goto(`${url}/auth/login`);
    await page.locator('input[name=username]').fill('demo');
    await page.locator('input[name=password]').fill('demo');
    await page.locator('button[type=submit]').click();
    await page.waitForURL(`${url}/`);
    const servers = await (await page.request.get(`${url}/api/client`)).json();
    const server = servers.data.find(
        (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
    );
    expect(server).toBeTruthy();
    const base = `${url}/server/${server.attributes.identifier}`;
    await page.goto(`${base}/schedules`);
    await page.getByRole('button', { name: 'Create Schedule', exact: true }).click();
    const ids: string[] = [];
    for (const name of ['Schedule name', 'Minute', 'Hour', 'Day of month', 'Month', 'Day of week']) {
        const input = page.getByRole('textbox', { name, exact: true });
        await expect(input).toBeVisible();
        ids.push((await input.getAttribute('id'))!);
        await page
            .locator('label')
            .filter({ hasText: new RegExp(`^${name}$`) })
            .click();
        await expect(input).toBeFocused();
    }
    expect(ids.every(Boolean)).toBe(true);
    expect(new Set(ids).size).toBe(ids.length);
    const scheduleName = page.getByRole('textbox', { name: 'Schedule name', exact: true });
    await scheduleName.fill('Accessible schedule');
    await expect(scheduleName).toHaveAttribute('id', ids[0]!);

    await page.goto(`${base}/backups`);
    await page.getByRole('button', { name: 'Create server backup', exact: true }).click();
    const backupName = page.getByRole('textbox', { name: 'Backup name', exact: true });
    await expect(backupName).toBeVisible();
    await page
        .locator('label')
        .filter({ hasText: /^Backup name$/ })
        .click();
    await expect(backupName).toBeFocused();
    await backupName.fill('Accessible backup');
    await expect(backupName).toHaveValue('Accessible backup');
});

for (const width of [1280, 390]) {
    test(`account form labels name and focus their fields at ${width}px`, async ({ page }) => {
        const url = process.env.ACCOUNT_FORM_DEMO_URL;
        test.skip(!url, 'Requires this task’s isolated Pi demo URL.');
        await page.setViewportSize({ width, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        for (const [route, names] of [
            ['api', ['Description', 'Allowed IPS']],
            ['ssh', ['SSH Key Name', 'Public Key']],
        ] as const) {
            await page.goto(`${url}/account/${route}`);
            const ids: string[] = [];
            for (const name of names) {
                const input = page.getByRole('textbox', { name, exact: true });
                await expect(input).toBeVisible();
                const id = (await input.getAttribute('id'))!;
                expect(id).toBeTruthy();
                ids.push(id);
                await page
                    .getByText(name, { exact: true })
                    .filter({ hasNot: page.locator('input') })
                    .click();
                await expect(input).toBeFocused();
                await input.fill(`${name} value`);
                await expect(input).toHaveAttribute('id', id);
                await expect(input).toHaveValue(`${name} value`);
            }
            expect(new Set(ids).size).toBe(ids.length);
        }
    });
}
