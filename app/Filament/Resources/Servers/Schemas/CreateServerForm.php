<?php

namespace App\Filament\Resources\Servers\Schemas;

use App\Models\Allocation;
use App\Models\Egg;
use App\Models\EggVariable;
use App\Models\Nest;
use App\Models\User;
use App\Services\Helpers\RandomWordService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CreateServerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Section::make(trans('admin/server.create.sections.core_details'))
                    ->schema([
                        TextInput::make('name')
                            ->label(trans('admin/server.create.fields.name.label'))
                            ->required()
                            ->maxLength(191)
                            ->placeholder(trans('admin/server.create.fields.name.placeholder'))
                            ->helperText(trans('admin/server.create.fields.name.helper'))
                            ->suffixAction(Action::make('random')
                                ->label('Random')
                                ->icon('tabler-dice-'.rand(1, 6))
                                ->action(fn (Set $set) => RandomWordService::setRandomName($set))
                            ),

                        Select::make('owner_id')
                            ->label(trans('admin/server.create.fields.owner.label'))
                            ->relationship('user', 'email')
                            ->getOptionLabelFromRecordUsing(fn (User $record): string => sprintf(
                                '%s %s (%s)',
                                $record->name_first,
                                $record->name_last,
                                $record->email,
                            ))
                            ->searchable(['email', 'username', 'name_first', 'name_last'])
                            ->required()
                            ->helperText(trans('admin/server.create.fields.owner.helper')),

                        Textarea::make('description')
                            ->label(trans('admin/server.create.fields.description.label'))
                            ->rows(3)
                            ->helperText(trans('admin/server.create.fields.description.helper')),

                        Toggle::make('start_on_completion')
                            ->label(trans('admin/server.create.fields.start_on_completion.label'))
                            ->default(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(trans('admin/server.create.sections.allocation'))
                    ->schema([
                        Select::make('node_id')
                            ->label(trans('admin/server.create.fields.node.label'))
                            ->relationship('node', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('allocation_id', null);
                                $set('allocation_additional', []);
                            })
                            ->helperText(trans('admin/server.create.fields.node.helper')),

                        Select::make('allocation_id')
                            ->label(trans('admin/server.create.fields.allocation.label'))
                            ->required()
                            ->searchable()
                            ->options(fn (Get $get): array => self::availableAllocations($get))
                            ->helperText(trans('admin/server.create.fields.allocation.helper')),

                        Select::make('allocation_additional')
                            ->label(trans('admin/server.create.fields.additional_allocations.label'))
                            ->multiple()
                            ->searchable()
                            ->options(fn (Get $get): array => self::availableAllocations($get, excludePrimary: true))
                            ->helperText(trans('admin/server.create.fields.additional_allocations.helper')),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make(trans('admin/server.create.sections.feature_limits'))
                    ->schema([
                        TextInput::make('database_limit')
                            ->label(trans('admin/server.create.fields.database_limit.label'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(trans('admin/server.create.fields.database_limit.helper')),

                        TextInput::make('allocation_limit')
                            ->label(trans('admin/server.create.fields.allocation_limit.label'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(trans('admin/server.create.fields.allocation_limit.helper')),

                        TextInput::make('backup_limit')
                            ->label(trans('admin/server.create.fields.backup_limit.label'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText(trans('admin/server.create.fields.backup_limit.helper')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(trans('admin/server.create.sections.resources'))
                    ->schema(self::resourceFields())
                    ->columns(2)
                    ->columnSpanFull(),

                Grid::make(12)
                    ->schema([
                        Section::make(trans('admin/server.create.sections.nest'))
                            ->schema([
                                Select::make('nest_id')
                                    ->label(trans('admin/server.create.fields.nest.label'))
                                    ->relationship('nest', 'name')
                                    ->default(fn (): ?int => self::firstNestId())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateHydrated(fn (Set $set, mixed $state): null => self::selectFirstEggForNest($set, $state))
                                    ->afterStateUpdated(fn (Set $set, mixed $state): null => self::selectFirstEggForNest($set, $state))
                                    ->helperText(trans('admin/server.create.fields.nest.helper')),

                                Select::make('egg_id')
                                    ->label(trans('admin/server.create.fields.egg.label'))
                                    ->required()
                                    ->searchable()
                                    ->options(fn (Get $get): array => self::eggOptions($get))
                                    ->default(fn (Get $get): ?int => self::firstEggId($get('nest_id')))
                                    ->live()
                                    ->afterStateHydrated(fn (Set $set, mixed $state): null => self::hydrateEggDefaults($set, $state))
                                    ->afterStateUpdated(fn (Set $set, mixed $state): null => self::hydrateEggDefaults($set, $state))
                                    ->helperText(trans('admin/server.create.fields.egg.helper')),

                                Toggle::make('skip_scripts')
                                    ->label(trans('admin/server.create.fields.skip_scripts.label'))
                                    ->default(false)
                                    ->helperText(trans('admin/server.create.fields.skip_scripts.helper')),
                            ])
                            ->columnSpan(['lg' => 6]),

                        Section::make(trans('admin/server.create.sections.docker'))
                            ->schema([
                                Select::make('image')
                                    ->label(trans('admin/server.create.fields.image.label'))
                                    ->required(fn (Get $get): bool => blank($get('custom_image')))
                                    ->searchable()
                                    ->live()
                                    ->default(fn (Get $get): ?string => self::firstDockerImage($get('egg_id')))
                                    ->afterStateUpdated(function (Set $set): null {
                                        $set('custom_image', null);

                                        return null;
                                    })
                                    ->options(fn (Get $get): array => self::dockerImageOptions($get))
                                    ->helperText(trans('admin/server.create.fields.image.helper')),

                                TextInput::make('custom_image')
                                    ->label(trans('admin/server.create.fields.custom_image.label'))
                                    ->maxLength(191)
                                    ->placeholder(trans('admin/server.create.fields.custom_image.placeholder'))
                                    ->helperText(trans('admin/server.create.fields.custom_image.helper')),
                            ])
                            ->columnSpan(['lg' => 6]),
                    ])
                    ->columnSpanFull(),

                Section::make(trans('admin/server.create.sections.startup'))
                    ->schema([
                        TextInput::make('startup')
                            ->label(trans('admin/server.create.fields.startup.label'))
                            ->default(fn (Get $get): ?string => self::firstStartupCommand($get('nest_id')))
                            ->required()
                            ->helperText(trans('admin/server.create.fields.startup.helper'))
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(trans('admin/server.create.sections.variables'))
                    ->schema([
                        Grid::make(2)
                            ->schema(fn (Get $get): array => self::environmentFields($get)),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function resourceFields(): array
    {
        return [
            TextInput::make('cpu')
                ->label(trans('admin/server.create.fields.cpu.label'))
                ->required()
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->suffix('%')
                ->helperText(trans('admin/server.create.fields.cpu.helper')),

            TextInput::make('threads')
                ->label(trans('admin/server.create.fields.threads.label'))
                ->regex('/^[0-9-,]+$/')
                ->helperText(trans('admin/server.create.fields.threads.helper')),

            TextInput::make('memory')
                ->label(trans('admin/server.create.fields.memory.label'))
                ->required()
                ->numeric()
                ->minValue(0)
                ->suffix('MiB')
                ->helperText(trans('admin/server.create.fields.memory.helper')),

            TextInput::make('swap')
                ->label(trans('admin/server.create.fields.swap.label'))
                ->required()
                ->numeric()
                ->minValue(-1)
                ->default(0)
                ->suffix('MiB')
                ->helperText(trans('admin/server.create.fields.swap.helper')),

            TextInput::make('disk')
                ->label(trans('admin/server.create.fields.disk.label'))
                ->required()
                ->numeric()
                ->minValue(0)
                ->suffix('MiB')
                ->helperText(trans('admin/server.create.fields.disk.helper')),

            TextInput::make('io')
                ->label(trans('admin/server.create.fields.io.label'))
                ->required()
                ->numeric()
                ->minValue(10)
                ->maxValue(1000)
                ->default(500)
                ->helperText(trans('admin/server.create.fields.io.helper')),

            Toggle::make('oom_disabled')
                ->label(trans('admin/server.create.fields.oom_disabled.label'))
                ->default(false)
                ->dehydrateStateUsing(fn ($state): bool => ! (bool) $state)
                ->helperText(trans('admin/server.create.fields.oom_disabled.helper')),
        ];
    }

    private static function availableAllocations(Get $get, bool $excludePrimary = false): array
    {
        if (blank($get('node_id'))) {
            return [];
        }

        return Allocation::query()
            ->where('node_id', $get('node_id'))
            ->whereNull('server_id')
            ->when($excludePrimary && filled($get('allocation_id')), fn ($query) => $query->whereKeyNot($get('allocation_id')))
            ->orderBy('ip')
            ->orderBy('port')
            ->get()
            ->mapWithKeys(fn (Allocation $allocation): array => [$allocation->id => $allocation->toString()])
            ->all();
    }

    private static function firstNestId(): ?int
    {
        return Nest::query()->orderBy('id')->value('id');
    }

    private static function firstEggId(mixed $nestId): ?int
    {
        if (blank($nestId)) {
            return null;
        }

        return Egg::query()->where('nest_id', $nestId)->orderBy('id')->value('id');
    }

    private static function firstDockerImage(mixed $eggId): ?string
    {
        $egg = filled($eggId) ? Egg::query()->find($eggId) : null;
        $image = collect(filled($eggId) && $egg !== null ? $egg->docker_images : [])->first();

        return is_string($image) && $image !== '' ? $image : null;
    }

    private static function firstStartupCommand(mixed $nestId): ?string
    {
        $eggId = self::firstEggId($nestId);

        return filled($eggId) ? Egg::query()->whereKey($eggId)->value('startup') : null;
    }

    private static function selectFirstEggForNest(Set $set, mixed $nestId): null
    {
        $eggId = self::firstEggId($nestId);

        $set('egg_id', $eggId);
        self::hydrateEggDefaults($set, $eggId ? (string) $eggId : null);

        return null;
    }

    private static function eggOptions(Get $get): array
    {
        if (blank($get('nest_id'))) {
            return [];
        }

        return Egg::query()->where('nest_id', $get('nest_id'))->orderBy('id')->pluck('name', 'id')->all();
    }

    private static function dockerImageOptions(Get $get): array
    {
        $egg = filled($get('egg_id')) ? Egg::query()->find($get('egg_id')) : null;
        
        return collect($egg !== null ? $egg->docker_images : [])
            ->mapWithKeys(fn (string $image, string $label): array => [
                $image => ctype_digit($label) ? $image : sprintf('%s (%s)', $label, $image),
            ])
            ->all();
    }

    private static function environmentFields(Get $get): array
    {
        if (blank($get('egg_id'))) {
            return [
                TextInput::make('environment_placeholder')
                    ->label(trans('admin/server.create.fields.environment_placeholder.label'))
                    ->disabled()
                    ->dehydrated(false),
            ];
        }

        return EggVariable::query()
            ->where('egg_id', $get('egg_id'))
            ->orderBy('name')
            ->get()
            ->map(fn (EggVariable $variable) => TextInput::make('environment.'.$variable->env_variable)
                ->label($variable->name.($variable->required ? ' *' : ''))
                ->default($variable->default_value)
                ->required((bool) $variable->required)
                ->helperText(trim($variable->description.' Startup Command Variable: '.$variable->env_variable.'. Input Rules: '.$variable->rules)))
            ->all();
    }

    private static function hydrateEggDefaults(Set $set, mixed $eggId): null
    {
        $egg = filled($eggId) ? Egg::query()->with('variables')->find($eggId) : null;

        if (! $egg) {
            $set('startup', null);
            $set('image', null);
            $set('custom_image', null);
            $set('environment', []);

            return null;
        }

        $set('startup', $egg->startup);
        $set('image', self::firstDockerImage($egg->id));
        $set('custom_image', null);
        $set('environment', $egg->variables->mapWithKeys(fn (EggVariable $variable): array => [$variable->env_variable => $variable->default_value])->all());

        return null;
    }
}
