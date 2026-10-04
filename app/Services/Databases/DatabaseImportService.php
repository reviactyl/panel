<?php

namespace App\Services\Databases;

use App\Exceptions\Service\Database\DatabaseImportException;
use App\Extensions\DynamicDatabaseConnection;
use App\Models\Database;
use App\Repositories\Eloquent\DatabaseRepository;
use App\Services\Databases\Transfer\DatabaseConnectionFactory;
use App\Services\Databases\Transfer\DatabaseDumper;
use App\Services\Databases\Transfer\SqlStatementReader;
use PDO;

class DatabaseImportService
{
    private const DETAIL_LENGTH = 500;

    private const ERROR_EMPTY_QUERY = 1065;

    public function __construct(
        private DatabaseConnectionFactory $connections,
        private DatabaseDumper $dumper,
        private SqlStatementReader $reader,
        private DynamicDatabaseConnection $dynamic,
        private DatabaseRepository $repository,
    ) {}

    /**
     * @param  (\Closure(int): void)|null  $progress
     *
     * @throws DatabaseImportException
     */
    public function fromFile(Database $database, string $path, bool $wipe = false, ?\Closure $progress = null): int
    {
        if (! is_readable($path)) {
            throw new DatabaseImportException(DatabaseImportException::FILE_UNREADABLE);
        }

        $archive = $this->isZip($path) ? new \ZipArchive() : null;
        $stream = $archive ? $this->openArchive($archive, $path) : @fopen('compress.zlib://'.$path, 'rb');

        if ($stream === false) {
            throw new DatabaseImportException(DatabaseImportException::FILE_UNREADABLE);
        }

        try {
            return $this->import($database, $this->reader->read($stream), $wipe, $progress);
        } finally {
            fclose($stream);
            $archive?->close();
        }
    }

    private function isZip(string $path): bool
    {
        return file_get_contents($path, false, null, 0, 4) === "PK\x03\x04";
    }

    /**
     * @return resource|false
     *
     * @throws DatabaseImportException
     */
    private function openArchive(\ZipArchive $archive, string $path)
    {
        if ($archive->open($path, \ZipArchive::RDONLY) !== true) {
            return false;
        }

        for ($index = 0; $index < $archive->numFiles; $index++) {
            $name = $archive->getNameIndex($index);

            if (is_string($name) && str_ends_with(strtolower($name), '.sql')) {
                return $archive->getStreamIndex($index);
            }
        }

        $archive->close();

        throw new DatabaseImportException(DatabaseImportException::ARCHIVE_EMPTY);
    }

    /**
     * @param  array{host: string, port: int, database: string, username: string, password: string|null}  $remote
     * @param  (\Closure(int): void)|null  $progress
     *
     * @throws DatabaseImportException
     */
    public function fromRemote(Database $database, array $remote, bool $wipe = false, ?\Closure $progress = null): int
    {
        $source = $this->connections->forRemote(
            $remote['host'],
            $remote['port'],
            $remote['database'],
            $remote['username'],
            $remote['password'] ?? '',
        );

        return $this->import($database, $this->remoteStatements($source), $wipe, $progress);
    }

    /**
     * @param  iterable<string>  $statements
     * @param  (\Closure(int): void)|null  $progress
     *
     * @throws DatabaseImportException
     */
    private function import(Database $database, iterable $statements, bool $wipe, ?\Closure $progress): int
    {
        try {
            $target = $this->connections->forDatabase($database);
        } catch (\PDOException $exception) {
            throw new DatabaseImportException(DatabaseImportException::CONNECTION_FAILED, previous: $exception);
        }

        if ($wipe) {
            $this->wipe($database, $target);
        }

        $count = 0;
        $reported = microtime(true);

        foreach ($statements as $statement) {
            try {
                $target->query($statement)->closeCursor();
            } catch (\PDOException $exception) {
                if (($exception->errorInfo[1] ?? null) !== self::ERROR_EMPTY_QUERY) {
                    throw new DatabaseImportException(
                        DatabaseImportException::STATEMENT_FAILED,
                        sprintf('#%d: %s', $count + 1, $this->detail($exception)),
                        $exception
                    );
                }
            }

            $count++;

            if ($progress && microtime(true) - $reported >= 1) {
                $progress($count);
                $reported = microtime(true);
            }
        }

        return $count;
    }

    private function wipe(Database $database, PDO $target): void
    {
        $this->dynamic->set('dynamic', $database->host);

        $this->repository->createDatabase($database->database);
        $this->repository->dropDatabase($database->database);
        $this->repository->createDatabase($database->database);

        $target->exec('USE `'.str_replace('`', '``', $database->database).'`');
    }

    /**
     * @return \Generator<int, string>
     *
     * @throws DatabaseImportException
     */
    private function remoteStatements(PDO $source): \Generator
    {
        try {
            yield from $this->dumper->statements($source);
        } catch (\PDOException $exception) {
            throw new DatabaseImportException(DatabaseImportException::REMOTE_READ_FAILED, $this->detail($exception), $exception);
        }
    }

    private function detail(\PDOException $exception): string
    {
        return mb_strimwidth($exception->errorInfo[2] ?? $exception->getMessage(), 0, self::DETAIL_LENGTH, '...');
    }
}
