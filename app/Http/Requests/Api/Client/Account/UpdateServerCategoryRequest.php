<?php

namespace App\Http\Requests\Api\Client\Account;

use App\Http\Requests\Api\Client\ClientApiRequest;

class UpdateServerCategoryRequest extends ClientApiRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:191',
            'description' => 'nullable|string',
            'color' => ['nullable', 'string', 'max:7', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
        ];
    }
}
