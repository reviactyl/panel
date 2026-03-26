<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Server;
use Illuminate\Console\Command;
use App\Models\ServerStatsHistory;
use App\Repositories\Wings\DaemonServerRepository;
use App\Exceptions\Http\Connection\DaemonConnectionException;

class CaptureServerStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:capture-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Capture resource usage stats for all active servers.';

    /**
     * CaptureServerStats constructor.
     */
    public function __construct(private DaemonServerRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting server stats capture...');

        // Process servers in chunks to avoid memory issues
        Server::query()
            ->whereNull('status')
            ->where('suspended', 0)
            ->where('status', '!=', 'installing') // Don't check installing servers
            ->chunkById(100, function ($servers) {
                foreach ($servers as $server) {
                    try {
                        $details = $this->repository->setServer($server)->getDetails();

                        $stats = $details['utilization'] ?? [];

                        ServerStatsHistory::create([
                            'server_id' => $server->id,
                            'cpu_usage' => $stats['cpu_absolute'] ?? 0,
                            'memory_bytes' => $stats['memory_bytes'] ?? 0,
                            'disk_bytes' => $stats['disk_bytes'] ?? 0,
                            'network_rx_bytes' => $stats['network']['rx_bytes'] ?? 0,
                            'network_tx_bytes' => $stats['network']['tx_bytes'] ?? 0,
                            'created_at' => Carbon::now(),
                        ]);

                    } catch (DaemonConnectionException $e) {
                        // Node likely offline or unreachable, skip this server
                        // $this->warn("Failed to contact node for server {$server->uuid}: {$e->getMessage()}");
                        continue;
                    } catch (\Exception $e) {
                        $this->error("Error processing server {$server->uuid}: {$e->getMessage()}");
                        continue;
                    }
                }
            });

        // Prune old records (keep 7 days)
        $deleted = ServerStatsHistory::where('created_at', '<', Carbon::now()->subDays(7))->delete();
        $this->info("Pruned {$deleted} old stats records.");

        $this->info('Server stats capture completed.');
    }
}
