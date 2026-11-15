@php
    $statePath = $getStatePath();
    $uploadPath = $getUploadPath();
    $disabled = $isDisabled();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        class="designify-site-image"
        x-data="{ imageUrl: $wire.$entangle(@js($statePath)), failed: false, uploading: false, progress: 0 }"
        x-init="$watch('imageUrl', () => failed = false)"
        x-on:livewire-upload-start="uploading = true; progress = 0"
        x-on:livewire-upload-finish="uploading = false; $refs.upload.value = ''"
        x-on:livewire-upload-error="uploading = false; $refs.upload.value = ''"
        x-on:livewire-upload-cancel="uploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
        <div class="designify-site-image__row">
            <div @class(['designify-site-image__thumbnail', 'designify-site-image__thumbnail--icon' => $isSquarePreview()])>
                <img
                    x-bind:src="imageUrl && imageUrl !== 'none' ? imageUrl.replace(/^\/public\//, '/') : null"
                    x-show="imageUrl && imageUrl !== 'none' && !failed"
                    x-on:error="failed = true"
                    x-on:load="failed = false"
                    alt=""
                >
                <span x-show="!imageUrl || imageUrl === 'none' || failed" x-cloak class="designify-site-image__fallback" role="status">
                    <x-tabler-photo-off />
                    <span class="sr-only">{{ trans('admin/settings.designify.image-preview-unavailable') }}</span>
                </span>
            </div>

            <x-filament::button
                type="button"
                color="gray"
                size="sm"
                icon="tabler-upload"
                :disabled="$disabled"
                :data-disabled="$disabled ? 'true' : 'false'"
                x-bind:disabled="uploading || $el.dataset.disabled === 'true'"
                x-on:click="$refs.upload.click()"
                :aria-label="trans('admin/settings.designify.upload-image').': '.$getLabel()"
            >
                {{ trans('admin/settings.designify.upload-image') }}
            </x-filament::button>

            <span class="designify-site-image__progress" x-show="uploading" x-cloak role="status" x-text="progress + '%'"></span>
        </div>

        <input
            type="file"
            x-ref="upload"
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
            wire:model="{{ $uploadPath }}"
            @disabled($disabled)
        >

        <x-filament::input.wrapper :disabled="$disabled" :valid="! $errors->has($statePath)">
            <x-filament::input
                :id="$getId()"
                type="text"
                :disabled="$disabled"
                :required="$isRequired() && ! $disabled"
                :maxlength="$getMaxLength()"
                :placeholder="$getPlaceholder()"
                :attributes="new \Illuminate\View\ComponentAttributeBag([$applyStateBindingModifiers('wire:model') => $statePath])"
            />
        </x-filament::input.wrapper>

        @error($uploadPath)
            <p class="fi-fo-field-wrp-error-message" role="alert">{{ $message }}</p>
        @enderror
    </div>
</x-dynamic-component>
