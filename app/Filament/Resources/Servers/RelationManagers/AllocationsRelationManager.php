<?php

namespace App\Filament\Resources\Servers\RelationManagers;

use App\Models\Allocation;
use App\Models\Server;
use App\Services\Servers\BuildModificationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AllocationsRelationManager extends RelationManager
{
    protected static string $relationship = 'allocations';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('ip')
                    ->label(trans('admin/server.allocations.table.ip'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('port')
                    ->label(trans('admin/server.allocations.table.port'))
                    ->sortable(),

                TextInputColumn::make('ip_alias')
                    ->label(trans('admin/server.allocations.table.alias'))
                    ->placeholder(trans('admin/server.allocations.placeholder.no_alias_assigned'))
                    ->toggleable(),

                IconColumn::make('primary')
                    ->label(trans('admin/server.allocations.table.primary'))
                    ->boolean()
                    ->state(function (Allocation $record): bool {
                        /** @var Server $owner */
                        $owner = $this->getOwnerRecord();

                        return $record->id === $owner->allocation_id;
                    }),

                TextInputColumn::make('notes')
                    ->label(trans('admin/server.allocations.table.notes'))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(trans('admin/server.allocations.table.created'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('make_primary')
                    ->label(trans('admin/server.allocations.actions.make_primary'))
                    ->icon('heroicon-o-star')
                    ->color('primary')
                    ->visible(function (Allocation $record): bool {
                        /** @var Server $owner */
                        $owner = $this->getOwnerRecord();

                        return $record->id !== $owner->allocation_id;
                    })
                    ->requiresConfirmation()
                    ->action(function (Allocation $record): void {
                        /** @var Server $server */
                        $server = $this->getOwnerRecord();

                        try {
                            app(BuildModificationService::class)->handle($server, [
                                'allocation_id' => $record->id,
                            ]);

                            Notification::make()
                                ->title(trans('admin/server.alerts.primary_allocation_updated'))
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('port');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('admin/server.allocations.title');
    }
}
