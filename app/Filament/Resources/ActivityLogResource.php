<?php

namespace App\Filament\Resources;

use App\Models\ActivityLog;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?int $navigationSort = 3;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.administration.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.administration.activity_log');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/navigation.administration.activity_log');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('actor.name')
                    ->label('User')
                    ->description(fn (ActivityLog $record) => $record->actor?->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event')
                    ->label('Action')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ip')
                    ->label('IP Address')
                    ->searchable(),

                TextColumn::make('timestamp')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options(fn () => ActivityLog::query()->distinct()->pluck('event', 'event')->all()),
            ])
            ->actions([
                // We don't really need edit/delete for logs.
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('timestamp', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
