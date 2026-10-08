<?php

namespace App\Http\Requests\Api\Client\Servers\Databases;

use App\Contracts\Http\ClientPermissionsRequest;
use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\Permission;
use App\Services\Databases\DatabaseExportService;
use Illuminate\Validation\Rule;

class ExportDatabaseRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_EXPORT;
    }

    public function rules(): array
    {
        return [
            'compress' => ['sometimes', 'nullable', Rule::in([DatabaseExportService::COMPRESSION_GZIP, DatabaseExportService::COMPRESSION_ZIP])],
        ];
    }
}
