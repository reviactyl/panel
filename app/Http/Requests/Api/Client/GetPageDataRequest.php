<?php

namespace App\Http\Requests\Api\Client;

class GetPageDataRequest extends ClientApiRequest
{
    public function rules(): array
    {
        return [
            'path' => ['required', 'string', 'max:255', 'regex:#^/[A-Za-z0-9/_.\-]*$#'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
