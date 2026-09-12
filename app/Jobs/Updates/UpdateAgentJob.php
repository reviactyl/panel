<?php

namespace App\Jobs\Updates;

use App\Jobs\Job;
use App\Models\Node;
use App\Repositories\Agent\DaemonConfigurationRepository;
use App\Services\Helpers\SoftwareVersionService;
use App\Services\Updates\InstallationTypeService;
use App\Services\Updates\SoftwareUpdateStatusService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UpdateAgentJob extends Job implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public int $timeout = 180;

    public int $uniqueFor = 300;

    public function __construct(public int $nodeId, public string $version)
    {
        $this->queue = 'standard';
    }

    public function handle(
        DaemonConfigurationRepository $repository,
        SoftwareUpdateStatusService $statuses,
        SoftwareVersionService $versions,
    ): void {
        $key = $statuses->agentKey($this->nodeId);
        $statuses->set($key, 'installing', trans('admin/updates.status.agent_installing'), $this->version);

        $node = Node::query()->findOrFail($this->nodeId);
        $information = $repository->setNode($node)->getSystemInformation();
        if (InstallationTypeService::normalize($information['installation_type'] ?? null) !== InstallationTypeService::NATIVE) {
            throw new \RuntimeException('Automatic updates are unavailable for this Agent installation.');
        }
        $latestVersion = $versions->getDaemon();
        if ($this->version !== $latestVersion) {
            throw new \RuntimeException('The requested Agent version is not the current official release.');
        }
        if ($versions->isLatestDaemon((string) ($information['version'] ?? 'develop'))) {
            $statuses->set($key, 'complete', trans('admin/updates.status.agent_current'), $this->version);

            return;
        }

        $repository->setNode($node)->updateSystem($this->version);
        $statuses->set($key, 'restarting', trans('admin/updates.status.agent_restarting'), $this->version);
    }

    public function uniqueId(): string
    {
        return (string) $this->nodeId;
    }

    public function failed(?Throwable $exception): void
    {
        $statuses = app(SoftwareUpdateStatusService::class);
        $statuses->set(
            $statuses->agentKey($this->nodeId),
            'failed',
            $exception?->getMessage() ?: trans('admin/updates.status.agent_failed'),
            $this->version,
        );
    }
}
