<?php

namespace App\Http\Requests\Api\Client\Servers\Settings;

use App\Models\Server;
use App\Http\Requests\Api\Client\ClientApiRequest;

class SetCategoryRequest extends ClientApiRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'category' => 'nullable|string|exists:server_categories,uuid',
        ];
    }

    public function permission(): string
    {
        return 'settings.rename'; // Using rename permission as a proxy for "general settings"
    }
}
