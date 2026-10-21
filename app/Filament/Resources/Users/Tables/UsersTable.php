<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\Actions\SendEmailAction;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('roles'))
            ->columns([
                TextColumn::make('id')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('username')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('name_first')
                    ->label(trans('strings.name'))
                    ->searchable(['name_first', 'name_last'])
                    ->formatStateUsing(fn ($record) => $record->name_first.' '.$record->name_last),
                TextColumn::make('roles.name')
                    ->label(trans('admin/roles.plural-label'))
                    ->badge()
                    ->color(fn (User $record): string => $record->root_admin ? 'warning' : 'info')
                    ->placeholder(trans('admin/roles.user.none')),
                IconColumn::make('use_totp')
                    ->boolean()
                    ->label(trans('strings.2fa'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_seen')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->last_seen?->diffForHumans())
                    ->placeholder(trans('generic.never')),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label(trans('admin/roles.plural-label'))
                    ->options(fn (): array => Role::query()->orderByDesc('root')->orderBy('name')->pluck('name', 'id')->all())
                    ->multiple()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['values'] ?? [],
                        fn (Builder $query, array $roles): Builder => $query->whereHas(
                            'roles',
                            fn (Builder $query): Builder => $query->whereIn('roles.id', $roles),
                        ),
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                SendEmailAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords(),
                ]),
            ]);
    }
}
