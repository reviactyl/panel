<?php

namespace App\Filament\Resources\Extensions;

use App\Filament\Resources\Extensions\Pages\ListExtensions;
use App\Models\Extension;
use App\Services\Extensions\ExtensionManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExtensionResource extends Resource
{
    protected static ?string $model = Extension::class;

    protected static ?int $navigationSort = 1;
     
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-puzzle-piece';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.service.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.service.extensions');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/extensions.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/extensions.plural-label');
    }

    public static function getNavigationBadge(): ?string
    {
        return Extension::count() > 0 ? (string) Extension::count() : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identifier')
                    ->label(trans('admin/extensions.columns.id'))
                    ->searchable(),

                TextColumn::make('name')
                    ->label(trans('admin/extensions.columns.name'))
                    ->searchable(),

                TextColumn::make('version')
                    ->label(trans('admin/extensions.columns.version')),

                TextColumn::make('author')
                    ->label(trans('admin/extensions.columns.author')),

                IconColumn::make('enabled')
                    ->label(trans('admin/extensions.columns.enabled'))
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label(trans('admin/extensions.columns.updated'))
                    ->dateTime(),
            ])
            ->actions([
                Action::make('manifest')
                    ->label(trans('admin/extensions.actions.manifest'))
                    ->icon('heroicon-o-document-text')
                    ->modalHeading(trans('admin/extensions.modals.manifest'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(trans('admin/extensions.actions.close'))
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('identifier')->label(trans('admin/extensions.columns.id')),
                        \Filament\Infolists\Components\TextEntry::make('name')->label(trans('admin/extensions.columns.name')),
                        \Filament\Infolists\Components\TextEntry::make('version')->label(trans('admin/extensions.columns.version')),
                        \Filament\Infolists\Components\TextEntry::make('manifest_json')
                            ->label(trans('admin/extensions.columns.manifest_json'))
                            ->state(fn (Extension $record) => json_encode($record->manifest ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
                    ]),

                Action::make('enable')
                    ->icon('heroicon-o-check-circle')
                    ->label(trans('admin/extensions.actions.enable'))
                    ->visible(fn (Extension $record): bool => !$record->enabled)
                    ->action(function (Extension $record): void {
                        try {
                            app(ExtensionManager::class)->enable($record->identifier);
                            Notification::make()->title(trans('admin/extensions.alerts.enabled'))->success()->send();
                        } catch (\Throwable $exception) {
                            Notification::make()->title(trans('admin/extensions.alerts.enable_failed'))->body($exception->getMessage())->danger()->send();
                        }
                    }),

                Action::make('disable')
                    ->color('warning')
                    ->label(trans('admin/extensions.actions.disable'))
                    ->icon('heroicon-o-pause-circle')
                    ->visible(fn (Extension $record): bool => $record->enabled)
                    ->action(function (Extension $record): void {
                        try {
                            app(ExtensionManager::class)->disable($record->identifier);
                            Notification::make()->title(trans('admin/extensions.alerts.disabled'))->success()->send();
                        } catch (\Throwable $exception) {
                            Notification::make()->title(trans('admin/extensions.alerts.disable_failed'))->body($exception->getMessage())->danger()->send();
                        }
                    }),

                DeleteAction::make()
                    ->label(trans('admin/extensions.actions.delete'))
                    ->requiresConfirmation()
                    ->action(function (Extension $record): void {
                        try {
                            app(ExtensionManager::class)->remove($record->identifier);
                            Notification::make()->title(trans('admin/extensions.alerts.uninstalled'))->success()->send();
                        } catch (\Throwable $exception) {
                            Notification::make()->title(trans('admin/extensions.alerts.uninstall_failed'))->body($exception->getMessage())->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExtensions::route('/'),
        ];
    }
}
