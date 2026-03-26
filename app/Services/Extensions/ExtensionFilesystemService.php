<?php

namespace App\Services\Extensions;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class ExtensionFilesystemService
{
    public function registerDisks(string $identifier, string $installPath): void
    {
        $publicName = $this->publicDiskName($identifier);
        $privateName = $this->privateDiskName($identifier);

        Config::set("filesystems.disks.{$publicName}", $this->publicDiskConfig($identifier));
        Config::set("filesystems.disks.{$privateName}", $this->privateDiskConfig($installPath));
    }

    public function configurePlaceholderDisks(string $identifier, string $installPath): void
    {
        Config::set('filesystems.disks.{fs}', $this->publicDiskConfig($identifier));
        Config::set('filesystems.disks.{fs/private}', $this->privateDiskConfig($installPath));
    }

    public function publicDiskName(string $identifier): string
    {
        return 'ext_' . $this->sanitizeIdentifier($identifier) . '_fs';
    }

    public function privateDiskName(string $identifier): string
    {
        return 'ext_' . $this->sanitizeIdentifier($identifier) . '_private';
    }

    public function publicRootPath(string $identifier): string
    {
        $root = rtrim((string) config('extensions.storage.public_fs_path', storage_path('extensions')), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $identifier;

        File::ensureDirectoryExists($root);

        return $root;
    }

    public function privateRootPath(string $installPath): string
    {
        $root = rtrim($installPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'private';
        File::ensureDirectoryExists($root);

        return $root;
    }

    /**
     * @return array<string, mixed>
     */
    private function publicDiskConfig(string $identifier): array
    {
        return [
            'driver' => 'local',
            'root' => $this->publicRootPath($identifier),
            'throw' => false,
            'url' => '/fs/' . $identifier,
            'visibility' => 'public',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function privateDiskConfig(string $installPath): array
    {
        return [
            'driver' => 'local',
            'root' => $this->privateRootPath($installPath),
            'throw' => false,
            'visibility' => 'private',
        ];
    }

    private function sanitizeIdentifier(string $identifier): string
    {
        return preg_replace('/[^a-z0-9_]+/i', '_', strtolower($identifier)) ?? $identifier;
    }
}
