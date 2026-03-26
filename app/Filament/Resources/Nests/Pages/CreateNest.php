<?php

namespace App\Filament\Resources\Nests\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Nests\NestResource;

class CreateNest extends CreateRecord
{
    protected static string $resource = NestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid'] = \Illuminate\Support\Str::uuid()->toString();
        $data['author'] = $data['author'] ?? config('panel.service.author');

        return $data;
    }
}
