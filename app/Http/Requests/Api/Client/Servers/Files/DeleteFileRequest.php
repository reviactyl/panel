<?php

namespace App\Http\Requests\Api\Client\Servers\Files;

use App\Contracts\Http\ClientPermissionsRequest;
use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\Permission;

class DeleteFileRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_DELETE;
    }

    public function rules(): array
    {
        return [
            'root' => 'required|nullable|string',
            'permanent' => 'sometimes|boolean',
            'files' => 'required|array|min:1|max:1000',
            'files.*' => 'string',
        ];
    }
}
