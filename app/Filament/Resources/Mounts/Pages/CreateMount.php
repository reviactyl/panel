<?php

namespace App\Filament\Resources\Mounts\Pages;

use App\Filament\Resources\Mounts\MountResource;
use App\Models\Mount;
use App\Services\Activity\ActivityLogService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
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

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = new Mount($data);
        $record->uuid = Str::uuid()->toString();
        $record->save();

        return $record;
    }

    protected function afterCreate(): void
    {
        app(ActivityLogService::class)
            ->subject($this->record)
            ->event('mount:create')
            ->log();
    }
}
