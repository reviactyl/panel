<?php

namespace App\Filament\Resources\DatabaseHost\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\DatabaseHost\DatabaseHostResource;

class CreateDatabaseHost extends CreateRecord
{
    protected static string $resource = DatabaseHostResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $record = parent::handleRecordCreation($data);

        /** @var \App\Services\Activity\ActivityLogService $logService */
        $logService = app(\App\Services\Activity\ActivityLogService::class);
        $logService->subject($record)->event('server:database-host.create')->log();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
