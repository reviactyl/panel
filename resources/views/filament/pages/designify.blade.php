<x-filament-panels::page>
    <div
        class="designify-editor"
        wire:ignore.self
        x-data="{
            draft: @js($this->getPreviewSettings()),
            savedSettings: @js($this->getPreviewSettings()),
            isDirty: false,
            isDesktop: true,
            workspacePanel: 'preview',
            previewMode: 'home',
            viewport: sessionStorage.getItem('designify-preview-viewport') || 'desktop',
            mediaQuery: null,
            syncMedia: null,
            handleBeforeUnload: null,
            handlePreviewReady: null,
            previewUrls: {
                home: @js(url('/')),
                403: @js(url('/preview/403')),
                404: @js(url('/preview/404')),
                500: @js(url('/preview/500')),
            },
            init() {
                this.mediaQuery = window.matchMedia('(max-width: 70rem)');
                this.syncMedia = () => this.isDesktop = !this.mediaQuery.matches;
                this.handleBeforeUnload = (event) => {
                    if (!this.isDirty || window.__designifySkipBeforeUnload) return;
                    event.preventDefault();
                    event.returnValue = '';
                };
                this.handlePreviewReady = (event) => {
                    if (event.origin !== window.location.origin) return;
                    if (event.source !== this.$refs.previewFrame?.contentWindow) return;
                    if (event.data?.type !== 'reviactyl:designify-preview-ready') return;
                    this.sendPreview();
                };

                this.syncMedia();
                this.mediaQuery.addEventListener('change', this.syncMedia);
                window.addEventListener('beforeunload', this.handleBeforeUnload);
                window.addEventListener('message', this.handlePreviewReady);
                this.$nextTick(() => this.sendPreview());
            },
            destroy() {
                this.mediaQuery?.removeEventListener('change', this.syncMedia);
                window.removeEventListener('beforeunload', this.handleBeforeUnload);
                window.removeEventListener('message', this.handlePreviewReady);
            },
            sendPreview(settings = this.draft) {
                const payload = JSON.parse(JSON.stringify(settings));
                this.draft = payload;
                this.$refs.previewFrame?.contentWindow?.postMessage({
                    type: 'reviactyl:designify-preview',
                    settings: payload,
                }, window.location.origin);
            },
            updateDirty(settings = this.draft) {
                this.isDirty = JSON.stringify(settings) !== JSON.stringify(this.savedSettings);
            },
            setPreview(mode) {
                this.previewMode = mode;
                this.$refs.previewFrame.src = this.previewUrls[mode];
            },
            setViewport(viewport) {
                this.viewport = viewport;
                sessionStorage.setItem('designify-preview-viewport', viewport);
            },
            refreshPreview() {
                const url = new URL(this.previewUrls[this.previewMode]);
                url.searchParams.set('designify-preview', Date.now().toString());
                this.$refs.previewFrame.src = url.toString();
            },
            openPreview() {
                if (this.isDirty) return;
                window.open(this.previewUrls[this.previewMode], '_blank', 'noopener,noreferrer');
            },
        }"
        x-on:input.capture="isDirty = true"
        x-on:change.capture="isDirty = true"
        x-on:designify-preview-updated.window="sendPreview($event.detail.settings); updateDirty($event.detail.settings)"
        x-on:designify-saved.window="draft = $event.detail.settings; savedSettings = JSON.parse(JSON.stringify($event.detail.settings)); isDirty = false"
        x-on:designify-reset.window="isDirty = false; window.__designifySkipBeforeUnload = true"
        x-on:reload-iframe.window="refreshPreview()"
    >
        <header class="designify-editor__header">
            <div class="designify-editor__brand">
                <a
                    href="{{ url('/admin') }}"
                    class="designify-editor__back"
                    aria-label="Return to administration"
                >
                    <x-tabler-arrow-left />
                </a>
                <div class="designify-editor__brand-copy">
                    <p class="designify-editor__brand-title">Designify</p>
                </div>
            </div>

            <div class="designify-editor__mobile-switcher" aria-label="Editor panel">
                <button
                    type="button"
                    class="designify-editor__tool-button"
                    x-bind:aria-pressed="workspacePanel === 'settings'"
                    x-on:click="workspacePanel = 'settings'"
                >
                    <x-tabler-adjustments-horizontal />
                    <span>Settings</span>
                </button>
                <button
                    type="button"
                    class="designify-editor__tool-button"
                    x-bind:aria-pressed="workspacePanel === 'preview'"
                    x-on:click="workspacePanel = 'preview'"
                >
                    <x-tabler-eye />
                    <span>Preview</span>
                </button>
            </div>

            <div class="designify-editor__actions">
                <span class="designify-editor__save-state" x-bind:data-dirty="isDirty.toString()" aria-live="polite">
                    <span class="designify-editor__save-state-dot" aria-hidden="true"></span>
                    <span x-text="isDirty ? 'Unsaved' : 'Saved'"></span>
                </span>
                <x-filament::button
                    color="gray"
                    icon="tabler-restore"
                    data-secondary-action="true"
                    wire:click="mountAction('reset')"
                    :disabled="config('panel.load_environment_only')"
                >
                    Reset
                </x-filament::button>
                <x-filament::button
                    icon="tabler-device-floppy"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    :disabled="config('panel.load_environment_only')"
                >
                    <span wire:loading.remove wire:target="save">Save changes</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </x-filament::button>
            </div>
        </header>

        <main class="designify-editor__workspace">
            <aside
                class="designify-editor__panel designify-editor__controls"
                aria-label="Designify settings"
                x-show="isDesktop || workspacePanel === 'settings'"
                x-cloak
            >
                <div class="designify-editor__controls-form">
                    {{ $this->form }}
                </div>
            </aside>

            <section
                class="designify-editor__panel designify-editor__preview"
                aria-label="Live panel preview"
                x-show="isDesktop || workspacePanel === 'preview'"
                x-cloak
            >
                <div class="designify-editor__preview-bar">
                    <div class="designify-editor__preview-tools">
                        <div class="designify-editor__page-switcher" aria-label="Preview page">
                            <button type="button" class="designify-editor__tool-button" x-bind:aria-pressed="previewMode === 'home'" x-on:click="setPreview('home')">
                                <x-tabler-home />
                                <span>Panel</span>
                            </button>
                            @foreach ([403, 404, 500] as $status)
                                <button
                                    type="button"
                                    class="designify-editor__tool-button"
                                    x-bind:aria-pressed="previewMode === '{{ $status }}'"
                                    x-on:click="setPreview('{{ $status }}')"
                                >
                                    <span>{{ $status }}</span>
                                </button>
                            @endforeach
                            <button type="button" class="designify-editor__tool-button" aria-label="Reload preview" x-on:click="refreshPreview()">
                                <x-tabler-refresh />
                            </button>
                            <button
                                type="button"
                                class="designify-editor__tool-button"
                                aria-label="Open saved preview in a new tab"
                                x-bind:disabled="isDirty"
                                x-bind:title="isDirty ? 'Save changes before opening a new tab' : 'Open saved preview in a new tab'"
                                x-on:click="openPreview()"
                            >
                                <x-tabler-external-link />
                            </button>
                        </div>

                        <div class="designify-editor__device-switcher" aria-label="Preview viewport">
                            <button type="button" class="designify-editor__tool-button" x-bind:aria-pressed="viewport === 'desktop'" x-on:click="setViewport('desktop')">
                                <x-tabler-device-desktop />
                                <span>Desktop</span>
                            </button>
                            <button type="button" class="designify-editor__tool-button" x-bind:aria-pressed="viewport === 'tablet'" x-on:click="setViewport('tablet')">
                                <x-tabler-device-tablet />
                                <span>Tablet</span>
                            </button>
                            <button type="button" class="designify-editor__tool-button" x-bind:aria-pressed="viewport === 'mobile'" x-on:click="setViewport('mobile')">
                                <x-tabler-device-mobile />
                                <span>Mobile</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="designify-editor__canvas">
                    <div class="designify-editor__frame-shell" x-bind:data-viewport="viewport">
                        <iframe
                            x-ref="previewFrame"
                            class="designify-editor__frame"
                            src="{{ url('/') }}"
                            title="Live Designify preview"
                            x-on:load="sendPreview()"
                        ></iframe>
                    </div>
                </div>

            </section>
        </main>
    </div>
</x-filament-panels::page>
