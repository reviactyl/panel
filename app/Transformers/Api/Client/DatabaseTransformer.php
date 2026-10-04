<?php

namespace App\Transformers\Api\Client;

use App\Contracts\Extensions\HashidsInterface;
use App\Models\Database;
use App\Models\Permission;
use App\Services\Databases\DatabaseImportStatusService;
use Illuminate\Contracts\Encryption\Encrypter;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;

class DatabaseTransformer extends BaseClientTransformer
{
    protected array $availableIncludes = ['password'];

    private Encrypter $encrypter;

    private HashidsInterface $hashids;

    private DatabaseImportStatusService $importStatus;

    /**
     * Handle dependency injection.
     */
    public function handle(Encrypter $encrypter, HashidsInterface $hashids, DatabaseImportStatusService $importStatus)
    {
        $this->encrypter = $encrypter;
        $this->hashids = $hashids;
        $this->importStatus = $importStatus;
    }

    public function getResourceName(): string
    {
        return Database::RESOURCE_NAME;
    }

    public function transform(Database $model): array
    {
        $model->loadMissing('host');

        return [
            'id' => $this->hashids->encode($model->id),
            'host' => [
                'address' => $model->getRelation('host')->host,
                'port' => $model->getRelation('host')->port,
            ],
            'name' => $model->database,
            'username' => $model->username,
            'connections_from' => $model->remote,
            'max_connections' => $model->max_connections,
            'import' => $this->importStatus->get($model),
        ];
    }

    /**
     * Include the database password in the request.
     */
    public function includePassword(Database $database): Item|NullResource
    {
        if (! $this->request->user()->can(Permission::ACTION_DATABASE_VIEW_PASSWORD, $database->server)) {
            return $this->null();
        }

        return $this->item($database, function (Database $model) {
            return [
                'password' => $this->encrypter->decrypt($model->password),
            ];
        }, 'database_password');
    }
}
