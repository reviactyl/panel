@props([
    'type' => 'info',
    'title' => null,
    'icon' => null,
])

@php
    $type = in_array($type, ['info', 'success', 'warning', 'danger'], true) ? $type : 'info';

    $icons = [
        'info' => 'heroicon-m-information-circle',
        'success' => 'heroicon-m-check-circle',
        'warning' => 'heroicon-m-exclamation-triangle',
        'danger' => 'heroicon-m-x-circle',
    ];
@endphp

<div
    {{ $attributes->class('filament-alert')->merge(['role' => 'alert']) }}
    data-alert-type="{{ $type }}"
>
    <x-filament::icon
        :icon="$icon ?? $icons[$type]"
        class="filament-alert__icon"
    />

    <div class="filament-alert__content">
        @if (filled($title))
            <p class="filament-alert__title">{{ $title }}</p>
        @endif

        <div class="filament-alert__message">
            {{ $slot }}
        </div>

        @if (isset($actions))
            <div class="filament-alert__actions">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
