<?php

namespace App\Http\Requests\Api\Client\Servers\Files;

use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\Permission;

class RecycleBinRequest extends ClientApiRequest
{
    public function authorize(): bool
    {
        $server = $this->route()->parameter('server');
        if (! $this->user()->can(Permission::ACTION_FILE_READ, $server)) {
            return false;
        }
        if ($this->isMethod('GET')) {
            return true;
        }
        if (! $this->user()->can(Permission::ACTION_FILE_DELETE, $server)) {
            return false;
        }

        return $this->route()->getActionMethod() !== 'restore' ||
            ($this->user()->can(Permission::ACTION_FILE_CREATE, $server) && $this->user()->can(Permission::ACTION_FILE_UPDATE, $server));
    }

    public function rules(): array
    {
        return ($this->isMethod('GET') || $this->route()->getActionMethod() === 'empty') ? [] : ['id' => 'required|uuid', 'replace' => 'sometimes|boolean', 'preview' => 'sometimes|boolean', 'path' => 'sometimes|string|max:4096'];
    }
}
