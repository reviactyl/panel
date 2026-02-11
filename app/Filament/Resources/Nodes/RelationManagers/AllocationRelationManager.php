<?php

namespace App\Filament\Resources\Nodes\RelationManagers;

use App\Models\Allocation;
use App\Services\Allocations\AssignmentService;
use App\Services\Allocations\AllocationDeletionService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;

class AllocationRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('ip')
                    ->label(trans('admin/node.allocations.table.ip'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('port')
                    ->label(trans('admin/node.allocations.table.port'))
                    ->sortable(),

                TextColumn::make('ip_alias')
                    ->label(trans('admin/node.allocations.table.alias'))
                    ->toggleable(),

                TextColumn::make('server.name')
                    ->label(trans('admin/node.allocations.table.server'))
                    ->formatStateUsing(fn ($state) => $state ?: trans('admin/node.allocations.table.unassigned')),

                TextColumn::make('notes')
                    ->label(trans('admin/node.allocations.table.notes'))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(trans('admin/node.allocations.table.created'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('addAllocation')
                    ->label(trans('admin/node.allocations.actions.add'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('allocation_ip')
                            ->label(trans('admin/node.allocations.fields.allocation_ip.label'))
                            ->helperText(trans('admin/node.allocations.fields.allocation_ip.helper'))
                            ->required(),
                        TextInput::make('allocation_alias')
                            ->label(trans('admin/node.allocations.fields.allocation_alias.label'))
                            ->helperText(trans('admin/node.allocations.fields.allocation_alias.helper')),
                        TagsInput::make('allocation_ports')
                            ->label(trans('admin/node.allocations.fields.allocation_ports.label'))
                            ->helperText(trans('admin/node.allocations.fields.allocation_ports.helper'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            app(AssignmentService::class)->handle($this->getOwnerRecord(), $data);

                            app(\App\Services\Activity\ActivityLogService::class)
                                ->subject($this->getOwnerRecord())
                                ->event('node:allocation.create')
                                ->log();

                            Notification::make()
                                ->title(trans('admin/node.allocations.messages.created'))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title(trans('admin/node.allocations.messages.failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Action::make('delete')
                    ->label(trans('admin/node.allocations.actions.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->disabled(fn (Allocation $record) => $record->server_id !== null)
                    ->action(function (Allocation $record) {
                        try {
                            app(AllocationDeletionService::class)->handle($record);

                            app(\App\Services\Activity\ActivityLogService::class)
                                ->subject($this->getOwnerRecord())
                                ->event('node:allocation.delete')
                                ->log();

                            Notification::make()
                                ->title(trans('admin/node.allocations.messages.deleted'))
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title(trans('admin/node.allocations.messages.failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}