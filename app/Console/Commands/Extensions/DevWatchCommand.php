<?php

namespace App\Console\Commands\Extensions;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use App\Services\Helpers\SoftwareVersionService;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class DevWatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'd:extensions:watch
                            {source? : Extension source directory or ID under /extensions}
                            {--once : Compile once and exit (no watch mode)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile extension frontend source in watch mode.';

    public function __construct(private ConfigRepository $config, private SoftwareVersionService $versionService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('');
        $this->line("<fg=red>REVIACTYL v{$this->config->get('app.version')}</> " . ($this->versionService->isLatestPanel() ? 'uptodate' : $this->formatText('outdated', 'bg=red')));
        $this->line('');
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

        $frontendSrc = $sourceReal . '/frontend/src';
        if (!is_dir($frontendSrc)) {
            $this->error('Source directory must contain frontend/src.');

            return self::FAILURE;
        }

        $scriptPath = base_path('resources/scripts/extensions/compile-extension.mjs');
        if (!is_file($scriptPath)) {
            $this->error('Compiler script is missing.');

            return self::FAILURE;
        }

        $mode = (bool) $this->option('once') ? '--once' : '--watch';
        $this->line("Compiling extension frontend from {$sourceReal}");
        $this->line((bool) $this->option('once') ? 'Mode: once' : 'Mode: watch (Ctrl+C to stop)');

        if (!File::isDirectory($sourceReal . '/frontend/dist')) {
            File::ensureDirectoryExists($sourceReal . '/frontend/dist');
        }

        $process = new Process(['node', $scriptPath, $sourceReal, $mode], base_path(), null, null, 0);
        $process->setTty(Process::isTtySupported());

        $process->run(function (string $type, string $buffer): void {
            if ($type === Process::ERR) {
                $this->output->write("<error>{$buffer}</error>");
            } else {
                $this->output->write($buffer);
            }
        });

        if (!$process->isSuccessful()) {
            $this->error('Extension frontend compile failed.');

            return self::FAILURE;
        }

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
}
