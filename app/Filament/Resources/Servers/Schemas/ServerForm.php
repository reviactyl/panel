<?php

namespace App\Filament\Resources\Servers\Schemas;

use App\Models\Allocation;
use App\Models\Egg;
use App\Models\Server;
use App\Models\User;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Log;

class ServerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('advanced_mode')
                    ->label(trans('admin/server.fields.advanced_mode.label'))
                    ->default(false)
                    ->live()
                    ->helperText(trans('admin/server.fields.advanced_mode.helper'))
                    ->columnSpanFull(),
                    
                Tabs::make('Server Configuration')
                    ->tabs([
                        Tab::make(trans('admin/server.sections.identity.title'))
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Section::make()
                                    ->description(trans('admin/server.sections.identity.description'))
                                    ->schema([
                        TextInput::make('name')
                            ->label(trans('admin/server.fields.name.label'))
                            ->required()
                            ->maxLength(191)
                            ->placeholder(trans('admin/server.fields.name.placeholder'))
                            ->helperText(trans('admin/server.fields.name.helper')),

                        Select::make('owner_id')
                            ->label(trans('admin/server.fields.owner.label'))
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getOptionLabelFromRecordUsing(
                                fn (User $record) => sprintf('%s (%s)', $record->username, $record->email)
                            )
                            ->helperText(trans('admin/server.fields.owner.helper')),

                        Textarea::make('description')
                            ->label(trans('admin/server.fields.description.label'))
                            ->placeholder(trans('admin/server.fields.description.placeholder'))
                            ->helperText(trans('admin/server.fields.description.helper'))
                            ->columnSpanFull(),
                        TextInput::make('external_id')
                            ->label(trans('admin/server.fields.external_id.label'))
                            ->helperText(trans('admin/server.fields.external_id.helper'))
                            ->maxLength(191)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('advanced_mode') === true),
                                    ]),
                            ]),

                        Tab::make(trans('admin/server.sections.startup.title'))
                            ->icon('heroicon-o-command-line')
                            ->schema([
                                Section::make(trans('admin/server.sections.allocation.title'))
                                    ->description(trans('admin/server.sections.allocation.description'))
                                    ->schema([
                                        Select::make('node_id')
                                            ->label(trans('admin/server.fields.node.label'))
                                            ->relationship('node', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->helperText(trans('admin/server.fields.node.helper'))
                                            ->disabled(fn (?Server $record) => $record !== null),

                                        Select::make('allocation_id')
                                            ->label(trans('admin/server.fields.allocation.label'))
                                            ->required()
                                            ->searchable()
                                            ->options(function (Get $get, ?Server $record) {
                                                $nodeId = $get('node_id') ?? $record?->node_id;

                                                if (!$nodeId) {
                                                    return [];
                                                }

                                                $query = Allocation::query()->where('node_id', $nodeId);

                                                if ($record) {
                                                    $query->where(function ($builder) use ($record) {
                                                        $builder->whereNull('server_id')
                                                            ->orWhere('server_id', $record->id);
                                                    });
                                                } else {
                                                    $query->whereNull('server_id');
                                                }

                                                return $query
                                                    ->get()
                                                    ->mapWithKeys(fn (Allocation $allocation) => [$allocation->id => $allocation->toString()])
                                                    ->all();
                                            })
                                            ->disabled(fn (Get $get, ?Server $record) => blank($get('node_id')) && $record === null)
                                            ->helperText(trans('admin/server.fields.allocation.helper')),

                                        Select::make('allocation_additional')
                                            ->label(trans('admin/server.fields.additional_allocations.label'))
                                            ->helperText(trans('admin/server.fields.additional_allocations.helper'))
                                            ->multiple()
                                            ->searchable()
                                            ->options(function (Get $get, ?Server $record) {
                                                $nodeId = $get('node_id') ?? $record?->node_id;

                                                if (!$nodeId) {
                                                    return [];
                                                }

                                                $query = Allocation::query()->where('node_id', $nodeId);

                                                if ($record) {
                                                    $query->where(function ($builder) use ($record) {
                                                        $builder->whereNull('server_id')
                                                            ->orWhere('server_id', $record->id);
                                                    });
                                                } else {
                                                    $query->whereNull('server_id');
                                                }

                                                $options = $query
                                                    ->get()
                                                    ->mapWithKeys(fn (Allocation $allocation) => [$allocation->id => $allocation->toString()])
                                                    ->all();

                                                $defaultAllocation = $get('allocation_id') ?? $record?->allocation_id;

                                                return array_diff_key($options, array_filter([$defaultAllocation => true]));
                                            })
                                            ->default(fn (?Server $record) => $record?->allocations
                                                ->where('id', '!=', $record->allocation_id)
                                                ->pluck('id')
                                                ->values()
                                                ->all() ?? [])
                                            ->disabled(fn (Get $get, ?Server $record) => blank($get('node_id')) && $record === null),
                                    ]),

                                Section::make()
                                    ->description(trans('admin/server.sections.startup.description'))
                                    ->schema([
                        Select::make('nest_id')
                            ->label(trans('admin/server.fields.nest.label'))
                            ->relationship('nest', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->helperText(trans('admin/server.fields.nest.helper')),

                        Select::make('egg_id')
                            ->label(trans('admin/server.fields.egg.label'))
                            ->required()
                            ->searchable()
                            ->options(fn (Get $get) =>
                                Egg::query()
                                    ->where('nest_id', $get('nest_id'))
                                    ->pluck('name', 'id')
                                    ->all()
                            )
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if (!$state) {
                                    $set('environment', []);
                                    $set('startup', '');
                                    return;
                                }

                                $egg = Egg::query()->with('variables')->find($state);

                                if (!$egg) {
                                    return;
                                }

                                // Always set startup value from egg, even if field is not visible
                                $set('startup', $egg->startup);

                                if (blank($get('image'))) {
                                    $images = $egg->docker_images ?? [];

                                    $firstImage = null;
                                    if (is_array($images) && $images !== []) {
                                        $firstKey = array_key_first($images);
                                        $firstImage = is_string($firstKey) ? $firstKey : reset($images);
                                    }

                                    if (is_string($firstImage) && $firstImage !== '') {
                                        $set('image', $firstImage);
                                    }
                                }

                                $set('environment', $egg->variables
                                    ->mapWithKeys(fn ($variable) => [$variable->env_variable => $variable->default_value])
                                    ->toArray());
                            })
                            ->helperText(trans('admin/server.fields.egg.helper')),

                        Textarea::make('startup')
                            ->label(trans('admin/server.fields.startup.label'))
                            ->required(fn (Get $get) => $get('advanced_mode') === true)
                            ->columnSpanFull()
                            ->helperText(trans('admin/server.fields.startup.helper'))
                            ->visible(fn (Get $get) => $get('advanced_mode') === true)
                            ->dehydrated()
                            ->live(),

                        Toggle::make('use_custom_image')
                            ->label(trans('admin/server.fields.use_custom_image.label'))
                            ->default(false)
                            ->live()
                            ->helperText(trans('admin/server.fields.use_custom_image.helper'))
                            ->visible(fn (Get $get) => $get('advanced_mode') === true),


                        // Image
                        TextInput::make('image')
                            ->label(trans('admin/server.fields.image.label'))
                            ->required()
                            ->helperText(trans('admin/server.fields.image.helper'))
                            ->placeholder(trans('admin/server.fields.image.placeholder'))
                            ->visible(fn (Get $get) => $get('use_custom_image') === true),

                        Select::make('image')
                            ->label(trans('admin/server.fields.image.label'))
                            ->required()
                            ->options(function (Get $get, ?Server $record) {
                                $eggId = $get('egg_id') ?? $record?->egg_id;
                                $currentImage = $get('image') ?? $record?->image;

                                if (!$eggId) {
                                    return $currentImage
                                        ? [$currentImage => trans('admin/server.fields.image.custom')]
                                        : [];
                                }

                                $egg = Egg::query()->find($eggId);
                                if (!$egg) {
                                    return $currentImage
                                        ? [$currentImage => trans('admin/server.fields.image.custom')]
                                        : [];
                                }

                                $images = $egg->docker_images ?? [];
                                $options = [];

                                if (is_array($images)) {
                                    foreach ($images as $label => $image) {
                                        if (is_int($label)) {
                                            $options[(string) $image] = (string) $image;
                                        } else {
                                            $options[(string) $image] = (string) $label;
                                        }
                                    }
                                }

                                if ($currentImage && !array_key_exists($currentImage, $options)) {
                                    $options = [$currentImage => trans('admin/server.fields.image.custom')] + $options;
                                }

                                return $options;
                            })
                            ->helperText(trans('admin/server.fields.image.helper'))
                            ->visible(fn (Get $get) => $get('use_custom_image') === false)
                            /*
                            Consideration:
                            If the image is not in the list of images for the selected egg, should we disable the select input? 
                            This would prevent users from selecting a different image without explicitly toggling to custom image mode.
                            However, it would also make it harder/more confusing if they want to remove the custom image.
                            */
                            /*->disabled(function (Get $get, ?Server $record) {
                                if ($get('use_custom_image') === true) {
                                    return false;
                                }

                                $currentImage = $get('image') ?? $record?->image;
                                if (!$currentImage) {
                                    return false;
                                }

                                $eggId = $get('egg_id') ?? $record?->egg_id;
                                if (!$eggId) {
                                    return true;
                                }

                                $egg = Egg::query()->find($eggId);
                                if (!$egg) {
                                    return true;
                                }

                                $images = $egg->docker_images ?? [];
                                $values = [];

                                if (is_array($images)) {
                                    foreach ($images as $label => $image) {
                                        $values[] = (string) $image;
                                    }
                                }

                                return !in_array((string) $currentImage, $values, true);
                            })*/
                            ->columnSpan(fn (Get $get) => $get('advanced_mode') === false ? 2 : 1),

                        Toggle::make('skip_scripts')
                            ->label(trans('admin/server.fields.skip_scripts.label'))
                            ->default(false)
                            ->helperText(trans('admin/server.fields.skip_scripts.helper'))
                            ->visible(fn (Get $get) => $get('advanced_mode') === true),

                        Toggle::make('start_on_completion')
                            ->label(trans('admin/server.fields.start_on_completion.label'))
                            ->default(true)
                            ->helperText(trans('admin/server.fields.start_on_completion.helper'))
                            ->columnSpan(fn (Get $get) => $get('advanced_mode') === false ? 2 : 1),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(trans('admin/server.sections.resources.title'))
                            ->icon('heroicon-o-cpu-chip')
                            ->schema([
                                Section::make()
                                    ->description(trans('admin/server.sections.resources.description'))
                                    ->schema([
                        TextInput::make('memory')
                            ->label(trans('admin/server.fields.memory.label'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn (Get $get) => $get('enter_size_in_gib') ? 'GiB' : 'MiB')
                            // Make sure this saves as MiB in the database regardless of how it's entered
                            ->dehydrateStateUsing(fn ($state, Get $get) => $get('enter_size_in_gib') ? (int)round((float)$state * 1024) : (int)$state)
                            ->helperText(trans('admin/server.fields.memory.helper')),

                        TextInput::make('swap')
                            ->label(trans('admin/server.fields.swap.label'))
                            ->required()
                            ->numeric()
                            ->minValue(-1)
                            ->default(-1)
                            ->suffix(fn (Get $get) => $get('enter_size_in_gib') ? 'GiB' : 'MiB')
                            // Make sure this saves as MiB in the database regardless of how it's entered
                            ->dehydrateStateUsing(fn ($state, Get $get) => $get('enter_size_in_gib') && $state != -1 ? (int)round((float)$state * 1024) : (int)$state)
                            ->helperText(trans('admin/server.fields.swap.helper')),

                        TextInput::make('disk')
                            ->label(trans('admin/server.fields.disk.label'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn (Get $get) => $get('enter_size_in_gib') ? 'GiB' : 'MiB')
                            // Make sure this saves as MiB in the database regardless of how it's entered
                            ->dehydrateStateUsing(fn ($state, Get $get) => $get('enter_size_in_gib') && $state != -1 ? (int)round((float)$state * 1024) : (int)$state)
                            ->helperText(trans('admin/server.fields.disk.helper')),

                        TextInput::make('io')
                            ->label(trans('admin/server.fields.io.label'))
                            ->required(fn (Get $get) => $get('advanced_mode') === true)
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(1000)
                            ->default(500)
                            ->helperText(trans('admin/server.fields.io.helper'))
                            ->visible(fn (Get $get) => $get('advanced_mode') === true)
                            ->dehydrated(),

                        TextInput::make('cpu')
                            ->label(trans('admin/server.fields.cpu.label'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->suffix('%')
                            ->helperText(trans('admin/server.fields.cpu.helper')),

                        TextInput::make('threads')
                            ->label(trans('admin/server.fields.threads.label'))
                            ->helperText(trans('admin/server.fields.threads.helper'))
                            ->visible(fn (Get $get) => $get('advanced_mode') === true),

                        Toggle::make('oom_disabled')
                            ->label(trans('admin/server.fields.oom_disabled.label'))
                            ->default(true)
                            ->helperText(trans('admin/server.fields.oom_disabled.helper'))
                            ->visible(fn (Get $get) => $get('advanced_mode') === true),

                        Toggle::make('enter_size_in_gib')
                            ->label(trans('admin/server.fields.enter_size_in_gib.label'))
                            ->default(false)
                            ->live()
                            ->helperText(trans('admin/server.fields.enter_size_in_gib.helper'))
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $memory = $get('memory');
                                $swap = $get('swap');
                                $disk = $get('disk');
                                if ($get('enter_size_in_gib')) {
                                    // Convert MiB to GiB
                                    $set('memory', is_numeric($memory) ? round((float)$memory / 1024, 2) : null);
                                    if($swap != -1) $set('swap', is_numeric($swap) ? round((float)$swap / 1024, 2) : null);
                                    $set('disk', is_numeric($disk) ? round((float)$disk / 1024, 2) : null);
                                } else {
                                    // Convert GiB to MiB
                                    $set('memory', is_numeric($memory) ? round($memory * 1024) : null);
                                    if($swap != -1) $set('swap', is_numeric($swap) ? round($swap * 1024) : null);
                                    $set('disk', is_numeric($disk) ? round($disk * 1024) : null);
                                }
                            })
                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->columnSpan(2),

                                Section::make(trans('admin/server.sections.feature_limits.title'))
                                    ->description(trans('admin/server.sections.feature_limits.description'))
                    ->schema([
                        TextInput::make('database_limit')
                            ->label(trans('admin/server.fields.database_limit.label'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(trans('admin/server.fields.database_limit.helper')),

                        TextInput::make('allocation_limit')
                            ->label(trans('admin/server.fields.allocation_limit.label'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(trans('admin/server.fields.allocation_limit.helper')),

                        TextInput::make('backup_limit')
                            ->label(trans('admin/server.fields.backup_limit.label'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(trans('admin/server.fields.backup_limit.helper')),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make(trans('admin/server.sections.environment.title'))
                            ->icon('heroicon-o-variable')
                            ->schema([
                                Section::make()
                                    ->description(trans('admin/server.sections.environment.description'))
                                    ->schema([
                        KeyValue::make('environment')
                            ->keyLabel(trans('admin/server.fields.environment.key'))
                            ->valueLabel(trans('admin/server.fields.environment.value'))
                            ->default([])
                            ->helperText(trans('admin/server.fields.environment.helper'))
                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->persistTabInQueryString()
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }
}
