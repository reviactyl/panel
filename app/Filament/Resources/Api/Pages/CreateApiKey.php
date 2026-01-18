<?php

namespace App\Filament\Resources\Api\Pages;

use App\Filament\Resources\Api\ApiKeyResource;
use App\Models\ApiKey;
use App\Services\Acl\Api\AdminAcl;
use App\Services\Api\KeyCreationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        $permissions = [];

        foreach (AdminAcl::getResourceList() as $resource) {
            $key = AdminAcl::COLUMN_IDENTIFIER . $resource;

            if (array_key_exists($key, $data)) {
                $permissions[$key] = (int) $data[$key];
                unset($data[$key]);
            }
        }

        app(KeyCreationService::class)
            ->setKeyType(ApiKey::TYPE_APPLICATION)
            ->handle(
                [
                    'memo' => $data['memo'],
                    'user_id' => Auth::id(),
                ],
                $permissions
            );

        return ApiKey::query()->latest()->firstOrFail();
    }
}
