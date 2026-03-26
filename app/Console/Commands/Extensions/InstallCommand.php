<?php

namespace App\Console\Commands\Extensions;

use App\Services\Extensions\ExtensionManager;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extensions:install
                            {source? : Extension ID, local .rext archive path/name, or remote URL}
                            {--enable : Enable extension after install}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Reviactyl Extension Package.';

    public function __construct(private readonly ExtensionManager $manager)
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

            $source = trim((string) $this->ask('Extension source (ID, .rext path/name, or URL)', 'my-extension.rext'));
        }

        if ($source === '') {
            $this->error('A valid source is required.');

            return self::FAILURE;
        }

        try {
            $extension = $this->manager->installFromSource($source);

            if ((bool) $this->option('enable')) {
                $extension = $this->manager->enable($extension->identifier);
            }
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Installed extension %s (%s).', $extension->identifier, $extension->version));

        if ($extension->enabled) {
            $this->line('Extension is enabled.');
        } else {
            $this->line('Extension is installed but disabled.');
        }

        return self::SUCCESS;
    }
}
