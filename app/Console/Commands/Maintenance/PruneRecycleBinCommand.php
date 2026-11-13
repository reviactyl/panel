<?php

namespace App\Console\Commands\Maintenance;

use App\Models\Server;
use App\Services\Servers\RecycleBinService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PruneRecycleBinCommand extends Command
{
    protected $signature = 'p:maintenance:prune-recycle-bin';

    protected $description = 'Permanently delete recycled files past the configured retention period.';

    public function handle(RecycleBinService $bin): int
    {
        $cutoff = now()->subDays($bin::retentionDays());
        $failed = false;
        foreach (DB::table('recycled_files')->where('created_at', '<=', $cutoff)->lazyById(100) as $entry) {
            try {
                Cache::lock('recycle-bin:'.$entry->server_id, 300)->block(5, function () use ($entry, $cutoff, $bin) {
                    if (! DB::table('recycled_files')->where('id', $entry->id)->where('created_at', '<=', $cutoff)->exists()) {
                        return;
                    }
                    $server = Server::query()->findOrFail($entry->server_id);
                    $bin->purge($server, $entry->id);
                });
            } catch (\Throwable $exception) {
                $failed = true;
                report($exception);
                $this->error('Could not purge recycled entry '.$entry->id.'. It will be retried on the next run.');
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
