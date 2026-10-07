<?php

namespace App\Filament\Resources\Alerts\Pages;

use App\Filament\Resources\Alerts\AlertResource;
use App\Models\Alert;
use App\Services\Activity\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlert extends EditRecord
{
    protected static string $resource = AlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function (Alert $record) {
                    app(ActivityLogService::class)
                        ->subject($record)
                        ->event('alert:delete')
                        ->property('name', $record->name)
                        ->log();
                }),
        ];
    }

    protected function afterSave(): void
    {
        app(ActivityLogService::class)
            ->subject($this->record)
            ->event('alert:update')
            ->property('name', $this->record->getAttribute('name'))
            ->log();
    }
}
