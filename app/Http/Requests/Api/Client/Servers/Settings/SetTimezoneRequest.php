<?php

namespace App\Http\Requests\Api\Client\Servers\Settings;

use App\Contracts\Http\ClientPermissionsRequest;
use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\Permission;

class SetTimezoneRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_SETTINGS_TIMEZONE;
    }

    public function rules(): array
    {
        return [
            'timezone' => 'present|nullable|string|max:64|timezone:all_with_bc',
        ];
    }
}
