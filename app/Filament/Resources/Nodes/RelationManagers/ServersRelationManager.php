<?php

namespace App\Filament\Resources\Nodes\RelationManagers;

use App\Models\Allocation;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use App\Services\Allocations\AllocationDeletionService;
use Filament\Resources\RelationManagers\RelationManager;

class ServersRelationManager extends RelationManager
{
    protected static string $relationship = 'servers';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(trans('admin/server.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('user')
                    ->label(trans('admin/server.table.owner'))
                    ->html()
                    // This is hacky as hell but it allows us to display the user's name alongside their Gravatar in a single column.
                    ->formatStateUsing(function ($state) {
                        $email = strtolower(trim($state->email ?? ''));
                        $hash = md5($email);
                        $avatar = "https://www.gravatar.com/avatar/{$hash}?s=64&d=mp";
                        $name = $state->name_first . ' ' . $state->name_last;

                        return "
                            <div style='display:flex;align-items:center;gap:8px'>
                                <img src='{$avatar}' width='28' height='28' style='border-radius:50%'>
                                <span>{$name}</span>
                            </div>
                        ";
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('allocation')
                    ->label(trans('admin/server.table.allocation'))
                    ->formatStateUsing(fn ($state) => $state?->toString()), // I don't image any server would be without an allocation, unless somebody manually tampered with the database
                TextColumn::make('status')
                    ->label(trans('admin/server.table.status'))
                    ->placeholder(trans('admin/server.table.no_status'))
                    ->badge()
                    ->sortable(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(trans('admin/server.actions.delete'))
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function ($record) {
                                app(AllocationDeletionService::class)->delete($record->allocation);
                                $record->delete();
                            });
                        }),
                ]),
            ]);
    }
}
