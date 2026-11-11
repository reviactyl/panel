<style>
    :root {
        color-scheme: light;
        --error-background: {{ config('designify.color950L', '#fafafa') }};
        --error-text: {{ config('designify.color100L', '#18181b') }};
        --error-muted: {{ config('designify.color300L', '#3f3f46') }};
        --error-on-accent: #fff;
    }
    :root.dark {
        color-scheme: dark;
        --error-background: {{ config('designify.color950', '#09090b') }};
        --error-text: {{ config('designify.color100', '#f4f4f5') }};
        --error-muted: {{ config('designify.color300', '#d4d4d8') }};
    }
</style>
<script>
    (() => {
        const root = document.documentElement;
        const media = matchMedia('(prefers-color-scheme: dark)');
        const update = () => {
            let theme = 'system';
            try { theme = localStorage.getItem('theme') || 'system'; } catch {}
            root.classList.toggle('dark', theme === 'dark' || (theme === 'system' && media.matches));
            const hex = getComputedStyle(root).getPropertyValue('--designify-error-color').trim().replace('#', '');
            const expanded = hex.length === 3 ? hex.split('').map((value) => value + value).join('') : hex;
            if (!/^[\da-f]{6}$/i.test(expanded)) return;
            const channels = expanded.match(/.{2}/g).map((value) => parseInt(value, 16) / 255);
            const luminance = channels.map((value) => value <= 0.04045 ? value / 12.92 : ((value + 0.055) / 1.055) ** 2.4)
                .reduce((sum, value, index) => sum + value * [0.2126, 0.7152, 0.0722][index], 0);
            root.style.setProperty('--error-on-accent', luminance > 0.179 ? '#000' : '#fff');
        };
        update();
        media.addEventListener('change', update);
        window.addEventListener('designifychange', update);
    })();
</script>
