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

    $type = in_array($type, array_keys($icons), true) ? $type : 'info';

    $styles = [
        'info' => [
            'container' => 'border-blue-300 bg-blue-50 text-blue-800 dark:border-blue-700 dark:bg-blue-950 dark:text-blue-200',
            'icon' => 'text-blue-600 dark:text-blue-400',
            'title' => 'text-blue-900 dark:text-blue-100',
            'links' => '[&_a]:text-blue-700 dark:[&_a]:text-blue-300',
        ],
        'success' => [
            'container' => 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-950 dark:text-green-200',
            'icon' => 'text-green-600 dark:text-green-400',
            'title' => 'text-green-900 dark:text-green-100',
            'links' => '[&_a]:text-green-700 dark:[&_a]:text-green-300',
        ],
        'warning' => [
            'container' => 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-200',
            'icon' => 'text-amber-600 dark:text-amber-400',
            'title' => 'text-amber-900 dark:text-amber-100',
            'links' => '[&_a]:text-amber-700 dark:[&_a]:text-amber-300',
        ],
        'danger' => [
            'container' => 'border-red-300 bg-red-50 text-red-800 dark:border-red-700 dark:bg-red-950 dark:text-red-200',
            'icon' => 'text-red-600 dark:text-red-400',
            'title' => 'text-red-900 dark:text-red-100',
            'links' => '[&_a]:text-red-700 dark:[&_a]:text-red-300',
        ],
    ];

    $style = $styles[$type];
@endphp

<div
    {{ \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())->class([
        'flex items-start gap-3 rounded-xl border p-4 text-sm leading-5',
        $style['container'],
    ]) }}
    role="alert"
>
    <x-filament::icon
        :icon="$icons[$type]"
        @class([
            'mt-0.5 size-5 shrink-0',
            $style['icon'],
        ])
    />

    <div class="min-w-0 flex-1">
        @if (filled($title))
            <p @class(['m-0 font-semibold', $style['title']])>
                {{ $title }}
            </p>
        @endif

        <div @class([
            ' [&_a]:font-medium [&_a]:no-underline [&_a:hover]:underline',
            'mt-1' => filled($title),
            $style['links'],
        ])>
            {{ $description }}
        </div>

        @if (filled($actions))
            <div @class([
                'mt-2 [&_a]:font-medium [&_a]:no-underline [&_a:hover]:underline',
                $style['links'],
            ])>
                {!! $actions !!}
            </div>
        @endif
    </div>
</div>
