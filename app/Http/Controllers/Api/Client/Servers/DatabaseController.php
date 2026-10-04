<?php

namespace App\Http\Controllers\Api\Client\Servers;

use App\Exceptions\DisplayException;
use App\Exceptions\Repository\RecordNotFoundException;
use App\Exceptions\Service\Database\DatabaseClientFeatureNotEnabledException;
use App\Exceptions\Service\Database\DatabaseImportInProgressException;
use App\Exceptions\Service\Database\TooManyDatabasesException;
use App\Facades\Activity;
use App\Http\Controllers\Api\Client\ClientApiController;
use App\Http\Requests\Api\Client\Servers\Databases\DeleteDatabaseRequest;
use App\Http\Requests\Api\Client\Servers\Databases\ExportDatabaseRequest;
use App\Http\Requests\Api\Client\Servers\Databases\GetDatabaseImportRequest;
use App\Http\Requests\Api\Client\Servers\Databases\GetDatabasesRequest;
use App\Http\Requests\Api\Client\Servers\Databases\ImportDatabaseRequest;
use App\Http\Requests\Api\Client\Servers\Databases\RotatePasswordRequest;
use App\Http\Requests\Api\Client\Servers\Databases\StoreDatabaseRequest;
use App\Jobs\Databases\ImportDatabaseJob;
use App\Models\Database;
use App\Models\Server;
use App\Services\Databases\DatabaseExportService;
use App\Services\Databases\DatabaseImportStatusService;
use App\Services\Databases\DatabaseManagementService;
use App\Services\Databases\DatabasePasswordService;
use App\Services\Databases\DeployServerDatabaseService;
use App\Transformers\Api\Client\DatabaseTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseController extends ClientApiController
{
    /**
     * DatabaseController constructor.
     */
    public function __construct(
        private DeployServerDatabaseService $deployDatabaseService,
        private DatabaseManagementService $managementService,
        private DatabasePasswordService $passwordService,
        private DatabaseExportService $exportService,
        private DatabaseImportStatusService $importStatus,
    ) {
        parent::__construct();
    }

    /**
     * Return all the databases that belong to the given server.
     */
    public function index(GetDatabasesRequest $request, Server $server): array
    {
        return $this->fractal->collection($server->databases)
            ->transformWith($this->getTransformer(DatabaseTransformer::class))
            ->toArray();
    }

    /**
     * Create a new database for the given server and return it.
     *
     * @throws \Throwable
     * @throws TooManyDatabasesException
     * @throws DatabaseClientFeatureNotEnabledException
     */
    public function store(StoreDatabaseRequest $request, Server $server): array
    {
        $database = Activity::event('server:database.create')->transaction(function ($log) use ($request, $server) {
            $server->databases()->lockForUpdate();

            $database = $this->deployDatabaseService->handle($server, $request->validated());

            $log->subject($database)->property('name', $database->database);

            return $database;
        });

        return $this->fractal->item($database)
            ->parseIncludes(['password'])
            ->transformWith($this->getTransformer(DatabaseTransformer::class))
            ->toArray();
    }

    /**
     * Rotates the password for the given server model and returns a fresh instance to
     * the caller.
     *
     * @throws \Throwable
     */
    public function rotatePassword(RotatePasswordRequest $request, Server $server, Database $database): array
    {
        if ($this->importStatus->isRunning($database)) {
            throw new DatabaseImportInProgressException();
        }

        Activity::event('server:database.rotate-password')
            ->subject($database)
            ->property('name', $database->database)
            ->transaction(function () use ($database) {
                $database->lockForUpdate();

                $this->passwordService->handle($database);
            });

        return $this->fractal->item($database->refresh())
            ->parseIncludes(['password'])
            ->transformWith($this->getTransformer(DatabaseTransformer::class))
            ->toArray();
    }

    /**
     * @throws DatabaseImportInProgressException
     * @throws DisplayException
     */
    public function export(ExportDatabaseRequest $request, Server $server, Database $database): StreamedResponse
    {
        if ($this->importStatus->isRunning($database)) {
            throw new DatabaseImportInProgressException();
        }

        $compress = $request->input('compress');

        try {
            $export = $this->exportService->handle($database, $compress);
        } catch (\PDOException $exception) {
            throw new DisplayException('The database could not be exported because its host could not be reached.', $exception);
        }

        Activity::event('server:database.export')
            ->subject($database)
            ->property('name', $database->database)
            ->log();

        $name = sprintf('%s_%s.sql%s', $database->database, now()->format('Y-m-d_His'), $compress ? '.'.$compress : '');

        return response()->streamDownload(function () use ($export) {
            set_time_limit(0);

            $export();
        }, $name, ['Content-Type' => match ($compress) {
            DatabaseExportService::COMPRESSION_GZIP => 'application/gzip',
            DatabaseExportService::COMPRESSION_ZIP => 'application/zip',
            default => 'application/sql',
        }]);
    }

    public function importStatus(GetDatabaseImportRequest $request, Server $server, Database $database): array
    {
        return $this->importStatusResponse($database);
    }

    /**
     * @throws DatabaseImportInProgressException
     * @throws \Throwable
     */
    public function import(ImportDatabaseRequest $request, Server $server, Database $database): JsonResponse
    {
        if (! $this->importStatus->start($database)) {
            throw new DatabaseImportInProgressException();
        }

        $remote = $request->remote();

        try {
            $file = is_null($remote) ? $request->file('file')->store('database-imports', 'local') : null;
            if ($file === false) {
                throw new \RuntimeException('The uploaded database import could not be stored.');
            }

            ImportDatabaseJob::dispatch($database->id, $file, $remote, $request->boolean('wipe'));
        } catch (\Throwable $exception) {
            $this->importStatus->clear($database);

            throw $exception;
        }

        Activity::event('server:database.import')
            ->subject($database)
            ->property([
                'name' => $database->database,
                'source' => is_null($remote) ? 'file' : 'remote',
                'wipe' => $request->boolean('wipe'),
            ])
            ->log();

        return new JsonResponse($this->importStatusResponse($database), Response::HTTP_ACCEPTED);
    }

    /**
     * Removes a database from the server.
     *
     * @throws RecordNotFoundException
     * @throws DatabaseImportInProgressException
     */
    public function delete(DeleteDatabaseRequest $request, Server $server, Database $database): Response
    {
        if ($this->importStatus->isRunning($database)) {
            throw new DatabaseImportInProgressException();
        }

        $this->managementService->delete($database);

        Activity::event('server:database.delete')
            ->subject($database)
            ->property('name', $database->database)
            ->log();

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function importStatusResponse(Database $database): array
    {
        return [
            'object' => 'database_import',
            'attributes' => $this->importStatus->get($database),
        ];
    }
}
