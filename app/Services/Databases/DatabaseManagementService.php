<?php

namespace App\Services\Databases;

use App\Exceptions\Repository\DuplicateDatabaseNameException;
use App\Exceptions\Service\Database\DatabaseClientFeatureNotEnabledException;
use App\Exceptions\Service\Database\TooManyDatabasesException;
use App\Extensions\DynamicDatabaseConnection;
use App\Helpers\Utilities;
use App\Models\Database;
use App\Models\Server;
use App\Repositories\Eloquent\DatabaseRepository;
use App\Services\Activity\ActivityLogService;
use App\Services\Databases\Transfer\DatabaseConnectionFactory;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

class DatabaseManagementService
{
    /**
     * The regex used to validate that the database name passed through to the function is
     * in the expected format.
     *
     * @see DatabaseManagementService::generateUniqueDatabaseName()
     */
    private const MATCH_NAME_REGEX = '/^(s[\d]+_)(.*)$/';

    /**
     * Determines if the service should validate the user's ability to create an additional
     * database for this server. In almost all cases this should be true, but to keep things
     * flexible you can also set it to false and create more databases than the server is
     * allocated.
     */
    protected bool $validateDatabaseLimit = true;

    public function __construct(
        protected ConnectionInterface $connection,
        protected DynamicDatabaseConnection $dynamic,
        protected Encrypter $encrypter,
        protected DatabaseRepository $repository,
        protected ActivityLogService $logService,
        protected DatabaseConnectionFactory $connections,
    ) {}

    /**
     * Generates a unique database name for the given server. This name should be passed through when
     * calling this handle function for this service, otherwise the database will be created with
     * whatever name is provided.
     */
    public static function generateUniqueDatabaseName(string $name, int $serverId): string
    {
        // Max of 48 characters, including the s123_ that we append to the front.
        return sprintf('s%d_%s', $serverId, substr($name, 0, 48 - strlen("s{$serverId}_")));
    }

    /**
     * Set whether this class should validate that the server has enough slots
     * left before creating the new database.
     */
    public function setValidateDatabaseLimit(bool $validate): self
    {
        $this->validateDatabaseLimit = $validate;

        return $this;
    }

    /**
     * Create a new database that is linked to a specific host.
     *
     * @throws \Throwable
     * @throws TooManyDatabasesException
     * @throws DatabaseClientFeatureNotEnabledException
     */
    public function create(Server $server, array $data): Database
    {
        $database = null;
        $createdDatabase = false;
        $createdUser = false;

        try {
            return $this->connection->transaction(function () use ($server, $data, &$database, &$createdDatabase, &$createdUser) {
                // SQLite ignores FOR UPDATE, so acquire its write lock before reading the quota.
                if ($server->getConnection()->getDriverName() === 'sqlite') {
                    $server->newQuery()->whereKey($server->id)->toBase()->update(['database_limit' => $this->connection->raw('database_limit')]);
                }

                $server = $server->newQuery()->lockForUpdate()->findOrFail($server->id);

                if (! config('panel.client_features.databases.enabled')) {
                    throw new DatabaseClientFeatureNotEnabledException();
                }

                if ($this->validateDatabaseLimit) {
                    // Use a current read even if an enclosing transaction already established a snapshot.
                    if (! is_null($server->database_limit) && count($server->databases()->lockForUpdate()->get(['id'])) >= $server->database_limit) {
                        throw new TooManyDatabasesException();
                    }
                }

                // Protect against developer mistakes...
                if (empty($data['database']) || ! preg_match(self::MATCH_NAME_REGEX, $data['database'])) {
                    throw new \InvalidArgumentException('The database name passed to DatabaseManagementService::handle MUST be prefixed with "s{server_id}_".');
                }

                $plainPassword = Utilities::randomStringWithSpecialCharacters(24);

                $data = array_merge($data, [
                    'server_id' => $server->id,
                    'username' => sprintf('u%d_%s', $server->id, Str::random(10)),
                    'password' => $this->encrypter->encrypt($plainPassword),
                ]);

                $database = $this->createModel($data);

                $this->dynamic->set('dynamic', $data['database_host_id']);

                // Never adopt a database that already exists on the host, otherwise a later failure
                // would drop a database (and its data) that this request never created.
                $this->repository->createDatabase($database->database, false);
                $createdDatabase = true;

                $this->repository->createUser(
                    $database->username,
                    $database->remote,
                    $plainPassword,
                    $database->max_connections
                );
                $createdUser = true;

                $this->repository->assignUserToDatabase($database->database, $database->username, $database->remote);
                $this->repository->flush();

                $this->logService->clone()->subject($database)->event('server:database-create')->log();

                return $database;
            });
        } catch (\Throwable $exception) {
            try {
                // Only remove what this request created on the host.
                if ($database instanceof Database && $createdDatabase) {
                    $this->repository->dropDatabase($database->database);

                    if ($createdUser) {
                        $this->repository->dropUser($database->username, $database->remote);
                    }

                    $this->repository->flush();
                }
            } catch (\Throwable $deletionException) {
                // Do nothing here. We've already encountered an issue before this point so no
                // reason to prioritize this error over the initial one.
            }

            throw $exception;
        }
    }

    /**
     * Delete a database from the given host server.
     *
     * @throws \Exception
     */
    public function delete(Database $database): ?bool
    {
        $this->dynamic->set('dynamic', $database->database_host_id);

        $this->repository->dropDatabase($database->database);
        $this->connections->deleteImportUser($database);
        $this->repository->dropUser($database->username, $database->remote);
        $this->repository->flush();

        $this->logService->clone()->subject($database)->event('server:database-delete')->log();

        return $database->delete();
    }

    /**
     * Create the database if there is not an identical match in the DB. While you can technically
     * have the same name across multiple hosts, for the sake of keeping this logic easy to understand
     * and avoiding user confusion we will ignore the specific host and just look across all hosts.
     *
     * @throws DuplicateDatabaseNameException
     * @throws \Throwable
     */
    protected function createModel(array $data): Database
    {
        $exists = Database::query()->where('server_id', $data['server_id'])
            ->where('database', $data['database'])
            ->exists();

        if ($exists) {
            throw new DuplicateDatabaseNameException('A database with that name already exists for this server.');
        }

        $database = (new Database())->forceFill($data);
        $database->saveOrFail();

        return $database;
    }
}
