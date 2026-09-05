<?php

namespace App\Jobs\Updates;

use App\Jobs\Job;
use App\Services\Updates\PanelUpdateService;
use App\Services\Updates\SoftwareUpdateStatusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UpdatePanelJob extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public int $timeout = 900;

    public function __construct(public string $version)
    {
        $this->queue = 'standard';
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
