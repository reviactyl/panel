<?php

namespace App\Console\Commands\Maintenance;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;

class CleanServiceBackupFilesCommand extends Command
{
    public const BACKUP_THRESHOLD_MINUTES = 5;

    protected $description = 'Clean orphaned .bak files created when modifying services.';

    protected $signature = 'p:maintenance:clean-service-backups';

    protected Filesystem $disk;

    /**
     * CleanServiceBackupFilesCommand constructor.
     */
    public function __construct(FilesystemFactory $filesystem)
    {
        parent::__construct();

        $this->disk = $filesystem->disk();
    }

    /**
     * Handle command execution.
     */
    public function handle()
    {
        $files = $this->disk->files('services/.bak');

        collect($files)->each(function (string $filePath): void {
            $lastModified = Carbon::createFromTimestamp($this->disk->lastModified($filePath));
            if ((int) $lastModified->diffInMinutes(Carbon::now()) > self::BACKUP_THRESHOLD_MINUTES) {
                $this->disk->delete($filePath);
                $this->info(trans('command/messages.maintenance.deleting_service_backup', ['file' => basename($filePath)]));
            }
        });
    }
}
