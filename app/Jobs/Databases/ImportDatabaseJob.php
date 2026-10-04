<?php

namespace App\Jobs\Databases;

use App\Exceptions\Service\Database\DatabaseImportException;
use App\Jobs\Job;
use App\Models\Database;
use App\Services\Databases\DatabaseImportService;
use App\Services\Databases\DatabaseImportStatusService;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImportDatabaseJob extends Job implements ShouldBeEncrypted, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;

    public const TIMEOUT = 3600;

    public int $timeout = self::TIMEOUT;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    /**
     * @param  array{host: string, port: int, database: string, username: string, password: string|null}|null  $remote
     */
    public function __construct(
        public int $database,
        public ?string $file,
        public ?array $remote,
        public bool $wipe = false,
    ) {
        $this->queue = 'standard';
    }

    public function handle(DatabaseImportService $service, DatabaseImportStatusService $status): void
    {
        $database = Database::query()->with('host')->find($this->database);

        if (is_null($database)) {
            $this->deleteFile();

            return;
        }

        $lock = Cache::lock($this->lockKey(), self::TIMEOUT + 60);
        if (! $lock->get()) {
            $this->deleteFile();

            return;
        }

        $status->refresh($database);

        try {
            $progress = fn (int $statements) => $status->progress($database, $statements);

            $statements = $this->remote
                ? $service->fromRemote($database, $this->remote, $this->wipe, $progress)
                : $service->fromFile($database, Storage::disk('local')->path($this->file), $this->wipe, $progress);

            $status->complete($database, $statements);
        } catch (DatabaseImportException $exception) {
            $status->fail($database, $exception->getReason(), $exception->getDetail());
        } catch (\Throwable $exception) {
            report($exception);

            $status->fail($database, DatabaseImportException::UNKNOWN);
        } finally {
            $lock->release();
            $this->deleteFile();
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $this->deleteFile();
        Cache::lock($this->lockKey())->forceRelease();

        $database = Database::query()->find($this->database);
        if (! is_null($database)) {
            app(DatabaseImportStatusService::class)->fail($database, DatabaseImportException::TIMED_OUT);
        }
    }

    private function lockKey(): string
    {
        return 'database:import:'.$this->database.':job';
    }

    private function deleteFile(): void
    {
        if (! is_null($this->file)) {
            Storage::disk('local')->delete($this->file);
        }
    }
}
