<?php

namespace App\Filament\Resources\Servers\Pages;

use App\Filament\Resources\Servers\ServerResource;
use App\Services\Servers\ServerCreationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateServer extends CreateRecord
{
    protected static string $resource = ServerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['environment'] = $data['environment'] ?? [];
        $data['allocation_additional'] = $data['allocation_additional'] ?? [];

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $server = app(ServerCreationService::class)->handle($data);

        app(\App\Services\Activity\ActivityLogService::class)->subject($server)->event('server:create')->log();

        return $server;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
