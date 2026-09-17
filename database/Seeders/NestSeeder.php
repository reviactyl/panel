<?php

namespace Database\Seeders;

use App\Contracts\Repository\NestRepositoryInterface;
use App\Exceptions\Model\DataValidationException;
use App\Services\Nests\NestCreationService;
use Illuminate\Database\Seeder;

class NestSeeder extends Seeder
{
    /**
     * @var NestCreationService
     */
    private $creationService;

    /**
     * @var NestRepositoryInterface
     */
    private $repository;

    /**
     * NestSeeder constructor.
     */
    public function __construct(
        NestCreationService $creationService,
        NestRepositoryInterface $repository,
    ) {
        $this->creationService = $creationService;
        $this->repository = $repository;
    }

    /**
     * Run the seeder to add missing nests to the Panel.
     *
     * @throws DataValidationException
     */
    public function run()
    {
        $items = $this->repository->findWhere([
            'author' => 'authors@reviactyl.app',
        ])->keyBy('name')->toArray();

        // check for old nests with old author for migration
        $oldItems = $this->repository->findWhere([
            'author' => 'support@pterodactyl.io',
        ])->keyBy('name');

        // migrate old nests -> new nests
        foreach ($oldItems as $oldNest) {
            if (! isset($items[$oldNest->name])) {
                $this->repository->update($oldNest->id, ['author' => 'authors@reviactyl.app']);
                $items[$oldNest->name] = $oldNest->toArray();
            }
        }

        $this->createMinecraftNest(array_get($items, 'Minecraft'));
        $this->createSourceEngineNest(array_get($items, 'Source Engine'));
        $this->createVoiceServersNest(array_get($items, 'Voice Servers'));
        $this->createRustNest(array_get($items, 'Rust'));
    }

    /**
     * Create the Minecraft nest to be used later on.
     *
     * @throws DataValidationException
     */
    private function createMinecraftNest(?array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Minecraft',
                'description' => 'Minecraft - the classic game from Mojang. With support for Vanilla MC, Spigot, and many others!',
                'image' => 'https://cdn.reviactyl.app/@reviactyl/eggs/minecraft/vanilla.webp',
            ], 'authors@reviactyl.app');
        }
    }

    /**
     * Create the Source Engine Games nest to be used later on.
     *
     * @throws DataValidationException
     */
    private function createSourceEngineNest(?array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Source Engine',
                'description' => 'Includes support for most Source Dedicated Server games.',
                'image' => 'https://cdn.reviactyl.app/@reviactyl/eggs/source/steam.webp',
            ], 'authors@reviactyl.app');
        }
    }

    /**
     * Create the Voice Servers nest to be used later on.
     *
     * @throws DataValidationException
     */
    private function createVoiceServersNest(?array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Voice Servers',
                'description' => 'Voice servers such as Mumble and Teamspeak 3.',
                'image' => 'https://cdn.reviactyl.app/@reviactyl/eggs/voice/teamspeak.webp',
            ], 'authors@reviactyl.app');
        }
    }

    /**
     * Create the Rust nest to be used later on.
     *
     * @throws DataValidationException
     */
    private function createRustNest(?array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Rust',
                'description' => 'Rust - A game where you must fight to survive.',
                'image' => 'https://cdn.reviactyl.app/@reviactyl/eggs/rust/rust.webp',
            ], 'authors@reviactyl.app');
        }
    }
}
