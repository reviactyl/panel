<?php

namespace App\Http\Requests\Api\Client\Servers\Files;

use App\Models\Permission;
use App\Contracts\Http\ClientPermissionsRequest;
use App\Http\Requests\Api\Client\ClientApiRequest;

class PullFileRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_CREATE;
    }

    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'string',
                'url',
                // HTTPS only — prevents downloading over unencrypted connections.
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (!str_starts_with(strtolower((string) $value), 'https://')) {
                        $fail('Only HTTPS URLs are permitted.');
                    }
                },
                // Block shell command-substitution patterns: $(...), ${...}, `...`
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (preg_match('/\$(\(|\{)|`/', (string) $value)) {
                        $fail('The URL contains invalid characters.');
                    }
                },
            ],
            'directory' => 'nullable|string',
            'filename' => 'nullable|string',
            'use_header' => 'boolean',
            'foreground' => 'boolean',
        ];
    }
}
