<?php

namespace App\Console\Commands\Extensions;

use App\Models\Extension;
use App\Services\Extensions\ExtensionManager;
use Illuminate\Console\Command;

class RepairCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extensions:repair {id? : Extension identifier (omit to process all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a basic repair check.';

    public function __construct(private readonly ExtensionManager $manager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('id');
        $query = Extension::query();

        if (is_string($identifier) && $identifier !== '') {
            $query->where('identifier', $identifier);
        }

        $extensions = $query->get();
        if ($extensions->isEmpty()) {
            $this->line('No matching extensions to repair.');

            return self::SUCCESS;
        }

        foreach ($extensions as $extension) {
            try {
                if ($extension->enabled) {
                    $this->manager->enable($extension->identifier);
                }

                $this->info("Repaired {$extension->identifier}");
            } catch (\Throwable $exception) {
                $this->error("Failed {$extension->identifier}: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
