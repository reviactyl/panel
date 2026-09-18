@if (request()->is('preview/*'))
    <script>
        (() => {
            const status = @js((string) $status);
            const defaultColors = {
                403: '#f59e0b',
                404: '#3b82f6',
                500: '#ef4444',
            };

            window.addEventListener('message', (event) => {
                if (event.origin !== window.location.origin || event.source !== window.parent) return;
                if (event.data?.type !== 'reviactyl:designify-preview') return;

                const settings = event.data.settings?.errors?.[status];
                if (!settings) return;

                const title = document.querySelector('[data-designify-error-title]');
                const message = document.querySelector('[data-designify-error-message]');
                const button = document.querySelector('[data-designify-error-button]');
                const image = document.querySelector('[data-designify-error-image]');
                const code = document.querySelector('[data-designify-error-code]');

                if (title && typeof settings.title === 'string') title.textContent = settings.title;
                if (message && typeof settings.message === 'string') message.textContent = settings.message;
                if (button && typeof settings.button === 'string') button.textContent = settings.button;
                if (typeof settings.color === 'string' && settings.color.length > 0) {
                    document.documentElement.style.setProperty('--designify-error-color', settings.color);
                } else {
                    document.documentElement.style.setProperty('--designify-error-color', defaultColors[status]);
                }
                if (image) {
                    const hasImage = typeof settings.image === 'string' && settings.image.length > 0;
                    if (hasImage) {
                        image.src = settings.image;
                    } else {
                        image.removeAttribute('src');
                    }
                    image.classList.toggle('hidden', !hasImage);
                    if (code) {
                        code.classList.toggle('hidden', hasImage);
                        code.classList.toggle('flex', !hasImage);
                    }
                }
            });

            window.parent.postMessage({ type: 'reviactyl:designify-preview-ready' }, window.location.origin);
        })();
    </script>
@endif
