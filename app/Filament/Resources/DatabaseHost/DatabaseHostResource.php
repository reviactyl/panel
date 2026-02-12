<?php

namespace App\Filament\Resources\DatabaseHost;

use Filament\Actions;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Panel;
use App\Models\DatabaseHost;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Filament\Resources\DatabaseHost\Pages;

class DatabaseHostResource extends Resource
{
    protected static ?string $model = DatabaseHost::class;

    protected static ?int $navigationSort = 1;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-circle-stack';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.management.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.management.databases');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/databases.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/databases.plural-label');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'databases';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make(trans('admin/databases.sections.host_details.title'))
                    ->description(trans('admin/databases.sections.host_details.description'))
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(191)
                            ->placeholder(trans('admin/databases.placeholders.name')),

                        TextInput::make('host')
                            ->required()
                            ->maxLength(191)
                            ->placeholder(trans('admin/databases.placeholders.host'))
                            ->helperText(trans('admin/databases.helpers.host')),

                        TextInput::make('port')
                            ->required()
                            ->numeric()
                            ->default(3306)
                            ->minValue(1)
                            ->maxValue(65535),
                    ])
                    ->columnSpanFull(),

                Section::make(trans('admin/databases.sections.authentication.title'))
                    ->schema([
                        TextInput::make('username')
                            ->required()
                            ->maxLength(32)
                            ->placeholder(trans('admin/databases.placeholders.username')),

                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                    ])
                    ->columnSpanFull(),

                Section::make(trans('admin/databases.sections.linked_node.title'))
                    ->schema([
                        Select::make('node_id')
                            ->label(trans('admin/databases.fields.linked_node'))
                            ->relationship('node', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText(trans('admin/databases.helpers.linked_node')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(trans('admin/databases.columns.id'))
                    ->sortable(),

                TextColumn::make('name')
                    ->label(trans('admin/databases.columns.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('host')
                    ->label(trans('admin/databases.columns.host'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('port')
                    ->label(trans('admin/databases.columns.port'))
                    ->sortable(),

                TextColumn::make('username')
                    ->label(trans('admin/databases.columns.username'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('node.name')
                    ->label(trans('admin/databases.columns.linked_node'))
                    ->placeholder(trans('admin/databases.none'))
                    ->sortable(),

                TextColumn::make('databases_count')
                    ->label(trans('admin/databases.columns.databases'))
                    ->counts('databases')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(trans('admin/databases.columns.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Actions\Action::make('edit')
                    ->label(trans('admin/databases.actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(fn (DatabaseHost $record): string => static::getUrl('edit', ['record' => $record->getKey()])),

                Actions\Action::make('delete')
                    ->label(trans('admin/databases.actions.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (DatabaseHost $record, Actions\Action $action) {
                        if ($record->databases()->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title(trans('admin/databases.errors.cannot_delete'))
                                ->danger()
                                ->send();

                            $action->halt();
                            $action->halt();
                        }

                        /** @var \App\Services\Activity\ActivityLogService $logService */
                        $logService = app(\App\Services\Activity\ActivityLogService::class);
                        $logService->subject($record)->event('server:database-host.delete')->log();

                        $record->delete();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDatabaseHosts::route('/'),
            'create' => Pages\CreateDatabaseHost::route('/create'),
            'edit' => Pages\EditDatabaseHost::route('/{record}/edit'),
        ];
    }
}
