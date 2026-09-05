<?php

namespace App\Console\Commands;

use App\Services\Helpers\SoftwareVersionService;
use App\Services\Updates\InstallationTypeService;
use App\Services\Updates\PanelUpdateService;
use Illuminate\Console\Command;
use Throwable;

class UpgradeCommand extends Command
{
    protected $signature = 'p:upgrade';

    protected $description = 'Back up and update a native Reviactyl Panel installation';

    public function handle(
        InstallationTypeService $installationTypes,
        SoftwareVersionService $versions,
        PanelUpdateService $updater,
    ): int {
        if (! $installationTypes->panelSupportsAutomaticUpdates()) {
            $this->error('Automatic Panel updates require a released native installation using MySQL, MariaDB, or PostgreSQL.');
            $this->line('See https://reviactyl.app/docs/panel/updating-the-panel for the Docker update process.');

            return self::FAILURE;
        }

        $version = $versions->getPanel();
        if ($version === 'error') {
            $this->error('Unable to resolve the latest Panel release.');

            return self::FAILURE;
        }

        if ($this->input->isInteractive() && ! $this->confirm("Back up and update this Panel to v{$version}?")) {
            return self::SUCCESS;
        }

        try {
            $updater->update($version);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("The Panel was updated to v{$version}.");

        return self::SUCCESS;
    }
}
