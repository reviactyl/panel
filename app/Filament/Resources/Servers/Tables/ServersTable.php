<?php

namespace App\Filament\Resources\Servers\Tables;

use App\Models\Server;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ServersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultGroup('node.name')
            ->groups([
                Group::make('node.name')->getDescriptionFromRecordUsing(fn (Server $server): string => str($server->node->description)->limit(150)),
                Group::make('user.username')->getDescriptionFromRecordUsing(fn (Server $server): string => $server->user->email),
                Group::make('egg.name')->getDescriptionFromRecordUsing(fn (Server $server): string => str($server->egg->description)->limit(150)),
            ])
            ->columns([

                TextColumn::make('status')
                    ->label(trans('admin/server.table.status'))
                    ->default(trans('admin/server.table.no_status'))
                    ->badge()
                    ->getStateUsing(fn (Server $record): ?string => $record->getResolvedStatus())
                    ->formatStateUsing(fn (?string $state, Server $record): string => $record->getStatusBadgeLabel($state))
                    ->icon(fn (?string $state, Server $record): string => $record->getStatusBadgeIcon($state))
                    ->color(fn (?string $state, Server $record): string => $record->getStatusBadgeColor($state))
                    ->sortable(),

                TextColumn::make('id')
                    ->label(trans('admin/server.table.id'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
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
                        $name = $record->user->name_first.' '.$record->user->name_last;

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
                    ->badge()
                    ->icon('tabler-world')
                    ->toggleable(),

                TextColumn::make('egg.name')
                    ->label(trans('admin/server.table.egg'))
                    ->html()
                    ->formatStateUsing(function (Server $record) {
                        $image = $record->egg->image;
                        $name = $record->egg->name;

                        return "
                            <div style='display:flex;align-items:center;gap:8px'>
                                <img src='{$image}' width='28' height='28' style='border-radius:50%'>
                                <span>{$name}</span>
                            </div>
                        ";
                    })
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('memory')
                    ->label(trans('admin/server.table.memory'))
                    ->formatStateUsing(fn (int $state): string => self::formatLimit($state, 'MiB'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('disk')
                    ->label(trans('admin/server.table.disk'))
                    ->formatStateUsing(fn (int $state): string => self::formatLimit($state, 'MiB'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cpu')
                    ->label(trans('admin/server.table.cpu'))
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? trans('admin/server.table.unlimited') : $state.'%')
                    ->sortable()
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

                TextColumn::make('installed_at')
                    ->label(trans('admin/server.table.installed'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('node_id')
                    ->label(trans('admin/server.table.node'))
                    ->relationship('node', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(trans('admin/server.actions.edit')),
                // Create an Action to open a new tab to the server's panel page
                ViewAction::make('view')
                    ->label(trans('admin/server.actions.view'))
                    ->url(fn (Server $record): string => url('/server/'.$record->uuidShort).'/')
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(trans('admin/server.actions.delete')),
                ]),
            ]);
    }

    private static function formatLimit(int $value, string $unit): string
    {
        if ($value === 0) {
            return '∞';
        }

        if ($value >= 1024) {
            return round($value / 1024, 2).' GiB';
        }

        return $value.' '.$unit;
    }
}
