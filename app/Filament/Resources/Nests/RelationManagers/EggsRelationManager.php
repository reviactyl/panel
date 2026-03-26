<?php

namespace App\Filament\Resources\Nests\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class EggsRelationManager extends RelationManager
{
    protected static string $relationship = 'eggs';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('create')
                    ->label(trans('admin/eggs.actions.create'))
                    ->icon('heroicon-o-plus')
                    ->url(fn () => \App\Filament\Resources\Nests\EggResource::getUrl('create', ['nest_id' => $this->getOwnerRecord()->id])),
            ])
            ->actions([
                \Filament\Actions\Action::make('view')
                    ->label(trans('admin/eggs.actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => \App\Filament\Resources\Nests\EggResource::getUrl('edit', ['record' => $record])),
                \Filament\Actions\Action::make('export')
                    ->label(trans('admin/eggs.actions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $json = app(\App\Services\Eggs\Sharing\EggExporterService::class)->handle($record->id);
                        $filename = trim(preg_replace('/\W/', '-', kebab_case($record->name)), '-');

                        return response()->streamDownload(function () use ($json) {
                            echo $json;
                        }, 'egg-' . $filename . '.json');
                    }),
                \Filament\Actions\DeleteAction::make()
                    ->before(function ($record, $action) {
                        if ($record->servers()->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title(trans('admin/eggs.notices.cannot_delete'))
                                ->body(trans('admin/eggs.notices.cannot_delete_body', ['count' => $record->servers()->count()]))
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make()
                    ->before(function ($records) {
                        $protectedCount = $records->filter(fn ($record) => $record->servers()->count() > 0)->count();
                        if ($protectedCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title(trans('admin/eggs.notices.cannot_delete_multiple'))
                                ->body(trans('admin/eggs.notices.cannot_delete_multiple_body', ['count' => $protectedCount]))
                                ->warning()
                                ->send();
                        }
                    })
                    ->action(function ($records) {
                        $deletable = $records->filter(fn ($record) => $record->servers()->count() === 0);
                        $deletable->each->delete();
                    }),
            ]);
    }
}
