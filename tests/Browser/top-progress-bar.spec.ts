import { expect, test } from '@playwright/test';
import { writeFile } from 'node:fs/promises';

for (const width of [1280, 390]) {
    test(`route progress stays above navigation at 2px and completes at ${width}px`, async ({ page }, testInfo) => {
        const url = process.env.PROGRESS_BAR_DEMO_URL;
        test.skip(
            !url,
            'Requires an isolated Panel and Agent demo with a visible header layout (classic, compact, or accent).',
        );
        await page.setViewportSize({ width, height: 900 });
        await page.goto(`${url}/auth/login`);
        await page.locator('input[name=username]').fill('demo');
        await page.locator('input[name=password]').fill('demo');
        await page.getByRole('button', { name: 'Login', exact: true }).click();
        await page.waitForURL(`${url}/`);
        await expect(page.locator('.fixed.h-16')).toBeVisible();
        const servers = await (await page.request.get(`${url}/api/client`)).json();
        const server = servers.data.find(
            (item: { attributes: { name: string } }) => item.attributes.name === 'Demo Playground',
        );
        expect(server).toBeTruthy();
        const path = `/server/${server.attributes.identifier}/`;
        const fill = page.locator('[class~="transition-[width]"]');
        await expect(fill).toHaveCount(0);
        let release!: () => void;
        const held = new Promise<void>((resolve) => (release = resolve));
        let arrived!: () => void;
        const received = new Promise<void>((resolve) => (arrived = resolve));
        await page.route(`**/api/client/servers/${server.attributes.identifier}?*`, async (route) => {
            const response = await route.fetch();
            arrived();
            await held;
            await route.fulfill({ response });
        });
        await page.locator(`a[href="${path}"]`).first().click();
        await received;
        await expect(fill).toHaveCount(1);
        const completion = page.evaluate(async () => {
            let reachedFullWidth = false;
            while (true) {
                const bar = document.querySelector('[class~="transition-[width]"]');
                if (!bar) return reachedFullWidth;
                reachedFullWidth ||= bar.getBoundingClientRect().width === innerWidth;
                await new Promise(requestAnimationFrame);
            }
        });
        try {
            await expect(fill.locator('..')).toHaveCSS('opacity', '1');
            const geometry = await fill.evaluate((bar) => {
                const animation = bar.parentElement!;
                const container = animation.parentElement!;
                const navigation = document.querySelector('.fixed.h-16')!;
                const rect = bar.getBoundingClientRect();
                const previous = container.style.pointerEvents;
                container.style.pointerEvents = 'auto';
                const paintedAboveNavigation = document.elementFromPoint(rect.width / 2, 1) === bar;
                container.style.pointerEvents = previous;
                return {
                    height: rect.height,
                    animationHeight: animation.getBoundingClientRect().height,
                    top: rect.top,
                    left: rect.left,
                    width: rect.width,
                    viewportWidth: innerWidth,
                    containerWidth: container.getBoundingClientRect().width,
                    navigationTop: navigation.getBoundingClientRect().top,
                    navigationHeight: navigation.getBoundingClientRect().height,
                    navigationReceivesPointer: navigation.contains(document.elementFromPoint(rect.width / 2, 1)),
                    navigationZIndex: getComputedStyle(navigation).zIndex,
                    paintedAboveNavigation,
                    pointerEvents: getComputedStyle(container).pointerEvents,
                };
            });
            await writeFile(testInfo.outputPath('geometry.json'), JSON.stringify(geometry, null, 2));
            await testInfo.attach('progress-geometry', {
                body: JSON.stringify(geometry, null, 2),
                contentType: 'application/json',
            });
            await page.screenshot({ path: testInfo.outputPath('progress.png') });
            expect(geometry.height).toBe(2);
            expect(geometry.animationHeight).toBe(2);
            expect(geometry.top).toBe(0);
            expect(geometry.left).toBe(0);
            expect(geometry.containerWidth).toBe(geometry.viewportWidth);
            expect(geometry.width).toBeGreaterThan(0);
            expect(geometry.width).toBeLessThan(geometry.viewportWidth);
            expect(geometry.navigationTop).toBe(0);
            expect(geometry.navigationHeight).toBe(64);
            expect(geometry.navigationReceivesPointer).toBe(true);
            expect(geometry.paintedAboveNavigation).toBe(true);
            expect(geometry.pointerEvents).toBe('none');
            const before = await fill.evaluate((bar) => bar.getBoundingClientRect().width);
            await expect.poll(() => fill.evaluate((bar) => bar.getBoundingClientRect().width)).toBeGreaterThan(before);
        } finally {
            release();
        }
        expect(await completion).toBe(true);
        await expect(fill).toHaveCount(0);
        await expect(page.getByRole('button', { name: 'Stop', exact: true })).toBeEnabled();
        await page.locator('.fixed.h-16 a[href="/"]').click();
        await page.waitForURL(`${url}/`);
        await expect(page.locator(`a[href="${path}"]`).first()).toBeVisible();
        await expect(fill).toHaveCount(0);
    });
}
