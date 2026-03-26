<?php

namespace App\Console\Commands\Extensions;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RebuildCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extensions:rebuild-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear extension cache artifacts and manifest cache keys.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        Cache::forget('extensions.frontend.registry');
        $this->info('Cleared extension registry cache key.');

        return self::SUCCESS;
    }
}
