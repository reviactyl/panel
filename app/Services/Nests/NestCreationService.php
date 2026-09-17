<?php

namespace App\Services\Nests;

use App\Contracts\Repository\NestRepositoryInterface;
use App\Exceptions\Model\DataValidationException;
use App\Models\Nest;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Ramsey\Uuid\Uuid;

class NestCreationService
{
    /**
     * NestCreationService constructor.
     */
    public function __construct(private ConfigRepository $config, private NestRepositoryInterface $repository) {}

    /**
     * Create a new nest on the system.
     *
     * @throws DataValidationException
     */
    public function handle(array $data, ?string $author = null): Nest
    {
        return $this->repository->create([
            'uuid' => Uuid::uuid4()->toString(),
            'author' => $author ?? $this->config->get('panel.service.author'),
            'name' => array_get($data, 'name'),
            'description' => array_get($data, 'description'),
            'image' => array_get($data, 'image'),
        ], true, true);
    }
}
