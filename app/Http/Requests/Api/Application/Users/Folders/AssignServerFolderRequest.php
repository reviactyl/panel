<?php

namespace App\Http\Requests\Api\Application\Users\Folders;

class AssignServerFolderRequest extends WriteFolderRequest
{
    public function rules(): array
    {
        return ['category_uuid' => ['present', 'nullable', 'uuid']];
    }
}
