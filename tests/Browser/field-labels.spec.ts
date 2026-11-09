import { expect, test } from '@playwright/test';

for (const width of [1280, 390]) {
    test(`registration password labels focus their fields and preserve form behavior at ${width}px`, async ({
        page,
    }) => {
        const url = process.env.REGISTRATION_LABEL_DEMO_URL;
        test.skip(!url, 'Requires this task’s isolated Pi demo URL with CAPTCHA disabled.');
        await page.setViewportSize({ width, height: 900 });
        await page.goto(`${url}/auth/register`);
        const password = page.getByLabel('Password', { exact: true });
        const confirmation = page.getByLabel('Confirm Password', { exact: true });
        const ids: string[] = [];
        for (const [name, input] of [
            ['Password', password],
            ['Confirm Password', confirmation],
        ] as const) {
            await expect(input).toBeVisible();
            const id = (await input.getAttribute('id'))!;
            expect(id).toBeTruthy();
            ids.push(id);
            await page
                .locator('label')
                .filter({ hasText: new RegExp(`^${name}$`) })
                .click({ position: { x: 2, y: 2 } });
            await expect(input).toBeFocused();
            await expect(input).toHaveAttribute('type', 'password');
        }
        expect(new Set(ids).size).toBe(2);

        await page.getByLabel('First Name', { exact: true }).fill('Demo');
        await page.getByLabel('Last Name', { exact: true }).fill('User');
        await page.getByLabel('Email', { exact: true }).fill('registration-labels@example.com');
        await page.getByLabel('Username', { exact: true }).fill('registration-labels');
        await password.fill('short');
        await confirmation.fill('short');
        const requests: unknown[] = [];
        await page.route('**/auth/register', async (route) => {
            if (route.request().method() !== 'POST') return route.continue();
            requests.push(route.request().postDataJSON());
            await route.fulfill({
                status: 422,
                contentType: 'application/json',
                body: JSON.stringify({
                    errors: [{ detail: 'The username has already been taken.', meta: { source_field: 'username' } }],
                }),
            });
        });
        const submit = page.getByRole('button', { name: 'Register', exact: true });
        await submit.click();
        await expect(page.getByText('Password must be at least 8 characters', { exact: true })).toBeVisible();
        expect(requests).toHaveLength(0);
        await password.fill('ValidPassword123!');
        await confirmation.fill('DifferentPassword123!');
        await submit.click();
        await expect(page.getByText('Your password does not match.', { exact: true })).toBeVisible();
        expect(requests).toHaveLength(0);
        await confirmation.fill('ValidPassword123!');

        const reveal = page.locator('form button[type=button]').filter({ has: page.locator('svg') });
        for (const type of ['text', 'password']) {
            await reveal.click();
            for (const [index, input] of [password, confirmation].entries()) {
                await expect(input).toHaveAttribute('type', type);
                await expect(input).toHaveAttribute('id', ids[index]!);
                await expect(input).toHaveValue('ValidPassword123!');
            }
            await page
                .locator('label')
                .filter({ hasText: /^Password$/ })
                .click();
            await expect(password).toBeFocused();
            await page
                .locator('label')
                .filter({ hasText: /^Confirm Password$/ })
                .click();
            await expect(confirmation).toBeFocused();
        }
        expect(requests).toHaveLength(0);
        await submit.click();
        await expect(page.getByText('The username has already been taken.', { exact: true })).toBeVisible();
        await expect(submit).toBeEnabled();
        expect(requests).toEqual([
            {
                first_name: 'Demo',
                last_name: 'User',
                email: 'registration-labels@example.com',
                username: 'registration-labels',
                password: 'ValidPassword123!',
                password_confirmation: 'ValidPassword123!',
            },
        ]);
    });
}

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
