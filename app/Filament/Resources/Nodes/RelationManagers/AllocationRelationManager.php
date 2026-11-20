<?php

namespace App\Filament\Resources\Nodes\RelationManagers;

use App\Filament\Concerns\AuthorizesAdminRelation;
use App\Models\Allocation;
use App\Models\Node;
use App\Services\Acl\RolePermission;
use App\Services\Activity\ActivityLogService;
use App\Services\Allocations\AllocationDeletionService;
use App\Services\Allocations\AssignmentService;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class AllocationRelationManager extends RelationManager
{
    use AuthorizesAdminRelation;

    protected static string $permissionResource = RolePermission::RESOURCE_NODES;

    protected static function changesOwner(): bool
    {
        return true;
    }

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

                TextInputColumn::make('ip_alias')
                    ->label(trans('admin/node.allocations.table.alias'))
                    ->rules(['string', 'max:191'])
                    ->disabled(fn (): bool => ! static::allows(RolePermission::UPDATE))
                    ->toggleable(),

                TextColumn::make('server.name')
                    ->label(trans('admin/node.allocations.table.server'))
                    ->formatStateUsing(fn ($state) => $state ?: trans('admin/node.allocations.table.unassigned')),

                TextInputColumn::make('notes')
                    ->label(trans('admin/node.allocations.table.notes'))
                    ->rules(['string', 'max:256'])
                    ->disabled(fn (): bool => ! static::allows(RolePermission::UPDATE))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(trans('admin/node.allocations.table.created'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('addAllocation')
                    ->authorize(fn (): bool => static::allows(RolePermission::UPDATE))
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
                    ->action(function (array $data, Action $action) {
                        /** @var Node $node */
                        $node = $this->getOwnerRecord();

                        try {
                            app(AssignmentService::class)->handle($node, $data);

                            app(ActivityLogService::class)
                                ->subject($node)
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

                            $action->halt();
                        }
                    }),
            ])
            ->actions([
                Action::make('delete')
                    ->authorize(fn (): bool => static::allows(RolePermission::UPDATE))
                    ->label(trans('admin/node.allocations.actions.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->disabled(fn (Allocation $record) => $record->server_id !== null)
                    ->action(function (Allocation $record) {
                        try {
                            app(AllocationDeletionService::class)->handle($record);

                            app(ActivityLogService::class)
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
