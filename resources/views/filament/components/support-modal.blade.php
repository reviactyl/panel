<div
    x-data="reviactylSupportPopup()"
    x-init="init()"
    x-cloak
    @keydown.escape.window="if (open) dismiss()"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-100 flex items-center justify-center overflow-y-auto bg-gray-950/45 p-4 sm:p-6"
        @click.self="dismiss()"
        aria-hidden="false"
    >
        <section
            x-show="open"
            x-trap.noscroll="open"
            x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
            x-transition:enter-start="translate-y-2 scale-[.99] opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="translate-y-2 scale-[.99] opacity-0"
            role="dialog"
            aria-modal="true"
            aria-labelledby="support-popup-title"
            aria-describedby="support-popup-description"
            tabindex="-1"
            class="relative my-auto w-full max-w-xl rounded-xl border border-gray-200 bg-white p-6 text-gray-950 shadow-xl shadow-gray-950/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 sm:p-8"
        >
            <button
                type="button"
                @click="dismiss()"
                aria-label="Close support message"
                class="absolute right-4 top-4 rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:hover:bg-gray-800 dark:hover:text-white"
            >
                <x-tabler-x class="h-5 w-5" />
            </button>

            <p class="mb-3 pr-8 text-xs font-semibold uppercase tracking-[0.14em] text-primary-600 dark:text-primary-400">
                A note from the developers
            </p>
            <h2 id="support-popup-title" class="mb-3 pr-8 text-2xl font-semibold leading-tight tracking-tight text-gray-950 dark:text-white sm:text-[1.7rem]">
                Enjoying Reviactyl?
            </h2>
            <p id="support-popup-description" class="mb-7 text-sm leading-6 text-gray-600 dark:text-gray-300">
                Reviactyl is built and maintained with a lot of time, effort and dedication. If it's been useful to you, consider supporting its continued development.
            </p>

            <div class="space-y-3">
                <a
                    href="https://github.com/reviactyl/panel"
                    target="_blank"
                    rel="noopener noreferrer"
                    @click="disableForever()"
                    class="group flex items-center gap-4 rounded-lg border border-gray-200 p-4 transition-colors hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-800/70"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100" aria-hidden="true">
                        <x-tabler-brand-github class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">Star us on GitHub</span>
                        <span class="mt-0.5 block text-sm text-gray-600 dark:text-gray-400">Help more developers discover Reviactyl.</span>
                    </span>
                    <x-tabler-arrow-up-right class="h-4 w-4 shrink-0 text-gray-400 transition group-hover:text-gray-700 dark:group-hover:text-gray-200" />
                </a>

                <a
                    href="https://github.com/sponsors/reviactyl"
                    target="_blank"
                    rel="noopener noreferrer"
                    @click="disableForever()"
                    class="group flex items-center gap-4 rounded-lg border border-gray-200 p-4 transition-colors hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-800/70"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400" aria-hidden="true">
                        <x-tabler-heart class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">Sponsor on GitHub</span>
                        <span class="mt-0.5 block text-sm text-gray-600 dark:text-gray-400">Your contribution helps keep the project moving.</span>
                    </span>
                    <x-tabler-arrow-up-right class="h-4 w-4 shrink-0 text-gray-400 transition group-hover:text-gray-700 dark:group-hover:text-gray-200" />
                </a>
                <a
                    href="https://ko-fi.com/reviactyl"
                    target="_blank"
                    rel="noopener noreferrer"
                    @click="disableForever()"
                    class="group flex items-center gap-4 rounded-lg border border-gray-200 p-4 transition-colors hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:border-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-800/70"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400" aria-hidden="true">
                        <x-tabler-coffee class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">Buy us a Ko-Fi (PayPal)</span>
                        <span class="mt-0.5 block text-sm text-gray-600 dark:text-gray-400">Your contribution helps keep the project moving.</span>
                    </span>
                    <x-tabler-arrow-up-right class="h-4 w-4 shrink-0 text-gray-400 transition group-hover:text-gray-700 dark:group-hover:text-gray-200" />
                </a>
            </div>

            <div class="mt-7 flex flex-col-reverse items-start justify-between gap-3 border-t border-gray-200 pt-5 dark:border-gray-700 sm:flex-row sm:items-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">Your support makes a difference.</p>
                <div class="flex items-center gap-4 self-end sm:self-auto">
                    <button type="button" @click="dismiss()" class="text-sm font-medium text-gray-600 underline-offset-4 hover:text-gray-950 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-gray-300 dark:hover:text-white">
                        Maybe later
                    </button>
                    <button type="button" @click="disableForever()" class="text-sm font-medium text-gray-500 underline-offset-4 hover:text-gray-800 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-gray-400 dark:hover:text-gray-200">
                        Don't show again
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    window.reviactylSupportPopup = window.reviactylSupportPopup || (() => ({
        open: false,
        storageKey: 'reviactyl.support-popup.v1',
        sessionKey: 'reviactyl.support-popup.shown',
        previousFocus: null,
        timer: null,
        destroyed: false,

        init() {
            let state;

            try {
                const stored = window.localStorage.getItem(this.storageKey);

                if (stored === null) {
                    state = {
                        firstSeenAt: Date.now(),
                        lastShownAt: null,
                        dismissCount: 0,
                        neverShowAgain: false,
                    };
                    window.localStorage.setItem(this.storageKey, JSON.stringify(state));
                    return;
                }

                state = JSON.parse(stored);
                if (!state || typeof state !== 'object' || Array.isArray(state)) return;

                state.dismissCount = Number.isInteger(state.dismissCount) && state.dismissCount >= 0 ? state.dismissCount : 0;
                state.neverShowAgain = state.neverShowAgain === true;
                state.lastShownAt = Number.isFinite(state.lastShownAt) && state.lastShownAt > 0 ? state.lastShownAt : null;

                if (!Number.isFinite(state.firstSeenAt) || state.firstSeenAt <= 0) {
                    state.firstSeenAt = Date.now();
                    this.save(state);
                    return;
                }

                if (state.neverShowAgain || window.sessionStorage.getItem(this.sessionKey) === '1') return;

                const dueAt = state.lastShownAt
                    ? state.lastShownAt + (state.dismissCount <= 1 ? 30 : 60) * 24 * 60 * 60 * 1000
                    : state.firstSeenAt + 7 * 24 * 60 * 60 * 1000;

                if (Date.now() < dueAt) return;

                this.previousFocus = document.activeElement;
                this.waitUntilSafe(dueAt);
            } catch (_) {
                // 
            }
        },

        save(state) {
            try {
                window.localStorage.setItem(this.storageKey, JSON.stringify(state));
            } catch (_) {
                // 
            }
        },

        read() {
            try {
                const state = JSON.parse(window.localStorage.getItem(this.storageKey) || '{}');
                return state && typeof state === 'object' && !Array.isArray(state) ? state : {};
            } catch (_) {
                return {};
            }
        },

        waitUntilSafe(dueAt) {
            const attempt = () => {
                this.timer = null;

                if (this.destroyed || this.open) return;

                const active = document.activeElement;
                const userIsWorking = active && (
                    active.matches('input, textarea, select, [contenteditable="true"]') ||
                    active.closest('.fi-modal, [role="dialog"]')
                );
                const anotherModalIsOpen = [...document.querySelectorAll('.fi-modal, [role="dialog"]')]
                    .some((element) => element.offsetParent !== null);
                const loading = document.querySelector('[wire\\:loading]:not([style*="display: none"]), [data-loading="true"]');

                if (Date.now() < dueAt || document.visibilityState !== 'visible' || userIsWorking || anotherModalIsOpen || loading) {
                    this.timer = window.setTimeout(attempt, 1500);
                    return;
                }

                try {
                    if (window.sessionStorage.getItem(this.sessionKey) === '1') return;
                    window.sessionStorage.setItem(this.sessionKey, '1');
                } catch (_) {
                    //
                }

                this.open = true;
                this.previousFocus = document.activeElement;
            };

            this.timer = window.setTimeout(attempt, 2000);
        },

        destroy() {
            this.destroyed = true;

            if (this.timer !== null) {
                window.clearTimeout(this.timer);
                this.timer = null;
            }
        },

        dismiss() {
            const state = this.read();
            state.lastShownAt = Date.now();
            state.dismissCount = (Number.isInteger(state.dismissCount) ? state.dismissCount : 0) + 1;
            state.neverShowAgain = false;
            this.save(state);
            this.close();
        },

        disableForever() {
            const state = this.read();
            state.lastShownAt = Date.now();
            state.dismissCount = Number.isInteger(state.dismissCount) ? state.dismissCount : 0;
            state.neverShowAgain = true;
            this.save(state);
            this.close();
        },

        close() {
            this.open = false;
            this.$nextTick(() => {
                if (this.previousFocus instanceof HTMLElement && document.contains(this.previousFocus)) {
                    this.previousFocus.focus({ preventScroll: true });
                }
            });
        },
    }));
</script>
