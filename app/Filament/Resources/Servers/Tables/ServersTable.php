<?php

namespace App\Filament\Resources\Servers\Tables;

use App\Models\Server;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class ServersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(trans('admin/server.table.id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label(trans('admin/server.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('user')
                    ->label(trans('admin/server.table.owner'))
                    ->html()
                    // This is hacky as hell but it allows us to display the user's name alongside their Gravatar in a single column.
                    ->formatStateUsing(function (Server $record) {
                        $email = strtolower(trim($record->user->email ?? ''));
                        $hash = md5($email);
                        $avatar = $record->user->getFilamentAvatarUrl();
                        $name = $record->user->name_first . ' ' . $record->user->name_last;

                        return "
                            <div style='display:flex;align-items:center;gap:8px'>
                                <img src='{$avatar}' width='28' height='28' style='border-radius:50%'>
                                <span>{$name}</span>
                            </div>
                        ";
                    })
                    ->searchable()
                    ->sortable(),

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
                    ->toggleable(),

                TextColumn::make('memory')
                    ->label(trans('admin/server.table.memory'))
                    ->numeric()
                    ->sortable()
                    // Change to Infinity and remove the MiB suffix if the value is 0 (which indicates no disk limit) and calculate size in GiB for values above or equal to 1024 MiB
                    ->formatStateUsing(function ($state) {
                        if ($state === 0) {
                            return '∞';
                        } elseif ($state >= 1024) {
                            return round($state / 1024, 2) . ' GiB';
                        } else {
                            return $state . ' MiB';
                        }
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('disk')
                    ->label(trans('admin/server.table.disk'))
                    ->numeric()
                    ->sortable()
                    // Change to Infinity and remove the MiB suffix if the value is 0 (which indicates no disk limit) and calculate size in GiB for values above or equal to 1024 MiB
                    ->formatStateUsing(function ($state) {
                        if ($state === 0) {
                            return '∞';
                        } elseif ($state >= 1024) {
                            return round($state / 1024, 2) . ' GiB';
                        } else {
                            return $state . ' MiB';
                        }
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cpu')
                    ->label(trans('admin/server.table.cpu'))
                    ->numeric()
                    ->sortable()
                    // Change to Infinity and remove the percent suffix if the value is 0 (which indicates no CPU limit)
                    ->formatStateUsing(fn ($state) => $state === 0 ? '∞' : $state . ' %')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('skip_scripts')
                    ->label(trans('admin/server.fields.skip_scripts.label'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(trans('admin/server.table.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(trans('admin/server.table.updated'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('installed_at')
                    ->label(trans('admin/server.table.installed'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->recordActions([
                EditAction::make()
                    ->label(trans('admin/server.actions.edit')),
                // Create an Action to open a new tab to the server's panel page
                ViewAction::make('view')
                    ->label(trans('admin/server.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Server $record) => config('app.url') . '/server/' . $record->uuidShort)
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(trans('admin/server.actions.delete')),
                ]),
            ]);
    }
}
