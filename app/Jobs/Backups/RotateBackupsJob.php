<?php

namespace App\Jobs\Backups;

use App\Jobs\Job;
use App\Models\Server;
use App\Services\Backups\RotateBackupsService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RotateBackupsJob extends Job implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(public int $serverId, public int $backupId)
    {
        $this->queue = 'standard';
    }

    /**
     * Removes excess backups after a successful replacement, retrying failures through the queue.
     */
    public function handle(RotateBackupsService $service): void
    {
        $server = Server::query()->find($this->serverId);
        if ($server) {
            $service->handle($server);
        }
    }

    /**
     * Uses increasing delays between the three total attempts.
     *
     * @return int[]
     */
    public function backoff(): array
    {
        return [30, 120];
    }

    /**
     * Prevents duplicate cleanup jobs for the same completed backup.
     */
    public function uniqueId(): string
    {
        return (string) $this->backupId;
    }
}
