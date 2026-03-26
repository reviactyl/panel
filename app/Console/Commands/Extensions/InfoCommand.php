<?php

namespace App\Console\Commands\Extensions;

use App\Models\Extension;
use Illuminate\Console\Command;

class InfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'extensions:info {id? : Extension identifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display extension information.';

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

            $identifier = trim((string) $this->ask('Extension identifier to inspect'));
        }

        if ($identifier === '') {
            $this->error('A valid extension identifier is required.');

            return self::FAILURE;
        }

        /** @var Extension|null $extension */
        $extension = Extension::query()->where('identifier', $identifier)->first();

        if ($extension === null) {
            $this->error("Extension {$identifier} is not installed.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->line("Identifier: <fg=cyan>{$extension->identifier}</>");
        $this->line("Name: <fg=green>{$extension->name}</>");
        $this->line("Update URL: <fg=yellow>" . ($extension->update_url ?? '-') . "</>");
        $this->line("Description: <fg=white>{$extension->description}</>");
        $this->line("Author: <fg=blue>{$extension->author}</>");
        $this->line("Website: <fg=yellow>" . ($extension->website ?? '-') . "</>");
        $this->line("Version: <fg=magenta>{$extension->version}</>");
        $this->line('Enabled: ' . ($extension->enabled ? '<fg=green>yes</>' : '<fg=red>no</>'));
        $this->line('Installed At: ' . ($extension->installed_at?->toDateTimeString() ?? '-'));
        $this->line("Install Path: <fg=cyan>{$extension->install_path}</>");
        return self::SUCCESS;
    }
}
