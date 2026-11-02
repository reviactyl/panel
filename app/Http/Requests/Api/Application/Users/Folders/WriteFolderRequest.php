<?php

namespace App\Http\Requests\Api\Application\Users\Folders;

use App\Http\Requests\Api\Client\Account\StoreServerCategoryRequest;
use App\Http\Requests\Api\Client\Account\UpdateServerCategoryRequest;
use App\Services\Acl\Api\AdminAcl;

class WriteFolderRequest extends ReadFolderRequest
{
    protected int $permission = AdminAcl::WRITE;

    public function rules(): array
    {
        if ($this->isMethod('DELETE')) {
            return [];
        }

        return $this->isMethod('POST')
            ? (new StoreServerCategoryRequest())->rules()
            : (new UpdateServerCategoryRequest())->rules();
    }
}
