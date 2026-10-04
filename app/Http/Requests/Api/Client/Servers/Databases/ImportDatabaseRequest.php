<?php

namespace App\Http\Requests\Api\Client\Servers\Databases;

use App\Contracts\Http\ClientPermissionsRequest;
use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\Permission;
use Illuminate\Http\UploadedFile;

class ImportDatabaseRequest extends ClientApiRequest implements ClientPermissionsRequest
{
    public function permission(): string
    {
        return Permission::ACTION_DATABASE_IMPORT;
    }

    public function rules(): array
    {
        return [
            'wipe' => 'sometimes|boolean',
            'file' => [
                'required_without:remote_host',
                'prohibits:remote_host',
                'file',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value instanceof UploadedFile && ! preg_match('/\.sql(\.(gz|zip))?$/i', $value->getClientOriginalName())) {
                        $fail('The file must be a .sql, .sql.gz or .sql.zip file.');
                    }
                },
                'max:'.((int) config('panel.client_features.databases.max_import_size') * 1024),
            ],
            'remote_host' => ['required_without:file', 'string', 'max:255', 'regex:/^[\w\-.:\[\]]+$/'],
            'remote_port' => 'required_with:remote_host|integer|between:1,65535',
            'remote_database' => ['required_with:remote_host', 'string', 'max:64', 'regex:/^[^;\/\\\\]+$/'],
            'remote_username' => 'required_with:remote_host|string|max:128',
            'remote_password' => 'nullable|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'remote_host' => 'remote host',
            'remote_port' => 'remote port',
            'remote_database' => 'remote database',
            'remote_username' => 'remote username',
            'remote_password' => 'remote password',
        ];
    }

    /**
     * @return array{host: string, port: int, database: string, username: string, password: string|null}|null
     */
    public function remote(): ?array
    {
        if (! $this->filled('remote_host')) {
            return null;
        }

        return [
            'host' => $this->string('remote_host')->toString(),
            'port' => $this->integer('remote_port'),
            'database' => $this->string('remote_database')->toString(),
            'username' => $this->string('remote_username')->toString(),
            'password' => $this->input('remote_password'),
        ];
    }
}
