<?php

namespace App\Filament\Resources\Mounts\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;

class NodesRelationManager extends RelationManager
{
    protected static string $relationship = 'nodes';

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
                Tables\Columns\TextColumn::make('location.short')
                    ->label(trans('admin/node.table.location'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('fqdn')
                    ->label(trans('admin/node.table.fqdn'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('servers_count')
                    ->label(trans('admin/node.table.servers'))
                    ->counts('servers')
                    ->sortable(),
            ])
            ->filters([

            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'fqdn'])
                    ->label(trans('admin/mounts.actions.attach_node')),
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
