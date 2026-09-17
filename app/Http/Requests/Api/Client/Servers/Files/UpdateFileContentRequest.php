<?php

namespace App\Http\Requests\Api\Client\Servers\Files;

use App\Models\Permission;

class UpdateFileContentRequest extends WriteFileContentRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_UPDATE;
    }
}
