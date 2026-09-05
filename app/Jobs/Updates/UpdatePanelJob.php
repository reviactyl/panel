<?php

namespace App\Jobs\Updates;

use App\Jobs\Job;
use App\Services\Updates\PanelUpdateService;
use App\Services\Updates\SoftwareUpdateStatusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;

class UpdatePanelJob extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public int $timeout = 3600;

    public int $tries = 1;

    public bool $failOnTimeout = true;

    public function __construct(public string $version)
    {
        $this->queue = 'standard';
        $connection = (string) config('queue.default');
        $driver = (string) config("queue.connections.{$connection}.driver");
        if ($driver === 'sqs') {
            throw new RuntimeException('Automatic Panel updates are unavailable when SQS is the queue connection.');
        }

        $retryAfter = config("queue.connections.{$connection}.retry_after");
        if ($connection !== 'sync' && (! is_numeric($retryAfter) || (int) $retryAfter <= $this->timeout)) {
            throw new RuntimeException('The queue retry_after setting must exceed the Panel update job timeout.');
        }
    }

    public function handle(PanelUpdateService $updater): void
    {
        $updater->update($this->version);
    }

    public function failed(?Throwable $exception): void
    {
        $statuses = app(SoftwareUpdateStatusService::class);
        $statuses->set(
            $statuses->panelKey(),
            'failed',
            $exception?->getMessage() ?: trans('admin/updates.status.panel_failed'),
            $this->version,
        );
    }
}
