<?php

namespace App\Filament\Resources\Api\Pages;

use App\Filament\Resources\Api\ApiKeyResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListApiKeys extends ListRecords
{
    protected static string $resource = ApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(trans('admin/api.create-btn')),
        ];
    }
}
