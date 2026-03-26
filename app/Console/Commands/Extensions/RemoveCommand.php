<?php

namespace App\Console\Commands\Extensions;

use App\Services\Extensions\ExtensionManager;
use Illuminate\Console\Command;

class RemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extensions:remove {id? : Extension identifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove an installed extension.';

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

            $identifier = trim((string) $this->ask('Extension identifier to remove'));
        }

        if ($identifier === '') {
            $this->error('A valid extension identifier is required.');

            return self::FAILURE;
        }

        try {
            $this->manager->remove($identifier);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Removed extension {$identifier}.");

        return self::SUCCESS;
    }
}
