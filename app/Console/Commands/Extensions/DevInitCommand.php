<?php

namespace App\Console\Commands\Extensions;

use App\Models\Extension;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DevInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'd:extensions:init
                            {path? : Output directory or extension ID under /extensions}
                            {--id= : Extension identifier}
                            {--name= : Extension display name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a minimal development extension with extension.json only.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pathInput = trim((string) ($this->argument('path') ?: ''));
        $nonInteractive = (bool) $this->option('no-interaction');
        $nameOption = (string) ($this->option('name') ?: '');
        $idOption = (string) ($this->option('id') ?: '');

        if ($pathInput === '') {
            $fallbackName = $nameOption !== '' ? $nameOption : 'my extension';
            $fallbackIdentifier = $idOption !== '' ? $idOption : Str::slug($fallbackName);

            if ($nonInteractive) {
                $pathInput = $fallbackIdentifier;
            } else {
                $pathInput = trim((string) $this->ask('Output directory or extension ID', $fallbackIdentifier));
            }
        }

        $path = rtrim($this->resolveOutputPath($pathInput), '/');
        if ($path === '') {
            $this->error('A valid output path is required.');

            return self::FAILURE;
        }

        $identifier = (string) ($idOption !== '' ? $idOption : Str::slug((string) ($nameOption !== '' ? $nameOption : basename($path))));
        $name = (string) ($this->option('name') ?: Str::headline($identifier));
        $panelVersion = trim((string) config('app.version', ''));

        $description = '';
        $author = '';
        $websiteInput = 'none';
        $updateUrlInput = 'none';
        $targetVersionInput = $panelVersion !== '' ? $panelVersion : 'none';

        if (!$nonInteractive) {
            $description = trim((string) $this->ask('Description', $description));
            $author = trim((string) $this->ask('Author', $author));
            $websiteInput = trim((string) $this->ask('Website (none = null)', $websiteInput));
            $updateUrlInput = trim((string) $this->ask('Update URL (none = null)', $updateUrlInput));
            $targetVersionInput = trim((string) $this->ask('Target version (none = null)', $targetVersionInput));
        }

        $website = $this->nullablePromptValue($websiteInput);
        $updateUrl = $this->nullablePromptValue($updateUrlInput);
        $targetVersion = $this->nullablePromptValue($targetVersionInput);

        if (File::exists($path)) {
            $this->error('Target path already exists.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists($path);

        $manifest = [
            'id' => $identifier,
            'name' => $name,
            'version' => '0.1.0',
            'description' => $description !== '' ? $description : null,
            'author' => $author !== '' ? $author : null,
            'website' => $website,
            'update_url' => $updateUrl,
            'api_version' => 'RCYL_v26',
            'target_version' => $targetVersion,
        ];

        File::put(
            $path . '/extension.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
        );

        // Register the rextension in database
        Extension::updateOrCreate(
            ['identifier' => $identifier],
            [
                'name' => $name,
                'version' => '0.1.0',
                'description' => $manifest['description'],
                'author' => $manifest['author'],
                'website' => $manifest['website'],
                'update_url' => $manifest['update_url'],
                'api_version' => 'RCYL_v26',
                'target_version' => $manifest['target_version'],
                'manifest' => $manifest,
                'installed_at' => now(),
                'extension_updated_at' => now(),
            ]
        );

        $this->info("Created extension at {$path}");

        return self::SUCCESS;
    }

    private function resolveOutputPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (str_contains($path, '/') || str_starts_with($path, '.')) {
            return str_starts_with($path, '/') ? $path : base_path($path);
        }

        return base_path('extensions/' . $path);
    }

    private function nullablePromptValue(string $value): ?string
    {
        $normalized = trim($value);

        if ($normalized === '' || strtolower($normalized) === 'none') {
            return null;
        }

        return $normalized;
    }
}
