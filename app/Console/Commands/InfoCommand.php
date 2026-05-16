<?php

namespace App\Console\Commands;

use App\Services\Helpers\SoftwareVersionService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class InfoCommand extends Command
{
    protected $description = 'Displays the application, database, and email configurations along with the panel version.';

    protected $signature = 'p:info';

    /**
     * VersionCommand constructor.
     */
    public function __construct(private ConfigRepository $config, private SoftwareVersionService $versionService)
    {
        parent::__construct();
    }

    /**
     * Handle execution of command.
     */
    public function handle()
    {
        $this->output->title(trans('command/messages.info.titles.version'));
        $this->table([], [
            [trans('command/messages.info.fields.panel_version'), $this->config->get('app.version')],
            [trans('command/messages.info.fields.latest_version'), $this->versionService->getPanel()],
            [trans('command/messages.info.fields.up_to_date'), $this->versionService->isLatestPanel() ? trans('command/messages.info.yes') : $this->formatText(trans('command/messages.info.no'), 'bg=red')],
            [trans('command/messages.info.fields.unique_identifier'), $this->config->get('panel.service.author')],
        ], 'compact');

        $this->output->title(trans('command/messages.info.titles.application'));
        $this->table([], [
            [trans('command/messages.info.fields.environment'), $this->formatText($this->config->get('app.env'), $this->config->get('app.env') === 'production' ?: 'bg=red')],
            [trans('command/messages.info.fields.debug_mode'), $this->formatText($this->config->get('app.debug') ? trans('command/messages.info.yes') : trans('command/messages.info.no'), ! $this->config->get('app.debug') ?: 'bg=red')],
            [trans('command/messages.info.fields.installation_url'), $this->config->get('app.url')],
            [trans('command/messages.info.fields.installation_directory'), base_path()],
            [trans('command/messages.info.fields.timezone'), $this->config->get('app.timezone')],
            [trans('command/messages.info.fields.cache_driver'), $this->config->get('cache.default')],
            [trans('command/messages.info.fields.queue_driver'), $this->config->get('queue.default')],
            [trans('command/messages.info.fields.session_driver'), $this->config->get('session.driver')],
            [trans('command/messages.info.fields.filesystem_driver'), $this->config->get('filesystems.default')],
            [trans('command/messages.info.fields.proxies'), $this->config->get('trustedproxies.proxies')],
        ], 'compact');

        $this->output->title(trans('command/messages.info.titles.database'));
        $driver = $this->config->get('database.default');
        $this->table([], [
            [trans('command/messages.info.fields.driver'), $driver],
            [trans('command/messages.info.fields.host'), $this->config->get("database.connections.$driver.host")],
            [trans('command/messages.info.fields.port'), $this->config->get("database.connections.$driver.port")],
            [trans('command/messages.info.fields.database'), $this->config->get("database.connections.$driver.database")],
            [trans('command/messages.info.fields.username'), $this->config->get("database.connections.$driver.username")],
        ], 'compact');

        // TODO: Update this to handle other mail drivers
        $this->output->title(trans('command/messages.info.titles.email'));
        $this->table([], [
            [trans('command/messages.info.fields.driver'), $this->config->get('mail.default')],
            [trans('command/messages.info.fields.host'), $this->config->get('mail.mailers.smtp.host')],
            [trans('command/messages.info.fields.port'), $this->config->get('mail.mailers.smtp.port')],
            [trans('command/messages.info.fields.username'), $this->config->get('mail.mailers.smtp.username')],
            [trans('command/messages.info.fields.from_address'), $this->config->get('mail.from.address')],
            [trans('command/messages.info.fields.from_name'), $this->config->get('mail.from.name')],
            [trans('command/messages.info.fields.encryption'), $this->config->get('mail.mailers.smtp.encryption')],
        ], 'compact');
    }

    /**
     * Format output in a Name: Value manner.
     */
    private function formatText(string $value, string $opts = ''): string
    {
        return sprintf('<%s>%s</>', $opts, $value);
    }
}
