<?php

namespace App\Filament\Resources\Mounts\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Mounts\MountResource;

class CreateMount extends CreateRecord
{
    protected static string $resource = MountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }
        
        $data['uuid'] = \Illuminate\Support\Str::uuid()->toString();
        return $data;
    }

    protected function afterCreate(): void
    {
        app(\App\Services\Activity\ActivityLogService::class)
            ->subject($this->record)
            ->event('mount:create')
            ->log();
    }
}
