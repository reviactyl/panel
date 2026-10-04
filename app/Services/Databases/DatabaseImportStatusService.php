<?php

namespace App\Services\Databases;

use App\Jobs\Databases\ImportDatabaseJob;
use App\Models\Database;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class DatabaseImportStatusService
{
    public const STATE_RUNNING = 'running';

    public const STATE_COMPLETED = 'completed';

    public const STATE_FAILED = 'failed';

    private const RESULT_LIFETIME = 86400;

    public function __construct(private CacheRepository $cache) {}

    /**
     * @return array{state: string, statements: int, error: string|null, detail: string|null, started_at: string, finished_at: string|null}|null
     */
    public function get(Database $database): ?array
    {
        return $this->cache->get($this->key($database));
    }

    public function isRunning(Database $database): bool
    {
        return ($this->get($database)['state'] ?? null) === self::STATE_RUNNING;
    }

    public function start(Database $database): bool
    {
        if (! $this->cache->add($this->key($database).':running', true, ImportDatabaseJob::TIMEOUT + 60)) {
            return false;
        }

        $this->refresh($database);

        return true;
    }

    public function refresh(Database $database): void
    {
        $this->cache->put($this->key($database).':running', true, ImportDatabaseJob::TIMEOUT + 60);

        $this->put($database, [
            'state' => self::STATE_RUNNING,
            'statements' => 0,
            'error' => null,
            'detail' => null,
            'started_at' => CarbonImmutable::now()->toAtomString(),
            'finished_at' => null,
        ], ImportDatabaseJob::TIMEOUT + 60);
    }

    public function progress(Database $database, int $statements): void
    {
        $status = $this->get($database);

        if (($status['state'] ?? null) === self::STATE_RUNNING) {
            $this->put($database, array_merge($status, ['statements' => $statements]), ImportDatabaseJob::TIMEOUT + 60);
        }
    }

    public function complete(Database $database, int $statements): void
    {
        $this->finish($database, ['state' => self::STATE_COMPLETED, 'statements' => $statements]);
    }

    public function fail(Database $database, string $error, ?string $detail = null, ?int $statements = null): void
    {
        $this->finish($database, array_filter([
            'state' => self::STATE_FAILED,
            'error' => $error,
            'detail' => $detail,
            'statements' => $statements,
        ], fn ($value) => ! is_null($value)));
    }

    public function clear(Database $database): void
    {
        $this->cache->forget($this->key($database));
        $this->cache->forget($this->key($database).':running');
    }

    private function finish(Database $database, array $values): void
    {
        $status = $this->get($database) ?? ['statements' => 0, 'error' => null, 'detail' => null, 'started_at' => CarbonImmutable::now()->toAtomString()];

        $this->put($database, array_merge($status, $values, [
            'finished_at' => CarbonImmutable::now()->toAtomString(),
        ]), self::RESULT_LIFETIME);

        $this->cache->forget($this->key($database).':running');
    }

    private function put(Database $database, array $status, int $seconds): void
    {
        $this->cache->put($this->key($database), $status, $seconds);
    }

    private function key(Database $database): string
    {
        return 'database:import:'.$database->id;
    }
}
