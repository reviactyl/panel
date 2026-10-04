<?php

namespace App\Filament\Resources\Alerts\Tables;

use App\Models\Alert;
use App\Services\Activity\ActivityLogService;
use App\Services\Alerts\AlertVisibilityService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'interactions as dismissals_count' => fn (Builder $builder) => $builder->whereNotNull('dismissed_at'),
                'interactions as clicks_count' => fn (Builder $builder) => $builder->whereNotNull('clicked_at'),
            ]))
            ->defaultSort('priority', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label(trans('admin/alerts.columns.name'))
                    ->description(fn (Alert $record): string => Str::limit($record->message, 50))
                    ->searchable(['name', 'title', 'message'])
                    ->sortable(),

                TextColumn::make('type')
                    ->label(trans('admin/alerts.columns.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => trans("admin/alerts.types.$state"))
                    ->color(fn (string $state): string => match ($state) {
                        'announcement' => 'primary',
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger' => 'danger',
                        default => 'info',
                    }),

                TextColumn::make('status')
                    ->label(trans('admin/alerts.columns.status'))
                    ->state(fn (Alert $record): string => $record->status())
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => trans("admin/alerts.statuses.$state"))
                    ->color(fn (string $state): string => match ($state) {
                        Alert::STATUS_ACTIVE => 'success',
                        Alert::STATUS_SCHEDULED => 'info',
                        Alert::STATUS_EXPIRED => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('audience')
                    ->label(trans('admin/alerts.columns.audience'))
                    ->formatStateUsing(fn (string $state): string => trans("admin/alerts.audiences.$state")),

                TextColumn::make('placements')
                    ->label(trans('admin/alerts.columns.placements'))
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => trans("admin/alerts.placements.$state")),

                ToggleColumn::make('enabled')
                    ->label(trans('admin/alerts.columns.enabled')),

                TextColumn::make('dismissals_count')
                    ->label(trans('admin/alerts.columns.dismissals'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('clicks_count')
                    ->label(trans('admin/alerts.columns.clicks'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('priority')
                    ->label(trans('admin/alerts.columns.priority'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ends_at')
                    ->label(trans('admin/alerts.columns.ends_at'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(trans('generic.never'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(trans('admin/alerts.columns.type'))
                    ->options(collect(Alert::TYPES)->mapWithKeys(fn (string $type) => [$type => trans("admin/alerts.types.$type")])->all()),

                TernaryFilter::make('enabled')
                    ->label(trans('admin/alerts.columns.enabled')),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    ReplicateAction::make()
                        ->label(trans('admin/alerts.actions.duplicate'))
                        ->excludeAttributes(['uuid', 'dismissals_count', 'clicks_count'])
                        ->beforeReplicaSaved(function (Alert $replica): void {
                            $replica->name = Str::limit(trans('admin/alerts.copy_suffix', ['name' => $replica->name]), 191, '');
                            $replica->enabled = false;
                        })
                        ->successNotificationTitle(trans('admin/alerts.notices.duplicated')),

                    Action::make('resetDismissals')
                        ->label(trans('admin/alerts.actions.reset_dismissals'))
                        ->icon('tabler-eye-check')
                        ->requiresConfirmation()
                        ->modalDescription(trans('admin/alerts.actions.reset_dismissals_description'))
                        ->visible(fn (Alert $record): bool => $record->dismissible || $record->hidesAfterAction())
                        ->action(function (Alert $record): void {
                            app(AlertVisibilityService::class)->resetDismissals($record);

                            Notification::make()
                                ->title(trans('admin/alerts.notices.dismissals_reset'))
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->after(function (Alert $record): void {
                            app(ActivityLogService::class)
                                ->subject($record)
                                ->event('alert:delete')
                                ->property('name', $record->name)
                                ->log();
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
