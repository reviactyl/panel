<?php

namespace App\Filament\Resources\Servers\RelationManagers;

use App\Models\Database;
use App\Models\DatabaseHost;
use App\Models\Server;
use App\Services\Databases\DatabaseManagementService;
use App\Services\Databases\DatabasePasswordService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DatabasesRelationManager extends RelationManager
{
    protected static string $relationship = 'databases';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('database')
            ->columns([
                TextColumn::make('database')
                    ->label(trans('admin/server.databases.table.database'))
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('username')
                    ->label(trans('admin/server.databases.table.username'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('remote')
                    ->label(trans('admin/server.databases.table.remote'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('host.name')
                    ->label(trans('admin/server.databases.table.host'))
                    ->sortable(),

                TextColumn::make('max_connections')
                    ->label(trans('admin/server.databases.table.max_connections'))
                    ->placeholder(trans('admin/server.databases.placeholder.unlimited'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(trans('admin/server.databases.table.created'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('create_database')
                    ->label(trans('admin/server.databases.actions.create_database'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        TextInput::make('database')
                            ->label(trans('admin/server.databases.create_modal.database_name.label'))
                            ->required()
                            ->minLength(1)
                            ->maxLength(24)
                            ->rule('alpha_dash')
                            ->helperText(trans('admin/server.databases.create_modal.database_name.helper')),

                        Select::make('database_host_id')
                            ->label(trans('admin/server.databases.create_modal.database_host.label'))
                            ->options(fn (): array => DatabaseHost::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required(),

                        TextInput::make('remote')
                            ->label(trans('admin/server.databases.create_modal.remote.label'))
                            ->required()
                            ->default('%')
                            ->regex('/^[0-9%.]{1,15}$/'),

                        TextInput::make('max_connections')
                            ->label(trans('admin/server.databases.create_modal.max_connections.label'))
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->action(function (array $data): void {
                        /** @var Server $server */
                        $server = $this->getOwnerRecord();

                        try {
                            app(DatabaseManagementService::class)->create($server, [
                                'database' => DatabaseManagementService::generateUniqueDatabaseName($data['database'], $server->id),
                                'remote' => $data['remote'],
                                'database_host_id' => $data['database_host_id'],
                                'max_connections' => blank($data['max_connections'] ?? null) ? null : (int) $data['max_connections'],
                            ]);

                            Notification::make()
                                ->title(trans('admin/server.alerts.database_created'))
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
            ->recordActions([
                Action::make('reset_password')
                    ->label(trans('admin/server.databases.actions.reset_password'))
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Database $record): void {
                        try {
                            $password = app(DatabasePasswordService::class)->handle($record);

                            Notification::make()
                                ->title(trans('admin/server.alerts.database_password_reset'))
                                ->body(trans('admin/server.alerts.database_password_reset').': '.$password)
                                ->success()
                                ->persistent()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('delete')
                    ->label(trans('admin/server.databases.actions.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Database $record): void {
                        try {
                            app(DatabaseManagementService::class)->delete($record);

                            Notification::make()
                                ->title(trans('admin/server.alerts.database_deleted'))
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('admin/server.databases.title');
    }
}
