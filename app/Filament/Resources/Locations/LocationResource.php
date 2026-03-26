<?php

namespace App\Filament\Resources\Locations;

use Filament\Actions;
use App\Models\Location;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-globe-alt';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.management.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.management.locations');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/locations.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/locations.plural-label');
    }

    public static function getNavigationBadge(): ?string
    {
        return Location::count() > 0 ? (string) Location::count() : null;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->components([
                Section::make(trans('admin/locations.section.title'))
                    ->description(trans('admin/locations.section.description'))
                    ->schema([
                        TextInput::make('short')
                            ->label(trans('admin/locations.fields.short.label'))
                            ->required()
                            ->maxLength(60)
                            ->unique(ignoreRecord: true)
                            ->placeholder(trans('admin/locations.fields.short.placeholder'))
                            ->helperText(trans('admin/locations.fields.short.helper')),

                        TextInput::make('long')
                            ->label(trans('admin/locations.fields.long.label'))
                            ->maxLength(191)
                            ->placeholder(trans('admin/locations.fields.long.placeholder'))
                            ->helperText(trans('admin/locations.fields.long.helper')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(trans('admin/locations.table.id'))
                    ->sortable(),

                TextColumn::make('short')
                    ->label(trans('admin/locations.table.short'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('long')
                    ->label(trans('admin/locations.table.long'))
                    ->searchable()
                    ->limit(50),

                TextColumn::make('nodes_count')
                    ->label(trans('admin/locations.table.nodes'))
                    ->counts('nodes')
                    ->sortable(),

                TextColumn::make('servers_count')
                    ->label(trans('admin/locations.table.servers'))
                    ->counts('servers')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(trans('admin/locations.table.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->actions([
                Actions\Action::make('edit')
                    ->label(trans('admin/locations.actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(
                        fn (Location $record): string => static::getUrl('edit', ['record' => $record->getKey()])
                    ),

                Actions\Action::make('delete')
                    ->label(trans('admin/locations.actions.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Location $record) {

                        if ($record->nodes()->count() > 0) {
                            throw new \Exception(trans('admin/locations.messages.cannot_delete_with_nodes'));
                        }

                        $record->delete();
                    }),
            ])
            ->bulkActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NodesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
