<?php

namespace App\Support;

use Illuminate\Database\Migrations\Migrator;

class InstallationState
{
    /**
     * InstallationState constructor.
     */
    public function __construct(private Migrator $migrator) {}

    /**
     * Determine if the application has already been installed.
     */
    public function isInstalled(): bool
    {
        if (! $this->migrator->repositoryExists()) {
            return false;
        }

        return count($this->migrator->getRepository()->getRan()) > 0;
    }
}
