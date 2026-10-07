<?php

namespace App\Filament\Resources\Alerts\Pages;

use App\Filament\Resources\Alerts\AlertResource;
use App\Services\Activity\ActivityLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateAlert extends CreateRecord
{
    protected static string $resource = AlertResource::class;

    protected function afterCreate(): void
    {
        app(ActivityLogService::class)
            ->subject($this->record)
            ->event('alert:create')
            ->property('name', $this->record->getAttribute('name'))
            ->log();
    }
}
