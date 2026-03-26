<?php

namespace App\Filament\Resources\Mounts\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;

class EggsRelationManager extends RelationManager
{
    protected static string $relationship = 'eggs';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(trans('strings.id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nest.name')
                    ->label(trans('admin/nests.label'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable(),
            ])
            ->filters([

            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'author'])
                    ->label(trans('admin/mounts.actions.attach_egg')),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
