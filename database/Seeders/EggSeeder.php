<?php

namespace Database\Seeders;

use App\Models\Egg;
use App\Models\Nest;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use App\Services\Eggs\Sharing\EggImporterService;
use App\Services\Eggs\Sharing\EggUpdateImporterService;

class EggSeeder extends Seeder
{
    protected EggImporterService $importerService;

    protected EggUpdateImporterService $updateImporterService;

    /**
     * @var string[]
     */
    public static array $import = [
        'Minecraft',
        'Source Engine',
        'Voice Servers',
        'Rust',
    ];

    /**
     * EggSeeder constructor.
     */
    public function __construct(
        EggImporterService $importerService,
        EggUpdateImporterService $updateImporterService,
    ) {
        $this->importerService = $importerService;
        $this->updateImporterService = $updateImporterService;
    }

    /**
     * Run the egg seeder.
     */
    public function run()
    {
        foreach (static::$import as $nest) {
            /* @noinspection PhpParamsInspection */
            // check for old eggs with old author for migration
            $nestModel = Nest::query()->where('author', 'authors@reviactyl.app')->where('name', $nest)->first()
                ?? Nest::query()->where('author', 'support@pterodactyl.io')->where('name', $nest)->firstOrFail();

            $this->parseEggFiles($nestModel);
        }
    }

    /**
     * Loop through the list of egg files and import them.
     */
    protected function parseEggFiles(Nest $nest)
    {
        $files = new \DirectoryIterator(database_path('Seeders/eggs/' . kebab_case($nest->name)));

        $this->command->alert('Updating Eggs for Nest: ' . $nest->name);
        /** @var \DirectoryIterator $file */
        foreach ($files as $file) {
            if (!$file->isFile() || !$file->isReadable()) {
                continue;
            }

            $decoded = json_decode(file_get_contents($file->getRealPath()), true, 512, JSON_THROW_ON_ERROR);
            $file = new UploadedFile($file->getPathname(), $file->getFilename(), 'application/json');

            // check for existing egg with new author or fallback to old Pterodactyl author for migration
            $egg = $nest->eggs()
                ->where('author', $decoded['author'])
                ->where('name', $decoded['name'])
                ->first();

            // fallback
            if (!$egg) {
                $egg = $nest->eggs()
                    ->where('author', 'support@pterodactyl.io')
                    ->where('name', $decoded['name'])
                    ->first();
            }

            if ($egg instanceof Egg) {
                $this->updateImporterService->handle($egg, $file);

                if ($egg->author !== $decoded['author'] || $egg->banner !== ($decoded['banner'] ?? null)) {
                    $egg->update([
                        'author' => $decoded['author'],
                        'banner' => $decoded['banner'] ?? null,
                    ]);
                }

                $this->command->info('Updated ' . $decoded['name']);
            } else {
                $this->importerService->handle($file, $nest->id);
                $this->command->comment('Created ' . $decoded['name']);
            }
        }

        $this->command->line('');
    }
}
