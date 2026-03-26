<?php

namespace App\Services\Extensions;

use App\Models\Extension;
use App\Services\Extensions\Exceptions\ExtensionInstallException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class ExtensionManager
{
    public const API_VERSION = 'RCYL_v26';
    public const PACKAGE_EXTENSION = '.rext';
    public const MANIFEST_FILENAME = 'extension.json';
    public const SUPPORTED_API_VERSIONS = [
        self::API_VERSION,
    ];

    public function __construct(private readonly ExtensionManifestService $manifestService)
    {
    }

    public function installFromSource(string $source, ?string $archiveFilename = null): Extension
    {
        $archivePath = $source;
        $downloadedArchive = false;

        if (Str::startsWith($source, ['http://', 'https://'])) {
            if (!config('extensions.security.allow_remote_installs', true)) {
                throw new ExtensionInstallException('Remote extension installs are disabled by policy.');
            }

            $archivePath = $this->downloadArchive($source);
            $downloadedArchive = true;
        } else {
            $archivePath = $this->resolveLocalArchivePath($source);
        }

        try {
            $extension = $this->installFromArchive($archivePath, $archiveFilename ?? basename($archivePath));
        } finally {
            if ($downloadedArchive && is_file($archivePath)) {
                @unlink($archivePath);
            }
        }

        return $extension;
    }

    public function installFromArchive(string $archivePath, ?string $archiveFilename = null): Extension
    {
        if (!is_file($archivePath)) {
            throw new ExtensionInstallException('Archive file does not exist.');
        }

        if (!Str::endsWith(Str::lower($archivePath), Str::lower(self::PACKAGE_EXTENSION))) {
            throw new ExtensionInstallException('Extension package must use .rext format.');
        }

        [$extractPath, $rootPath] = $this->extractArchive($archivePath);
        $manifestPath = $rootPath . DIRECTORY_SEPARATOR . self::MANIFEST_FILENAME;
        $manifest = $this->manifestService->parseFromFile($manifestPath);
        $resolvedApiVersion = $this->assertApiVersionSupported($manifest);
        $this->manifestService->assertCompatible($manifest);

        $identifier = (string) $manifest['id'];
        $extensionPath = $this->extensionPath($identifier);
        $publicPath = $this->publicExtensionPath($identifier);

        File::ensureDirectoryExists(dirname($extensionPath));
        File::ensureDirectoryExists(dirname($publicPath));

        if (File::exists($extensionPath)) {
            File::deleteDirectory($extensionPath);
        }

        if (!File::copyDirectory($rootPath, $extensionPath)) {
            throw new ExtensionInstallException('Unable to copy extension files into extensions directory.');
        }

        $this->ensureCanonicalLayout($extensionPath);
        $this->publishAssets($identifier, $manifest);

        /** @var Extension $record */
        $record = Extension::query()->firstOrNew(['identifier' => $identifier]);
        $record->name = (string) $manifest['name'];
        $record->version = (string) $manifest['version'];
        $record->description = Arr::get($manifest, 'description');
        $record->author = Arr::get($manifest, 'author');
        $record->website = Arr::get($manifest, 'website');
        $record->update_url = Arr::get($manifest, 'update_url');
        $record->api_version = $resolvedApiVersion;
        $record->target_version = Arr::get($manifest, 'target_version');
        $record->manifest = $manifest;
        $record->installed_at ??= now();
        $record->extension_updated_at = now();
        $record->save();

        File::deleteDirectory($extractPath);

        return $record;
    }

    public function enable(string $identifier): Extension
    {
        /** @var Extension $extension */
        $extension = Extension::query()->where('identifier', $identifier)->firstOrFail();
        $manifest = $extension->manifest ?? [];
        $resolvedApiVersion = $this->assertApiVersionSupported($manifest);
        $this->manifestService->assertCompatible($manifest);

        $extension->api_version = $resolvedApiVersion;
        $extension->enabled = true;
        $extension->save();

        return $extension;
    }

    public function disable(string $identifier): Extension
    {
        /** @var Extension $extension */
        $extension = Extension::query()->where('identifier', $identifier)->firstOrFail();
        $extension->enabled = false;
        $extension->save();

        return $extension;
    }

    public function remove(string $identifier): void
    {
        /** @var Extension $extension */
        $extension = Extension::query()->where('identifier', $identifier)->firstOrFail();

        if (File::exists($extension->install_path)) {
            File::deleteDirectory($extension->install_path);
        }

        $publicPath = $this->publicExtensionPath($extension->identifier);
        if (File::exists($publicPath)) {
            File::deleteDirectory($publicPath);
        }

        $extension->delete();
    }

    /**
     * @return Collection<int, Extension>
     */
    public function all(): Collection
    {
        return Extension::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Extension>
     */
    public function enabled(): Collection
    {
        return Extension::query()->enabled()->orderBy('name')->get();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function frontendRegistry(): array
    {
        return $this->enabled()->map(function (Extension $extension): array {
            $manifest = $this->resolveRuntimeManifest($extension);
            $frontend = Arr::get($manifest, 'frontend', []);

            $slots = collect(Arr::wrap(Arr::get($frontend, 'slots', [])))
                ->map(function ($slot) use ($extension) {
                    if (!is_array($slot)) {
                        return $slot;
                    }

                    $module = Arr::get($slot, 'module');
                    if (is_string($module)) {
                        $normalized = $this->normalizeFrontendModulePath($module);
                        $slot['module'] = $this->versionedFrontendModulePath($normalized, $extension);
                    }

                    return $slot;
                })
                ->values()
                ->all();

            $dashboardRoutes = collect(Arr::wrap(Arr::get($frontend, 'routes.dashboardRouter', [])))
                ->map(function ($route) use ($extension) {
                    if (!is_array($route)) {
                        return $route;
                    }

                    $module = Arr::get($route, 'module');
                    if (is_string($module)) {
                        $normalized = $this->normalizeFrontendModulePath($module);
                        $route['module'] = $this->versionedFrontendModulePath($normalized, $extension);
                    }

                    return $route;
                })
                ->values()
                ->all();

            $serverRoutes = collect(Arr::wrap(Arr::get($frontend, 'routes.serverRouter', [])))
                ->map(function ($route) use ($extension) {
                    if (!is_array($route)) {
                        return $route;
                    }

                    $module = Arr::get($route, 'module');
                    if (is_string($module)) {
                        $normalized = $this->normalizeFrontendModulePath($module);
                        $route['module'] = $this->versionedFrontendModulePath($normalized, $extension);
                    }

                    return $route;
                })
                ->values()
                ->all();

            return [
                'id' => $extension->identifier,
                'name' => $extension->name,
                'version' => $extension->version,
                'permissions' => Arr::wrap(Arr::get($manifest, 'permissions', [])),
                'feature_flags' => Arr::wrap(Arr::get($manifest, 'feature_flags', [])),
                'frontend' => [
                    'build_strategy' => Arr::get($frontend, 'build_strategy', 'precompiled'),
                    'entry_points' => $this->resolveFrontendEntryPoints($manifest),
                    'slots' => $slots,
                    'routes' => [
                        'dashboardRouter' => $dashboardRoutes,
                        'serverRouter' => $serverRoutes,
                    ],
                ],
            ];
        })->values()->all();
    }

    /**
     * Prefer current manifest on disk so dev updates are reflected immediately.
     *
     * @return array<string, mixed>
     */
    private function resolveRuntimeManifest(Extension $extension): array
    {
        $manifestPath = rtrim($extension->install_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
            . self::MANIFEST_FILENAME;

        if (is_file($manifestPath)) {
            try {
                return $this->manifestService->parseFromFile($manifestPath);
            } catch (\Throwable $exception) {
                Log::warning('Failed to parse extension manifest from disk, falling back to DB snapshot.', [
                    'extension' => $extension->identifier,
                    'manifest_path' => $manifestPath,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return is_array($extension->manifest) ? $extension->manifest : [];
    }

    public function applySchedules(Schedule $schedule): void
    {
        $this->enabled()->each(function (Extension $extension) use ($schedule): void {
            $schedules = Arr::wrap(Arr::get($extension->manifest, 'backend.schedules', []));

            foreach ($schedules as $item) {
                if (!is_array($item) || !isset($item['command']) || !is_string($item['command'])) {
                    continue;
                }

                $event = $schedule->command($item['command']);

                if (isset($item['cron']) && is_string($item['cron'])) {
                    $event->cron($item['cron']);
                    continue;
                }

                $frequency = $item['frequency'] ?? null;
                if (is_string($frequency) && method_exists($event, $frequency)) {
                    $event->{$frequency}();
                }
            }
        });
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function extractArchive(string $archivePath): array
    {
        $tempRoot = rtrim((string) config('extensions.storage.temp_path'), DIRECTORY_SEPARATOR);
        File::ensureDirectoryExists($tempRoot);

        $extractPath = $tempRoot . DIRECTORY_SEPARATOR . Str::uuid()->toString();
        File::ensureDirectoryExists($extractPath);

        $zip = new ZipArchive();
        $open = $zip->open($archivePath);
        if ($open !== true) {
            throw new ExtensionInstallException('Could not open extension archive.');
        }

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $name = $zip->getNameIndex($i);
            if (!is_string($name)) {
                continue;
            }

            if (
                Str::contains($name, ['../', '..\\']) ||
                Str::startsWith($name, ['/','\\'])
            ) {
                $zip->close();
                throw new ExtensionInstallException('Archive contains invalid paths.');
            }
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $manifestName = self::MANIFEST_FILENAME;
        if (is_file($extractPath . DIRECTORY_SEPARATOR . $manifestName)) {
            return [$extractPath, $extractPath];
        }

        $directories = collect(File::directories($extractPath));
        if ($directories->count() === 1) {
            $root = (string) $directories->first();
            if (is_file($root . DIRECTORY_SEPARATOR . $manifestName)) {
                return [$extractPath, $root];
            }
        }

        throw new ExtensionInstallException('Archive does not contain extension.json at the package root.');
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function publishAssets(string $identifier, array $manifest): void
    {
        $sourceRoot = $this->extensionPath($identifier);
        $destinationRoot = $this->publicExtensionPath($identifier);

        if (File::exists($destinationRoot)) {
            File::deleteDirectory($destinationRoot);
        }

        File::ensureDirectoryExists($destinationRoot);

        $sourcePublic = $sourceRoot . DIRECTORY_SEPARATOR . 'public';
        if (File::isDirectory($sourcePublic)) {
            File::copyDirectory($sourcePublic, $destinationRoot);
        }

        $entries = $this->resolveFrontendEntryPoints($manifest);
        foreach ($entries as $entry) {
            if (!is_string($entry) || trim($entry) === '') {
                continue;
            }

            $normalized = ltrim($entry, '/');
            $source = $sourceRoot . DIRECTORY_SEPARATOR . $normalized;

            if (!File::exists($source) || !is_file($source)) {
                continue;
            }

            $target = $destinationRoot . DIRECTORY_SEPARATOR . $normalized;
            File::ensureDirectoryExists(dirname($target));
            File::copy($source, $target);
        }
    }

    /**
     * @param array<string, mixed> $manifest
     * @return array<int, string>
     */
    private function resolveFrontendEntryPoints(array $manifest): array
    {
        $entryPoints = Arr::wrap(Arr::get($manifest, 'frontend.entry_points', []));
        $sourceEntry = Arr::get($manifest, 'frontend.source.entry');
        $sourceEntries = Arr::wrap(Arr::get($manifest, 'frontend.source.entries', []));

        $candidates = collect([...$entryPoints, $sourceEntry, ...$sourceEntries])
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value): string => $this->normalizeFrontendModulePath($value))
            ->unique()
            ->values()
            ->all();

        return $candidates;
    }

    private function normalizeFrontendModulePath(string $path): string
    {
        $normalized = ltrim(str_replace('\\', '/', trim($path)), '/');

        if (Str::startsWith($normalized, 'frontend/src/')) {
            if (Str::endsWith($normalized, '.tsx') || Str::endsWith($normalized, '.ts')) {
                return preg_replace('/\.(tsx|ts)$/', '.js', str_replace('frontend/src/', 'frontend/dist/', $normalized))
                    ?? $normalized;
            }
        }

        return $normalized;
    }

    private function versionedFrontendModulePath(string $path, Extension $extension): string
    {
        $cleanPath = preg_replace('/\?.*$/', '', $path) ?? $path;
        $file = rtrim($extension->install_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $cleanPath;

        if (!is_file($file)) {
            return $path;
        }

        $mtime = @filemtime($file);
        if (!is_int($mtime)) {
            return $path;
        }

        return $cleanPath . '?v=' . $mtime;
    }

    private function ensureCanonicalLayout(string $extensionPath): void
    {
        foreach (['backend', 'frontend', 'public', 'data', 'cache', 'private'] as $directory) {
            File::ensureDirectoryExists($extensionPath . DIRECTORY_SEPARATOR . $directory);
        }
    }

    private function extensionPath(string $identifier): string
    {
        return rtrim(base_path('extensions'), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $identifier;
    }

    private function publicExtensionPath(string $identifier): string
    {
        return rtrim((string) config('extensions.storage.public_path'), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $identifier;
    }

    private function downloadArchive(string $url): string
    {
        $tempRoot = rtrim((string) config('extensions.storage.temp_path'), DIRECTORY_SEPARATOR);
        File::ensureDirectoryExists($tempRoot);

        $target = $tempRoot . DIRECTORY_SEPARATOR . Str::uuid()->toString() . self::PACKAGE_EXTENSION;
        $response = Http::timeout(60)->get($url);

        if (!$response->successful()) {
            throw new ExtensionInstallException('Could not download extension archive from URL.');
        }

        File::put($target, $response->body());

        return $target;
    }

    private function resolveLocalArchivePath(string $source): string
    {
        $source = trim($source);
        if ($source === '') {
            return $source;
        }

        $candidates = [$source];

        if (!Str::endsWith(Str::lower($source), Str::lower(self::PACKAGE_EXTENSION))) {
            $candidates[] = $source . self::PACKAGE_EXTENSION;
        }

        $baseCandidates = [];
        foreach ($candidates as $candidate) {
            $baseCandidates[] = base_path($candidate);
        }

        foreach (array_unique([...$candidates, ...$baseCandidates]) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return base_path($source);
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function assertApiVersionSupported(array $manifest): string
    {
        $apiVersion = Arr::get($manifest, 'api_version');

        $requestedVersions = [];
        if (is_string($apiVersion)) {
            $value = trim($apiVersion);
            if ($value !== '') {
                $requestedVersions[] = $value;
            }
        } elseif (is_array($apiVersion)) {
            $requestedVersions = collect($apiVersion)
                ->filter(fn ($item) => is_string($item) && trim($item) !== '')
                ->map(fn (string $item) => trim($item))
                ->unique()
                ->values()
                ->all();
        }

        if (count($requestedVersions) === 0) {
            throw new ExtensionInstallException('Manifest api_version is required.');
        }

        foreach ($requestedVersions as $version) {
            if (in_array($version, self::SUPPORTED_API_VERSIONS, true)) {
                return $version;
            }
        }

        if (count($requestedVersions) === 1) {
            throw new ExtensionInstallException(sprintf(
                'Unsupported extension api_version "%s". Supported: %s.',
                $requestedVersions[0],
                implode(', ', self::SUPPORTED_API_VERSIONS)
            ));
        }

        throw new ExtensionInstallException(sprintf(
            'Unsupported extension api_version list [%s]. Supported: %s.',
            implode(', ', $requestedVersions),
            implode(', ', self::SUPPORTED_API_VERSIONS)
        ));
    }

    /**
     * @return list<string>
     */
    public static function supportedApiVersions(): array
    {
        return self::SUPPORTED_API_VERSIONS;
    }
}
