<x-filament::widget>
    <x-filament::card>
        <div
            x-data="{
                mode: 'home',
                getSrc() {
                    switch (this.mode) {
                        case '404': return '{{ url('/preview/404') }}';
                        case '403': return '{{ url('/preview/403') }}';
                        case '500': return '{{ url('/preview/500') }}';
                        default: return '{{ url('/') }}';
                    }
                },

                setMode(m) {
                    this.mode = m;
                    this.$nextTick(() => this.$refs.frame.src = this.getSrc());
                },

                btnStyle(m, danger = false) {
                    if (this.mode === m) {
                        return danger
                            ? 'background:var(--danger-500);'
                            : 'background:var(--primary-500);';
                    }
                    return 'background:var(--gray-700);';
                }
            }"

            x-on:reload-iframe.window="
                const iframe = $refs.frame;
                if (iframe) {
                    iframe.src = getSrc() + '?t=' + Date.now();
                }
            "
        >
            <div style="display:flex; gap:8px; margin-bottom:12px;">
                <x-filament::button
                    size="sm"
                    @click="setMode('home')"
                    x-bind:style="btnStyle('home')"
                >
                    <x-heroicon-o-home style="width:16px;height:16px;margin-right:4px;" />
                    Home
                </x-filament::button>

                <x-filament::button
                    size="sm"
                    @click="setMode('404')"
                    x-bind:style="btnStyle('404')"
                >
                    <x-heroicon-o-magnifying-glass style="width:16px;height:16px;margin-right:4px;" />
                    404
                </x-filament::button>

                <x-filament::button
                    size="sm"
                    @click="setMode('403')"
                    x-bind:style="btnStyle('403')"
                >
                    <x-heroicon-o-lock-closed style="width:16px;height:16px;margin-right:4px;" />
                    403
                </x-filament::button>

                <x-filament::button
                    size="sm"
                    @click="setMode('500')"
                    x-bind:style="btnStyle('500', true)"
                >
                    <x-heroicon-o-exclamation-triangle style="width:16px;height:16px;margin-right:4px;" />
                    500
                </x-filament::button>

            </div>
            <div style="width:100%; height:80vh; overflow:hidden; border-radius:12px;">
                <iframe
                    x-ref="frame"
                    id="preview-frame"
                    :src="getSrc()"
                    style="width:100%; height:100%; border:0;"
                ></iframe>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
