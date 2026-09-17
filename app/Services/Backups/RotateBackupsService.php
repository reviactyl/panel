<?php

namespace App\Services\Backups;

use App\Models\Server;
use Illuminate\Database\ConnectionInterface;

class RotateBackupsService
{
    public function __construct(
        private ConnectionInterface $connection,
        private DeleteBackupService $deleteBackupService,
    ) {}

    /**
     * Removes the oldest unlocked successful backup when completed backups exceed the server limit.
     */
    public function handle(Server $server): void
    {
        if ($server->backup_limit <= 0) {
            return;
        }

        $this->connection->transaction(function () use ($server) {
            $backups = $server->backups()
                ->where(function ($query) {
                    $query->whereNull('completed_at')
                        ->orWhere('is_successful', true);
                })
                ->lockForUpdate()
                ->get();

            if ($backups->count() <= $server->backup_limit) {
                return;
            }

            $oldest = $backups
                ->where('is_locked', false)
                ->where('is_successful', true)
                ->whereNotNull('completed_at')
                ->sortBy('created_at')
                ->first();

            if ($oldest) {
                $this->deleteBackupService->handle($oldest);
            }
        });
    }
}
