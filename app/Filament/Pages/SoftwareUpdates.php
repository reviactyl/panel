<?php

namespace App\Filament\Pages;

use App\Jobs\Updates\UpdateAgentJob;
use App\Jobs\Updates\UpdatePanelJob;
use App\Models\Node;
use App\Repositories\Agent\DaemonConfigurationRepository;
use App\Services\Helpers\SoftwareVersionService;
use App\Services\Updates\InstallationTypeService;
use App\Services\Updates\SoftwareUpdateStatusService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class SoftwareUpdates extends Page
{
    private const BUSY_STATES = ['queued', 'downloading', 'validating', 'backing_up', 'installing', 'migrating', 'restarting'];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-arrow-path';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.software-updates';

    public array $panel = [];

    public array $agents = [];

    public function mount(): void
    {
        $this->refreshUpdates();
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/updates.navigation');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return app(InstallationTypeService::class)->panelSupportsSoftwareUpdatesPage();
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.service.title');
    }

    public function getTitle(): string
    {
        return trans('admin/updates.title');
    }

    public function getHeading(): string
    {
        return trans('admin/updates.title');
    }

    public function refreshUpdates(): void
    {
        $versions = app(SoftwareVersionService::class);
        $installationTypes = app(InstallationTypeService::class);
        $statuses = app(SoftwareUpdateStatusService::class);
        $latestPanel = $versions->getPanel();
        $currentPanel = (string) config('app.version');

        $this->panel = [
            'current' => $currentPanel,
            'latest' => $latestPanel,
            'latest_available' => $latestPanel !== 'error',
            'installation_type' => $installationTypes->panel(),
            'automatic_supported' => $installationTypes->panelSupportsAutomaticUpdates(),
            'outdated' => $latestPanel !== 'error' && ! $versions->isLatestPanel(),
            'status' => $statuses->get($statuses->panelKey()),
        ];

        $repository = app(DaemonConfigurationRepository::class);
        $latestAgent = $versions->getDaemon();
        $this->agents = Node::query()->orderBy('name')->get()->map(function (Node $node) use ($repository, $latestAgent, $versions, $statuses): array {
            try {
                $information = $repository->setNode($node)->getSystemInformation();
                $current = (string) ($information['version'] ?? 'unknown');
                $installationType = InstallationTypeService::normalize($information['installation_type'] ?? null);
                $statusKey = $statuses->agentKey($node->id);
                $status = $statuses->get($statusKey);
                if (
                    ($status['state'] ?? null) === 'restarting'
                    && isset($status['version'])
                    && version_compare($current, (string) $status['version'], '>=')
                ) {
                    $statuses->set($statusKey, 'complete', trans('admin/updates.status.agent_complete'), (string) $status['version']);
                    $status = $statuses->get($statusKey);
                }

                return [
                    'id' => $node->id,
                    'name' => $node->name,
                    'current' => $current,
                    'latest' => $latestAgent,
                    'latest_available' => $latestAgent !== 'error',
                    'installation_type' => $installationType,
                    'outdated' => $latestAgent !== 'error' && $current !== 'unknown' && ! $versions->isLatestDaemon($current),
                    'reachable' => true,
                    'status' => $status,
                ];
            } catch (\Throwable) {
                return [
                    'id' => $node->id,
                    'name' => $node->name,
                    'current' => 'unavailable',
                    'latest' => $latestAgent,
                    'latest_available' => $latestAgent !== 'error',
                    'installation_type' => InstallationTypeService::UNKNOWN,
                    'outdated' => false,
                    'reachable' => false,
                    'status' => $statuses->get($statuses->agentKey($node->id)),
                ];
            }
        })->all();
    }

    public function updatePanel(): void
    {
        $installationTypes = app(InstallationTypeService::class);
        if (
            ! $installationTypes->panelSupportsAutomaticUpdates()
            || ! ($this->panel['outdated'] ?? false)
            || $this->updateInProgress($this->panel['status'] ?? null)
        ) {
            Notification::make()->warning()->title(trans('admin/updates.unavailable'))->send();

            return;
        }

        $version = (string) $this->panel['latest'];
        $statuses = app(SoftwareUpdateStatusService::class);
        $statuses->set($statuses->panelKey(), 'queued', trans('admin/updates.status.queued'), $version);
        try {
            UpdatePanelJob::dispatch($version);
        } catch (\Throwable $exception) {
            $statuses->set($statuses->panelKey(), 'failed', trans('admin/updates.status.panel_failed'), $version);
            report($exception);
            Notification::make()->danger()->title(trans('admin/updates.status.panel_failed'))->send();
            $this->panel['status'] = $statuses->get($statuses->panelKey());

            return;
        }
        Notification::make()->success()->title(trans('admin/updates.panel_queued'))->send();
        $this->refreshUpdates();
    }

    public function updateAgent(int $nodeId): void
    {
        $node = Node::query()->find($nodeId);
        if (! $node) {
            Notification::make()->warning()->title(trans('admin/updates.unavailable'))->send();

            return;
        }

        try {
            $versions = app(SoftwareVersionService::class);
            $information = app(DaemonConfigurationRepository::class)->setNode($node)->getSystemInformation();
            $version = $versions->getDaemon();
            if (
                $version === 'error'
                || $versions->isLatestDaemon((string) ($information['version'] ?? 'develop'))
                || InstallationTypeService::normalize($information['installation_type'] ?? null) !== InstallationTypeService::NATIVE
            ) {
                throw new \RuntimeException();
            }
        } catch (\Throwable) {
            Notification::make()->warning()->title(trans('admin/updates.unavailable'))->send();

            return;
        }

        $statuses = app(SoftwareUpdateStatusService::class);
        if ($this->updateInProgress($statuses->get($statuses->agentKey($nodeId)))) {
            Notification::make()->warning()->title(trans('admin/updates.already_running'))->send();

            return;
        }
        if (! $this->dispatchAgentUpdate($statuses, $nodeId, $version)) {
            Notification::make()->danger()->title(trans('admin/updates.status.agent_failed'))->send();
            foreach ($this->agents as &$agent) {
                if ($agent['id'] === $nodeId) {
                    $agent['status'] = $statuses->get($statuses->agentKey($nodeId));
                    break;
                }
            }
            unset($agent);

            return;
        }
        Notification::make()->success()->title(trans('admin/updates.agent_queued', ['name' => $node->name]))->send();
        $this->refreshUpdates();
    }

    public function updateAllAgents(): void
    {
        $this->refreshUpdates();
        $queued = 0;
        $statuses = app(SoftwareUpdateStatusService::class);
        foreach ($this->agents as $agent) {
            if (! $agent['reachable'] || ! $agent['outdated'] || $agent['installation_type'] !== InstallationTypeService::NATIVE) {
                continue;
            }
            if ($this->updateInProgress($statuses->get($statuses->agentKey($agent['id'])))) {
                continue;
            }

            if ($this->dispatchAgentUpdate($statuses, $agent['id'], $agent['latest'])) {
                $queued++;
            }
        }

        Notification::make()
            ->title($queued > 0 ? trans('admin/updates.agents_queued', ['count' => $queued]) : trans('admin/updates.no_agents_queued'))
            ->color($queued > 0 ? 'success' : 'warning')
            ->send();
        $this->refreshUpdates();
    }

    private function updateInProgress(?array $status): bool
    {
        return in_array($status['state'] ?? null, self::BUSY_STATES, true);
    }

    private function dispatchAgentUpdate(SoftwareUpdateStatusService $statuses, int $nodeId, string $version): bool
    {
        $key = $statuses->agentKey($nodeId);
        $statuses->set($key, 'queued', trans('admin/updates.status.queued'), $version);
        $job = new UpdateAgentJob($nodeId, $version);

        try {
            dispatch($job);

            return true;
        } catch (\Throwable $exception) {
            $statuses->set($key, 'failed', trans('admin/updates.status.agent_failed'), $version);
            report($exception);
            try {
                (new UniqueLock(app(CacheRepository::class)))->release($job);
            } catch (\Throwable $lockException) {
                report($lockException);
            }

            return false;
        }
    }
}
