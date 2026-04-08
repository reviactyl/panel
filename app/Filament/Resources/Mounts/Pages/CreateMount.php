<?php

namespace App\Filament\Resources\Mounts\Pages;

use App\Filament\Resources\Mounts\MountResource;
use App\Services\Activity\ActivityLogService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateMount extends CreateRecord
{
    protected static string $resource = MountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $firstValue = reset($data);

        if (is_array($firstValue)) {
            $data = $firstValue;
        }

        $data['uuid'] = Str::uuid()->toString();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(ActivityLogService::class)
            ->subject($this->record)
            ->event('mount:create')
            ->log();
    }
}
