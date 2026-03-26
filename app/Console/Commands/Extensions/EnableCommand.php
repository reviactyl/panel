<?php

namespace App\Console\Commands\Extensions;

use App\Services\Extensions\ExtensionManager;
use Illuminate\Console\Command;

class EnableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extensions:enable {id? : Extension identifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable an installed extension.';

    public function __construct(private readonly ExtensionManager $manager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = trim((string) ($this->argument('id') ?: ''));

        if ($identifier === '') {
            if ((bool) $this->option('no-interaction')) {
                $this->error('The id argument is required in non-interactive mode.');

                return self::FAILURE;
            }

            $identifier = trim((string) $this->ask('Extension identifier to enable'));
        }

        if ($identifier === '') {
            $this->error('A valid extension identifier is required.');

            return self::FAILURE;
        }

        try {
            $extension = $this->manager->enable($identifier);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Enabled extension %s (%s).', $extension->identifier, $extension->version));

        return self::SUCCESS;
    }
}
