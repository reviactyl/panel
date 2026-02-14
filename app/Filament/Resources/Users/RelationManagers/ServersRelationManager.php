<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\Server;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ServersRelationManager extends RelationManager
{
    protected static string $relationship = 'servers';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label(trans('admin/server.table.id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label(trans('admin/server.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Server $record): string => $record->description ?? ''),

                TextColumn::make('node.name')
                    ->label(trans('admin/server.table.node'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('allocation')
                    ->label(trans('admin/server.table.allocation'))
                    ->formatStateUsing(fn (Server $record) => $record->allocation?->toString())
                    ->toggleable(),

                TextColumn::make('status')
                    ->label(trans('admin/server.table.status'))
                    ->placeholder(trans('admin/server.table.no_status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('egg.name')
                    ->label(trans('admin/server.table.egg'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('memory')
                    ->label(trans('admin/server.table.memory'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if ($state === 0) {
                            return '∞';
                        } elseif ($state >= 1024) {
                            return round($state / 1024, 2) . ' GiB';
                        } else {
                            return $state . ' MiB';
                        }
                    })
                    ->toggleable(),

                TextColumn::make('disk')
                    ->label(trans('admin/server.table.disk'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if ($state === 0) {
                            return '∞';
                        } elseif ($state >= 1024) {
                            return round($state / 1024, 2) . ' GiB';
                        } else {
                            return $state . ' MiB';
                        }
                    })
                    ->toggleable(),

                TextColumn::make('cpu')
                    ->label(trans('admin/server.table.cpu'))
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state === 0 ? '∞' : $state . ' %')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(trans('admin/server.table.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('skip_scripts')
                    ->label(trans('admin/server.fields.skip_scripts.label'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('node_id')
                    ->label(trans('admin/server.table.node'))
                    ->relationship('node', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('egg_id')
                    ->label(trans('admin/server.table.egg'))
                    ->relationship('egg', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label(trans('admin/server.actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(fn (Server $record) => route('filament.admin.resources.servers.edit', ['record' => $record])),

                Action::make('view')
                    ->label(trans('admin/server.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Server $record) => config('app.url') . '/server/' . $record->uuid)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(trans('admin/server.actions.delete'))
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return trans('admin/server.plural-label');
    }
}
