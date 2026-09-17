<?php

namespace App\Filament\Resources\Nests\Pages;

use App\Filament\Resources\Nests\NestResource;
use App\Services\Nests\NestCreationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNest extends CreateRecord
{
    protected static string $resource = NestResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(NestCreationService::class)->handle($data, $data['author'] ?? null);
    }
}
