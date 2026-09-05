<?php

namespace App\Services\Updates;

class InstallationTypeService
{
    public const NATIVE = 'native';

    public const DOCKER = 'docker';

    public const UNKNOWN = 'unknown';

    public function panel(): string
    {
        return self::normalize(config('panel.installation_type'));
    }

    public function panelSupportsAutomaticUpdates(): bool
    {
        if ($this->panel() !== self::NATIVE || config('app.version') === 'canary') {
            return false;
        }

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        return in_array($driver, ['mysql', 'mariadb', 'pgsql'], true);
    }

    public function panelSupportsSoftwareUpdatesPage(): bool
    {
        return $this->panel() === self::NATIVE;
    }

    public static function normalize(mixed $value): string
    {
        return match (strtolower(trim((string) $value))) {
            self::NATIVE => self::NATIVE,
            self::DOCKER => self::DOCKER,
            default => self::UNKNOWN,
        };
    }
}
