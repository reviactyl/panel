<?php

namespace App\Filament\Resources\Nests\RelationManagers;

use App\Filament\Resources\Nests\EggResource;
use App\Models\Nest;
use App\Services\Eggs\Sharing\EggExporterService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EggsRelationManager extends RelationManager
{
    protected static string $relationship = 'eggs';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('image')
                    ->getStateUsing(fn ($record) => $record->image ?: url('/reviactyl/icon.png')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->toggleable()
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('create')
                    ->label(trans('admin/eggs.actions.create'))
                    ->icon('heroicon-o-plus')
                    ->url(function (): string {
                        /** @var Nest $nest */
                        $nest = $this->getOwnerRecord();

                        return EggResource::getUrl('create', ['nest_id' => $nest->id]);
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label(trans('admin/eggs.actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => EggResource::getUrl('edit', ['record' => $record])),
                Action::make('export')
                    ->label(trans('admin/eggs.actions.export'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $json = app(EggExporterService::class)->handle($record->id);
                        $filename = trim(preg_replace('/\W/', '-', kebab_case($record->name)), '-');

                        return response()->streamDownload(function () use ($json) {
                            echo $json;
                        }, 'egg-'.$filename.'.json');
                    }),
                DeleteAction::make()
                    ->before(function ($record, $action) {
                        if ($record->servers()->count() > 0) {
                            Notification::make()
                                ->title(trans('admin/eggs.notices.cannot_delete'))
                                ->body(trans('admin/eggs.notices.cannot_delete_body', ['count' => $record->servers()->count()]))
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->before(function ($records) {
                        $protectedCount = $records->filter(fn ($record) => $record->servers()->count() > 0)->count();
                        if ($protectedCount > 0) {
                            Notification::make()
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
