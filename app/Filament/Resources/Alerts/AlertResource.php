<?php

namespace App\Filament\Resources\Alerts;

use App\Filament\Resources\Alerts\Pages\CreateAlert;
use App\Filament\Resources\Alerts\Pages\EditAlert;
use App\Filament\Resources\Alerts\Pages\ListAlerts;
use App\Filament\Resources\Alerts\Schemas\AlertForm;
use App\Filament\Resources\Alerts\Tables\AlertsTable;
use App\Models\Alert;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AlertResource extends Resource
{
    protected static ?string $model = Alert::class;

    protected static ?int $navigationSort = 2;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-speakerphone';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.administration.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.administration.alerts');
    }

    public static function getModelLabel(): string
    {
        return trans('admin/alerts.label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('admin/alerts.plural_label');
    }

    public static function getNavigationBadge(): ?string
    {
        $active = Alert::query()->active()->count();

        return $active > 0 ? (string) $active : null;
    }

    public static function form(Schema $schema): Schema
    {
        return AlertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlertsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlerts::route('/'),
            'create' => CreateAlert::route('/create'),
            'edit' => EditAlert::route('/{record}/edit'),
        ];
    }
}
