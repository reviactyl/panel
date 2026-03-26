<?php

namespace App\Filament\Resources\Nests;

use App\Models\Egg;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Filament\Resources\Nests\Eggs\Pages;

class EggResource extends Resource
{
    protected static ?string $model = Egg::class;

    protected static ?string $slug = 'nests/egg';

    protected static bool $shouldRegisterNavigation = false;

    public static function getRecordRouteKeyName(): string
    {
        return 'id';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Tabs::make('egg_configuration')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('configuration')
                            ->label(trans('admin/eggs.sections.configuration.title'))
                            ->icon('tabler-settings-2')
                            ->schema([
                                \Filament\Schemas\Components\Section::make(trans('admin/eggs.sections.identity.title'))
                                    ->schema([
                                        Forms\Components\Select::make('nest_id')
                                            ->label(trans('admin/eggs.fields.nest'))
                                            ->relationship('nest', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->disabled(fn ($context) => $context === 'edit')
                                            ->dehydrated(),
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(191),
                                        Forms\Components\TextInput::make('uuid')
                                            ->label(trans('admin/eggs.fields.uuid'))
                                            ->disabled(),
                                        Forms\Components\TextInput::make('author')
                                            ->email()
                                            ->disabled(),
                                        Forms\Components\TextInput::make('banner')
                                            ->maxLength(191),
                                        Forms\Components\Textarea::make('description')
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                \Filament\Schemas\Components\Section::make(trans('admin/eggs.sections.docker_images.title'))
                                    ->description(trans('admin/eggs.sections.docker_images.description'))
                                    ->schema([
                                        Forms\Components\KeyValue::make('docker_images')
                                            ->required()
                                            ->live()
                                            ->keyLabel(trans('admin/eggs.fields.image_name'))
                                            ->valueLabel(trans('admin/eggs.fields.image_uri'))
                                            ->addActionLabel(trans('admin/eggs.fields.add_docker_image'))
                                            ->columnSpanFull(),
                                        Forms\Components\Toggle::make('force_outgoing_ip')
                                            ->label(trans('admin/eggs.fields.force_outgoing_ip'))
                                            ->helperText(trans('admin/eggs.helpers.force_outgoing_ip')),
                                        Forms\Components\TagsInput::make('features')
                                            ->label(trans('admin/eggs.fields.features'))
                                            ->helperText(trans('admin/eggs.helpers.features'))
                                            ->columnSpanFull(),
                                    ]),

                                \Filament\Schemas\Components\Section::make(trans('admin/eggs.sections.process_management.title'))
                                    ->schema([
                                        Forms\Components\Textarea::make('startup')
                                            ->label(trans('admin/eggs.fields.startup'))
                                            ->required()
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('config_stop')
                                            ->label(trans('admin/eggs.fields.config_stop')),
                                        Forms\Components\Select::make('config_from')
                                            ->label(trans('admin/eggs.fields.config_from'))
                                            ->relationship('configFrom', 'name')
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\Textarea::make('config_startup')
                                            ->label(trans('admin/eggs.fields.config_startup'))
                                            ->json()
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('config_logs')
                                            ->label(trans('admin/eggs.fields.config_logs'))
                                            ->json()
                                            ->columnSpanFull(),
                                        Forms\Components\Textarea::make('config_files')
                                            ->label(trans('admin/eggs.fields.config_files'))
                                            ->json()
                                            ->columnSpanFull(),
                                        Forms\Components\TagsInput::make('file_denylist')
                                            ->label(trans('admin/eggs.fields.file_denylist'))
                                            ->helperText(trans('admin/eggs.helpers.file_denylist'))
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('variables')
                            ->label(trans('admin/eggs.sections.variables.title'))
                            ->icon('tabler-variable')
                            ->schema([
                                Forms\Components\Repeater::make('variables')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(191),
                                        Forms\Components\TextInput::make('description')
                                            ->maxLength(191),
                                        Forms\Components\TextInput::make('env_variable')
                                            ->label(trans('admin/eggs.fields.env_variable'))
                                            ->required()
                                            ->maxLength(191),
                                        Forms\Components\TextInput::make('default_value')
                                            ->dehydrateStateUsing(fn ($state) => $state ?? '')
                                            ->maxLength(191),
                                        Forms\Components\Toggle::make('user_viewable')
                                            ->label(trans('admin/eggs.fields.user_viewable'))
                                            ->default(true),
                                        Forms\Components\Toggle::make('user_editable')
                                            ->label(trans('admin/eggs.fields.user_editable'))
                                            ->default(true),
                                        Forms\Components\TagsInput::make('rules')
                                            ->label(trans('admin/eggs.fields.rules'))
                                            ->required()
                                            ->suggestions([
                                                'required',
                                                'nullable',
                                                'string',
                                                'boolean',
                                                'integer',
                                                'numeric',
                                                'alpha',
                                                'alpha_dash',
                                                'alpha_num',
                                                'url',
                                                'email',
                                                'regex:',
                                                'in:',
                                                'in:true,false',
                                                'between:',
                                                'between:1024,65535',
                                                'min:',
                                                'max:',
                                            ]),
                                    ])
                                    ->columns(2)
                                    ->defaultItems(0)
                                    ->reorderableWithButtons()
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                        if (isset($data['rules']) && is_array($data['rules'])) {
                                            $data['rules'] = implode('|', $data['rules']);
                                        }

                                        return $data;
                                    })
                                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                        if (isset($data['rules']) && is_array($data['rules'])) {
                                            $data['rules'] = implode('|', $data['rules']);
                                        }

                                        return $data;
                                    })
                                    ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                        if (isset($data['rules']) && is_string($data['rules'])) {
                                            $data['rules'] = explode('|', $data['rules']);
                                        }

                                        return $data;
                                    }),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('install_script')
                            ->label(trans('admin/eggs.sections.install_script.title'))
                            ->icon('tabler-file-code')
                            ->schema([
                                Forms\Components\Textarea::make('script_install')
                                    ->label(trans('admin/eggs.fields.script_install'))
                                    ->rows(10)
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('script_container')
                                    ->label(trans('admin/eggs.fields.script_container'))
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('script_entry')
                                    ->label(trans('admin/eggs.fields.script_entry'))
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\Select::make('copy_script_from')
                                    ->label(trans('admin/eggs.fields.copy_script_from'))
                                    ->relationship('scriptFrom', 'name')
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Toggle::make('script_is_privileged')
                                    ->label(trans('admin/eggs.fields.script_is_privileged'))
                                    ->helperText(trans('admin/eggs.helpers.script_is_privileged')),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('export')
                    ->label(trans('admin/eggs.actions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $json = app(\App\Services\Eggs\Sharing\EggExporterService::class)->handle($record->id);
                        $filename = trim(preg_replace('/\W/', '-', kebab_case($record->name)), '-');

                        return response()->streamDownload(function () use ($json) {
                            echo $json;
                        }, 'egg-' . $filename . '.json');
                    }),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record, $action) {
                        if ($record->servers()->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title(trans('admin/eggs.notices.cannot_delete'))
                                ->body(trans('admin/eggs.notices.cannot_delete_body', ['count' => $record->servers()->count()]))
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $protectedCount = $records->filter(fn ($record) => $record->servers()->count() > 0)->count();
                            if ($protectedCount > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title(trans('admin/eggs.notices.cannot_delete_multiple'))
                                    ->body(trans('admin/eggs.notices.cannot_delete_multiple_body', ['count' => $protectedCount]))
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->action(function ($records) {
                            $deletable = $records->filter(fn ($record) => $record->servers()->count() === 0);
                            $deletable->each->delete();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEggs::route('/'),
            'create' => Pages\CreateEgg::route('/create'),
            'edit' => Pages\EditEgg::route('/{record}'),
        ];
    }
}
