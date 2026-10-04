<?php

namespace App\Filament\Resources\Alerts\Pages;

use App\Filament\Resources\Alerts\AlertResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlerts extends ListRecords
{
    protected static string $resource = AlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
