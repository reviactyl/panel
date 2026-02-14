<?php

namespace App\Filament\Resources\Locations\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Node;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\Nodes\NodeResource;

class NodesRelationManager extends RelationManager
{
    protected static string $relationship = 'nodes';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label(trans('admin/node.table.id'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label(trans('admin/node.table.name'))
                    ->description(fn (Node $record): string => $record->description ?? '')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('public')
                    ->label(trans('admin/node.table.public'))
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('maintenance_mode')
                    ->label(trans('admin/node.table.maintenance_mode'))
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('fqdn')
                    ->label(trans('admin/node.table.fqdn'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500),

                TextColumn::make('servers_count')
                    ->label(trans('admin/node.table.servers'))
                    ->counts('servers')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state < 5 => 'success',
                        $state < 10 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('allocations_count')
                    ->label(trans('admin/node.allocations.label'))
                    ->counts('allocations')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('public')
                    ->label(trans('admin/node.filters.public'))
                    ->boolean()
                    ->trueLabel('Public')
                    ->falseLabel('Private')
                    ->native(false),

                Tables\Filters\TernaryFilter::make('maintenance_mode')
                    ->label(trans('admin/node.filters.maintenance'))
                    ->boolean()
                    ->trueLabel('Under Maintenance')
                    ->falseLabel('Active')
                    ->native(false),
            ])
            ->headerActions([
                Action::make('create')
                    ->label(trans('admin/node.actions.create'))
                    ->url(fn (): string => NodeResource::getUrl('create'))
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                Action::make('view')
                    ->label(trans('admin/node.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Node $record): string => NodeResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
                    
                Action::make('allocations')
                    ->label(trans('admin/node.allocations.label'))
                    ->icon('heroicon-o-server-stack')
                    ->url(fn (Node $record): string => NodeResource::getUrl('edit', ['record' => $record]) . '?activeRelationManager=0')
                    ->color('info')
                    ->visible(fn (Node $record): bool => $record->allocations_count > 0),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->servers()->count() > 0) {
                                    throw new \Exception(
                                        trans('admin/node.messages.cannot_delete_with_servers')
                                    );
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('id', 'asc')
            ->poll('30s');
    }
}
