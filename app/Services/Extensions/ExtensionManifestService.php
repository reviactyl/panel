<?php

namespace App\Services\Extensions;

use App\Services\Extensions\Exceptions\ExtensionInstallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ExtensionManifestService
{
    /**
     * @return array<string, mixed>
     */
    public function parseFromFile(string $manifestPath): array
    {
        if (!is_file($manifestPath)) {
            throw new ExtensionInstallException('Missing extension.json in package root.');
        }

        $raw = file_get_contents($manifestPath);
        if ($raw === false) {
            throw new ExtensionInstallException('Could not read extension.json.');
        }

        $manifest = json_decode($raw, true);
        if (!is_array($manifest)) {
            throw new ExtensionInstallException('Invalid extension.json. Expected a JSON object.');
        }

        return $this->normalize($manifest);
    }

    /**
     * @param array<string, mixed> $manifest
     * @return array<string, mixed>
     */
    public function normalize(array $manifest): array
    {
        foreach (['id', 'name', 'version'] as $required) {
            if (!isset($manifest[$required]) || !is_string($manifest[$required]) || trim($manifest[$required]) === '') {
                throw new ExtensionInstallException("Missing required manifest field: {$required}");
            }
        }

        $identifier = trim((string) $manifest['id']);
        if (!preg_match('/^[a-z0-9][a-z0-9-_\.]*$/i', $identifier)) {
            throw new ExtensionInstallException('Manifest id must be a slug-like identifier.');
        }

        $manifest['id'] = Str::lower($identifier);
        $manifest['description'] = Arr::get($manifest, 'description');
        $manifest['author'] = Arr::get($manifest, 'author');
        $manifest['website'] = Arr::get($manifest, 'website');
        $updateUrl = Arr::get($manifest, 'update_url');
        $manifest['update_url'] = is_string($updateUrl) && trim($updateUrl) !== '' ? trim($updateUrl) : null;
        $apiVersion = Arr::get($manifest, 'api_version', Arr::get($manifest, 'apiVersion'));
        if (is_array($apiVersion)) {
            $manifest['api_version'] = collect($apiVersion)
                ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                ->map(fn (string $item) => trim($item))
                ->values()
                ->all();
        } elseif (is_string($apiVersion)) {
            $manifest['api_version'] = trim($apiVersion);
        } else {
            $manifest['api_version'] = null;
        }
        $manifest['target_version'] = Arr::get($manifest, 'target_version');
        $manifest['signature'] = Arr::get($manifest, 'signature');

        $manifest['backend'] = is_array(Arr::get($manifest, 'backend')) ? $manifest['backend'] : [];
        $manifest['frontend'] = is_array(Arr::get($manifest, 'frontend')) ? $manifest['frontend'] : [];
        $manifest['assets'] = is_array(Arr::get($manifest, 'assets')) ? $manifest['assets'] : [];
        $manifest['permissions'] = is_array(Arr::get($manifest, 'permissions')) ? $manifest['permissions'] : [];
        $manifest['feature_flags'] = is_array(Arr::get($manifest, 'feature_flags')) ? $manifest['feature_flags'] : [];

        return $manifest;
    }

    /**
     * @param array<string, mixed> $manifest
     */
    public function assertCompatible(array $manifest): void
    {
        if (!config('extensions.security.enforce_compatibility', true)) {
            return;
        }

        $panelVersion = (string) config('app.version', 'canary');
        $targetVersion = Arr::get($manifest, 'target_version');

        if (!$this->isCalver($panelVersion)) {
            return;
        }

        if (is_string($targetVersion) && $this->isCalver($targetVersion) && version_compare($panelVersion, $targetVersion, '>')) {
            throw new ExtensionInstallException("Extension requires panel version <= {$targetVersion}. Current: {$panelVersion}");
        }
    }

    private function isCalver(string $version): bool
    {
        return preg_match('/^\d+\.\d+\.\d+([-.+].+)?$/', $version) === 1;
    }
}
