<?php

namespace App\Filament\Resources\ActivityLog;

use App\Filament\Resources\ActivityLog\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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

    public static function getSlug(?Panel $panel = null): string
    {
        return 'activity';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('actor.username')
                    ->label(trans('admin/activity_log.columns.user'))
                    ->description(fn (ActivityLog $record) => $record->actor instanceof User ? $record->actor->email : null)
                    ->searchable(['username', 'email'])
                    ->sortable(),

                TextColumn::make('event')
                    ->label(trans('admin/activity_log.columns.action'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ip')
                    ->label(trans('strings.ip'))
                    ->searchable(),

                TextColumn::make('timestamp')
                    ->label(trans('admin/activity_log.columns.time'))
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

            ])
            ->defaultSort('timestamp', 'desc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
