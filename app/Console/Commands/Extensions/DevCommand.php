<?php

namespace App\Console\Commands\Extensions;

use App\Models\Extension;
use App\Services\Extensions\ExtensionManifestService;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DevCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'd:extensions:dev
                            {source? : Extension source directory or ID under /extensions}
                            {--id= : Override extension identifier from extension.json}
                            {--unlink : Remove dev links instead of creating them}
                            {--enable : Enable extension after linking if installed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link an extension source directory for live local development.';

    public function __construct(private readonly ExtensionManifestService $manifestService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = trim((string) ($this->argument('source') ?: ''));

        if ($source === '') {
            if ((bool) $this->option('no-interaction')) {
                $this->error('The source argument is required in non-interactive mode.');

                return self::FAILURE;
            }

            $source = trim((string) $this->ask('Extension source directory or ID', 'my-extension'));
        }

        $sourcePath = $this->resolveSourcePath($source);

        if ($sourcePath === '') {
            $this->error('A valid source directory is required.');

            return self::FAILURE;
        }

        $sourceReal = realpath($sourcePath);
        if ($sourceReal === false || !is_dir($sourceReal)) {
            $this->error('Source directory does not exist.');

            return self::FAILURE;
        }

        $manifestPath = $sourceReal . '/extension.json';
        if (!is_file($manifestPath)) {
            $this->error('Source directory must contain extension.json.');

            return self::FAILURE;
        }

        try {
            $manifest = $this->manifestService->parseFromFile($manifestPath);
        } catch (\Throwable $exception) {
            $this->error('extension.json is invalid: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $identifier = trim((string) ($this->option('id') ?: ($manifest['id'] ?? '')));
        if ($identifier === '') {
            $this->error('Could not resolve extension identifier.');

            return self::FAILURE;
        }

        $installLinkPath = base_path('extensions/' . $identifier);
        $publicRootPath = public_path('extensions/' . $identifier);
        $publicFrontendLinkPath = $publicRootPath . '/frontend';
        $sourceFrontendPath = $sourceReal . '/frontend';

        if ((bool) $this->option('unlink')) {
            $this->removeLinkIfExists($installLinkPath);
            $this->removeLinkIfExists($publicFrontendLinkPath);

            if (is_dir($publicRootPath) && count(File::allFiles($publicRootPath)) === 0 && count(File::directories($publicRootPath)) === 0) {
                @rmdir($publicRootPath);
            }

            $this->info("Removed dev links for extension {$identifier}.");

            return self::SUCCESS;
        }

        if (is_dir($installLinkPath) && !is_link($installLinkPath)) {
            $existingReal = realpath($installLinkPath);

            if ($existingReal === $sourceReal) {
                $this->line("Install path already points to source directory: {$installLinkPath}");
            } else {
                $this->error("Refusing to replace real directory with link: {$installLinkPath}");

                return self::FAILURE;
            }
        } else {
            if (!$this->replaceWithSymlink($installLinkPath, $sourceReal)) {
                return self::FAILURE;
            }
        }

        // only if frontend exists, since not all extensions may have frontend
        if (is_dir($sourceFrontendPath)) {
            File::ensureDirectoryExists($publicRootPath);

            if (is_dir($publicFrontendLinkPath) && !is_link($publicFrontendLinkPath)) {
                $existingReal = realpath($publicFrontendLinkPath);

                if ($existingReal === $sourceFrontendPath) {
                    $this->line("Public frontend path already points to source frontend directory: {$publicFrontendLinkPath}");
                } else {
                    $backupRoot = base_path('extensions/.dev/backups/' . $identifier);
                    File::ensureDirectoryExists($backupRoot);

                    $backupPath = $backupRoot . '/public-frontend-' . now()->format('Ymd_His');
                    if (!@rename($publicFrontendLinkPath, $backupPath)) {
                        $this->error("Could not back up existing public frontend directory: {$publicFrontendLinkPath}");

                        return self::FAILURE;
                    }

                    $this->line("Backed up existing public frontend directory to: {$backupPath}");

                    if (!$this->replaceWithSymlink($publicFrontendLinkPath, $sourceFrontendPath)) {
                        return self::FAILURE;
                    }
                }
            } else {
                if (!$this->replaceWithSymlink($publicFrontendLinkPath, $sourceFrontendPath)) {
                    return self::FAILURE;
                }
            }
        }

        if ((bool) $this->option('enable')) {
            $this->syncManifestSnapshot($identifier, $sourceReal, $manifest, true);
            $this->line("Enabled installed extension {$identifier}.");
        } else {
            $this->syncManifestSnapshot($identifier, $sourceReal, $manifest, null);
        }

        $this->info("Linked {$identifier} for development.");
        $this->line("Install path link: {$installLinkPath} -> {$sourceReal}");
        $this->line("Public frontend link: {$publicFrontendLinkPath} -> {$sourceFrontendPath}");
        $this->newLine();
        $this->line("<bg=blue> TIP </> <fg=yellow>run 'php artisan d:extensions:watch {$sourceReal}' to keep frontend files updated live.</>");

        return self::SUCCESS;
    }

    private function resolveSourcePath(string $source): string
    {
        $source = trim($source);
        if ($source === '') {
            return '';
        }

        if (str_contains($source, '/') || str_starts_with($source, '.')) {
            return str_starts_with($source, '/') ? $source : base_path($source);
        }

        return base_path('extensions/' . $source);
    }

    /**
     * @param array<string, mixed> $manifest
     */
    private function syncManifestSnapshot(string $identifier, string $sourceReal, array $manifest, ?bool $forceEnabled): void
    {
        /** @var Extension $record */
        $record = Extension::query()->firstOrNew(['identifier' => $identifier]);

        $record->name = (string) Arr::get($manifest, 'name', $identifier);
        $record->version = (string) Arr::get($manifest, 'version', '0.0.0');
        $record->description = Arr::get($manifest, 'description');
        $record->author = Arr::get($manifest, 'author');
        $record->website = Arr::get($manifest, 'website');
        $record->update_url = Arr::get($manifest, 'update_url');
        $record->api_version = Arr::get($manifest, 'api_version');
        $record->target_version = Arr::get($manifest, 'target_version');
        $record->manifest = $manifest;
        $record->installed_at ??= now();
        $record->extension_updated_at = now();

        if ($forceEnabled === true) {
            $record->enabled = true;
        }

        $record->save();
    }

    private function replaceWithSymlink(string $linkPath, string $targetPath): bool
    {
        if (is_link($linkPath)) {
            @unlink($linkPath);
        } elseif (is_dir($linkPath)) {
            $this->error("Refusing to replace real directory with link: {$linkPath}");

            return false;
        } elseif (is_file($linkPath)) {
            $this->error("Refusing to replace real file with link: {$linkPath}");

            return false;
        }

        File::ensureDirectoryExists(dirname($linkPath));
        symlink($targetPath, $linkPath);

        return true;
    }

    private function removeLinkIfExists(string $path): void
    {
        if (is_link($path)) {
            @unlink($path);
        }
    }
}
