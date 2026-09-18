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
        return $this->panelAutomaticUpdateError() === null;
    }

    public function panelAutomaticUpdateError(): ?string
    {
        if ($this->panel() !== self::NATIVE) {
            return 'unsupported_installation';
        }

        if (config('app.version') === 'canary') {
            return 'development_build';
        }

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        return in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlite'], true) ? null : 'invalid_database';
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
