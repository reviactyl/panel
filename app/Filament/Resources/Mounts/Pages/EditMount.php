<?php

namespace App\Filament\Resources\Mounts\Pages;

use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Mounts\MountResource;

class EditMount extends EditRecord
{
    protected static string $resource = MountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->after(function (\App\Models\Mount $record) {
                    app(\App\Services\Activity\ActivityLogService::class)
                        ->subject($record)
                        ->event('mount:delete')
                        ->log();
                }),
        ];
    }

    protected function afterSave(): void
    {
        app(\App\Services\Activity\ActivityLogService::class)
            ->subject($this->record)
            ->event('mount:update')
            ->log();
    }
}
