<?php

namespace App\Console\Commands\Extensions;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use ZipArchive;

class DevCompileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'd:extensions:compile
                            {source? : Extension source directory or ID under /extensions}
                            {output? : Output .rext archive path (defaults to app root)}
                            {--build-frontend : Run "npm run build" in the extension directory first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package an unpacked extension directory into a distributable .rext archive.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = trim((string) ($this->argument('source') ?: ''));

        if ($source === '') {
            $source = trim((string) $this->ask('Extension source directory or ID', 'my-extension'));
        }

        $sourcePath = $this->resolveSourcePath($source);

        if ($sourcePath === '') {
            $this->error('A valid source directory is required.');

            return self::FAILURE;
        }

        $sourcePath = rtrim($sourcePath, '/');

        $output = (string) ($this->argument('output') ?: '');
        if ($output === '') {
            $output = (string) $this->ask('Output archive path', $this->defaultOutputPath($sourcePath));
        }

        $output = rtrim($this->resolveOutputPath($output), '/');
        if ($output === '') {
            $this->error('A valid output archive path is required.');

            return self::FAILURE;
        }

        if (!File::isDirectory($sourcePath)) {
            $this->error('Source directory does not exist.');

            return self::FAILURE;
        }

        if (!File::isFile($sourcePath . '/extension.json')) {
            $this->error('Source directory must contain extension.json.');

            return self::FAILURE;
        }

        if ((bool) $this->option('build-frontend')) {
            $this->line('Running frontend build hook: npm run build');
            $process = new Process(['npm', 'run', 'build'], $sourcePath, null, null, 300);
            $process->run(function (string $type, string $buffer): void {
                if ($type === Process::ERR) {
                    $this->output->write("<error>{$buffer}</error>");
                } else {
                    $this->output->write($buffer);
                }
            });

            if (!$process->isSuccessful()) {
                $this->error('Frontend build failed.');

                return self::FAILURE;
            }
        }

        File::ensureDirectoryExists(dirname($output));
        if (File::exists($output)) {
            File::delete($output);
        }

        $zip = new ZipArchive();
        $open = $zip->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($open !== true) {
            $this->error('Could not create output archive.');

            return self::FAILURE;
        }

        $finder = Finder::create()->files()->in($sourcePath);
        foreach ($finder as $file) {
            $absolute = $file->getRealPath();
            if (!is_string($absolute)) {
                continue;
            }

            $relative = ltrim(str_replace($sourcePath, '', $absolute), DIRECTORY_SEPARATOR);
            $zip->addFile($absolute, $relative);
        }

        $zip->close();

        $this->info("Created extension package: {$output}");

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

    private function resolveOutputPath(string $output): string
    {
        $output = trim($output);
        if ($output === '') {
            return '';
        }

        if (str_contains($output, '/') || str_starts_with($output, '.')) {
            return str_starts_with($output, '/') ? $output : base_path($output);
        }

        return base_path($output);
    }

    private function defaultOutputPath(string $sourcePath): string
    {
        $id = basename($sourcePath);

        return base_path($id . '.rext');
    }
}
