<?php

namespace App\Http\Controllers\Api\Client;

use App\Services\Extensions\ExtensionManager;

class ExtensionsController extends ClientApiController
{
    public function __construct(private readonly ExtensionManager $manager)
    {
        parent::__construct();
    }

    /**
     * Returns enabled extension frontend registry metadata.
     *
     * @return array{object: string, data: array<int, array<string, mixed>>}
     */
    public function index(): array
    {
        return [
            'object' => 'list',
            'data' => $this->manager->frontendRegistry(),
        ];
    }
}
