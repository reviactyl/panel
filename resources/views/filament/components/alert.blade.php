@php
    $description = $getDescription();
    $title = $getTitle();
    $type = $getType();
    $actions = $getChildSchema($schemaComponent::ACTIONS_SCHEMA_KEY)?->toHtmlString();
    $icons = [
        'info' => 'heroicon-m-information-circle',
        'success' => 'heroicon-m-check-circle',
        'warning' => 'heroicon-m-exclamation-triangle',
        'danger' => 'heroicon-m-x-circle',
    ];
@endphp

<div
    {{ \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())->class('filament-alert') }}
    role="alert"
    data-alert-type="{{ $type }}"
>
    <x-filament::icon :icon="$icons[$type]" class="filament-alert__icon" />

    <div class="filament-alert__content">
        @if (filled($title))
            <p class="filament-alert__title">{{ $title }}</p>
        @endif

        <div class="filament-alert__message">
            {{ $description }}
        </div>

        @if (filled($actions))
            <div class="filament-alert__actions">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
