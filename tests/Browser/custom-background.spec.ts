import { execFileSync } from 'node:child_process';
import { resolve, sep } from 'node:path';
import { expect, test } from '@playwright/test';

test('Designify previews a continuous theme-aware wallpaper overlay without reloading', async ({ page }) => {
    const html = execFileSync('php', ['tests/Fixtures/auth-page.php', '/auth/login'], {
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
        if (url.pathname === '/') {
            return route.fulfill({ contentType: 'text/html', body: '<iframe src="/auth/login"></iframe>' });
        }
        if (url.pathname === '/auth/login') return route.fulfill({ contentType: 'text/html', body: html });
        if (url.pathname.startsWith('/locales/')) return route.fulfill({ json: {} });
        const path = resolve(publicPath, `.${url.pathname}`);
        if (!path.startsWith(publicPath + sep)) return route.abort();
        try {
            await route.fulfill({ path });
        } catch {
            await route.abort();
        }
    });
    await page.goto('http://panel.test/');
    const frame = page.frames().find((frame) => frame.url().endsWith('/auth/login'))!;
    await expect(frame.locator('button[type=submit]')).toBeVisible();
    await frame.evaluate(() => {
        const saved = document.createElement('style');
        saved.textContent =
            ':root { --background: url("/reviactyl/icon.png"); --background-overlay-color: 255 0 0; --background-overlay-opacity: 0.3; }';
        document.head.append(saved);
        window.dispatchEvent(new Event('designifychange'));
    });
    await expect(frame.locator('html')).toHaveAttribute('data-wallpaper', '');
    for (const background of ['', '/reviactyl/icon.png', 'none', '/reviactyl/logo.png']) {
        await page.evaluate((background) => {
            document
                .querySelector('iframe')!
                .contentWindow!.postMessage(
                    { type: 'reviactyl:designify-preview', settings: { background } },
                    window.location.origin,
                );
        }, background);
        const hasWallpaper = background.startsWith('/');
        await expect
            .poll(() => frame.locator('html').evaluate((root) => root.hasAttribute('data-wallpaper')))
            .toBe(hasWallpaper);
        await expect
            .poll(() =>
                frame.locator('html').evaluate((root) => getComputedStyle(root).getPropertyValue('--background')),
            )
            .toBe(hasWallpaper ? `url("${background}")` : 'none');
    }
    const update = async (settings: Record<string, unknown>) => {
        await page.evaluate((settings) => {
            document
                .querySelector('iframe')!
                .contentWindow!.postMessage({ type: 'reviactyl:designify-preview', settings }, window.location.origin);
        }, settings);
    };
    const backgroundImage = () => frame.locator('body').evaluate((body) => getComputedStyle(body).backgroundImage);
    const username = frame.locator('input[name=username]');
    const cardBackground = () =>
        username.evaluate((input) => getComputedStyle(input.closest('.bg-gray-900')!).backgroundImage);
    for (const scope of ['all', 'page', 'all']) {
        await update({ backgroundScope: scope });
        await expect.poll(cardBackground).toContain(scope === 'all' ? '/reviactyl/logo.png' : 'none');
        await expect
            .poll(() => username.evaluate((input) => getComputedStyle(input).backgroundImage))
            .toContain(scope === 'all' ? '/reviactyl/logo.png' : 'none');
        await expect(frame.locator('button[type=submit]')).toHaveCSS('background-image', 'none');
    }
    await frame.evaluate(() => {
        const sample = document.createElement('div');
        sample.innerHTML = `<div id="translucent" class="bg-gray-900/40">Surface</div>
            <div id="backdrop" data-wallpaper-backdrop class="bg-gray-900/40">Backdrop</div>
            <input id="checkbox" class="bg-gray-800" type="checkbox">`;
        document.body.append(sample);
    });
    await expect(frame.locator('#translucent')).toHaveCSS('background-attachment', 'fixed, fixed, fixed');
    await expect(frame.locator('#backdrop')).toHaveCSS('background-image', 'none');
    await expect(frame.locator('#checkbox')).toHaveCSS('background-image', 'none');
    await update({ background: '' });
    await expect.poll(cardBackground).toBe('none');
    await update({ background: '/reviactyl/logo.png', backgroundScope: 'page' });
    await expect(frame.locator('html')).toHaveAttribute('data-wallpaper', '');
    await update({ backgroundOverlay: true, backgroundOverlayOpacity: 60, backgroundOverlayColor: '#123456' });
    await expect.poll(backgroundImage).toContain('rgba(18, 52, 86, 0.6)');
    await expect.poll(backgroundImage).toContain('/reviactyl/logo.png');
    await update({ backgroundOverlayColor: '' });
    for (const theme of ['light', 'dark']) {
        await frame.evaluate((theme) => {
            localStorage.setItem('theme', theme);
            window.dispatchEvent(new Event('themechange'));
        }, theme);
        await expect
            .poll(backgroundImage)
            .toContain(theme === 'light' ? 'rgba(250, 250, 250, 0.6)' : 'rgba(9, 9, 11, 0.6)');
    }
    for (const [opacity, alpha] of [
        [-10, 0],
        [100, 1],
        [150, 1],
        [0, 0],
        [85, 0.85],
    ]) {
        await update({ backgroundOverlayOpacity: opacity });
        await expect
            .poll(() =>
                frame
                    .locator('html')
                    .evaluate((root) => getComputedStyle(root).getPropertyValue('--background-overlay-opacity')),
            )
            .toBe(String(alpha));
    }
    await update({ backgroundOverlay: false });
    await expect.poll(backgroundImage).toContain('rgba(9, 9, 11, 0)');
    await update({ backgroundOverlay: true });
    await expect.poll(backgroundImage).toContain('rgba(9, 9, 11, 0.85)');
    for (const width of [390, 1280]) {
        await page.locator('iframe').evaluate((frame, width) => {
            frame.style.width = `${width}px`;
            frame.style.height = '900px';
        }, width);
        const surface = await frame.locator('body').evaluate((body) => {
            const style = getComputedStyle(body);
            return {
                size: style.backgroundSize,
                position: style.backgroundPosition,
                attachment: style.backgroundAttachment,
                width: body.scrollWidth,
                viewport: innerWidth,
            };
        });
        expect(surface).toMatchObject({
            size: 'cover, cover',
            position: '50% 50%, 50% 50%',
            attachment: 'fixed, fixed',
        });
        expect(surface.width).toBeLessThanOrEqual(surface.viewport);
    }
});

test('wallpaper surfaces preserve the selected color and keyboard behavior of real switches', async ({ page }) => {
    const html = execFileSync('php', ['tests/Fixtures/auth-page.php', '/', 'admin'], {
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
        if (url.pathname === '/') return route.fulfill({ contentType: 'text/html', body: html });
        if (url.pathname.startsWith('/locales/')) return route.fulfill({ json: {} });
        if (url.pathname.startsWith('/api/')) {
            return route.fulfill({
                json: {
                    data: [],
                    meta: { pagination: { total: 0, count: 0, per_page: 50, current_page: 1, total_pages: 1 } },
                },
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
    await page.goto('http://panel.test/');
    const checkbox = page.locator('input[name=show_all_servers]');
    await expect(checkbox).toBeAttached();
    const id = await checkbox.getAttribute('id');
    const track = page.locator(`label[for="${id}"]`).first();
    await page.evaluate(() => {
        window.PanelConfiguration = { ...window.PanelConfiguration, backgroundScope: 'all' };
        document.documentElement.style.setProperty('--background', 'url("/reviactyl/icon.png")');
        window.dispatchEvent(new Event('designifychange'));
    });
    await expect(page.locator('html')).toHaveAttribute('data-wallpaper-surfaces', '');
    for (const theme of ['light', 'dark']) {
        await page.evaluate((theme) => {
            localStorage.setItem('theme', theme);
            window.dispatchEvent(new Event('themechange'));
        }, theme);
        await expect(track).toHaveCSS('background-image', 'none');
        await track.click();
        await expect(checkbox).toBeChecked();
        const primary = await page
            .locator('html')
            .evaluate(
                (root) =>
                    `rgb(${getComputedStyle(root).getPropertyValue('--color-primary').trim().split(/\s+/).join(', ')})`,
            );
        await expect(track).toHaveCSS('background-color', primary);
        await expect(track).toHaveCSS('background-image', 'none');
        await checkbox.focus();
        await page.keyboard.press('Space');
        await expect(checkbox).not.toBeChecked();
        await expect(track).not.toHaveCSS('background-color', primary);
    }
});
