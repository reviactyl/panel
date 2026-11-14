<?php

namespace App\Console\Commands\Maintenance;

use App\Services\Designify\SiteImageService;
use Illuminate\Console\Command;

class PruneDesignifyImagesCommand extends Command
{
    protected $signature = 'p:maintenance:prune-designify-images';

    protected $description = 'Delete unreferenced Designify uploads older than 24 hours.';

    public function handle(SiteImageService $images): int
    {
        $this->info('Deleted '.$images->pruneUnusedUploads().' unused Designify images.');

        return self::SUCCESS;
    }
}
