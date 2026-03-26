<?php

namespace App\Filament\Resources\Nodes\Tables;

use Filament\Actions;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;

class NodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->columns([
            ViewColumn::make('health')
                ->label(trans('admin/node.table.health'))
                ->view('filament.columns.node-health'),

            TextColumn::make('name')
                ->label(trans('admin/node.table.name'))
                ->searchable()
                ->sortable()
                ->weight('medium'),

            TextColumn::make('location.short')
                ->label(trans('admin/node.table.location'))
                ->searchable()
                ->sortable(),

            TextColumn::make('id')
                ->label(trans('admin/node.table.id'))
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('fqdn')
                ->label(trans('admin/node.table.fqdn'))
                ->searchable()
                ->sortable()
                ->limit(40)
                ->toggleable(isToggledHiddenByDefault: true),

            IconColumn::make('behind_proxy')
                ->label(trans('admin/node.table.behind_proxy'))
                ->boolean()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('memory')
                ->label(trans('admin/node.table.memory'))
                ->numeric()
                ->sortable()
                ->suffix(' MiB')
                ->toggleable(),

            TextColumn::make('disk')
                ->label(trans('admin/node.table.disk'))
                ->numeric()
                ->sortable()
                ->suffix(' MiB')
                ->toggleable(),

            TextColumn::make('created_at')
                ->label(trans('admin/node.table.created'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label(trans('admin/node.table.updated'))
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('servers_count')
                ->label(trans('admin/node.table.servers'))
                ->counts('servers')
                ->sortable(),

            IconColumn::make('maintenance_mode')
                ->label(trans('admin/node.table.maintenance_mode'))
                ->boolean()
                ->sortable()
                ->toggleable(),

            IconColumn::make('public')
                ->label(trans('admin/node.table.public'))
                ->boolean()
                ->sortable()
                ->toggleable(),
        ])
            ->filters([

            ])
            ->actions([
                Actions\EditAction::make()
                    ->label(trans('admin/node.actions.edit')),
                Actions\DeleteAction::make()
                    ->label(trans('admin/node.actions.delete')),
            ])
            /*->recordActions([
                EditAction::make()
                    ->label(trans('admin/node.actions.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(trans('admin/node.actions.delete')),
                ]),
            ])*/;
    }
}
