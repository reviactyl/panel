<?php

namespace App\Console\Commands\Extensions;

use App\Services\Extensions\ExtensionManager;
use Illuminate\Console\Command;

class ListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extensions:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all installed extensions and their status.';

    public function __construct(private readonly ExtensionManager $manager)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $extensions = $this->manager->all();

        if ($extensions->isEmpty()) {
            $this->line('No extensions installed.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Version', 'Enabled', 'Installed At', 'Path'],
            $extensions->map(fn ($extension) => [
                $extension->identifier,
                $extension->name,
                $extension->version,
                $extension->enabled ? 'yes' : 'no',
                $extension->installed_at?->toDateTimeString() ?? '-',
                $extension->install_path,
            ])->all()
        );

        return self::SUCCESS;
    }
}
