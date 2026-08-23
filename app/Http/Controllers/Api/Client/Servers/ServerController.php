<?php

namespace App\Http\Controllers\Api\Client\Servers;

use App\Http\Controllers\Api\Client\ClientApiController;
use App\Http\Requests\Api\Client\Servers\GetServerRequest;
use App\Models\Server;
use App\Services\Servers\GetUserPermissionsService;
use App\Services\Subusers\SubuserPreviewContext;
use App\Transformers\Api\Client\ServerTransformer;

class ServerController extends ClientApiController
{
    /**
     * ServerController constructor.
     */
    public function __construct(private GetUserPermissionsService $permissionsService)
    {
        parent::__construct();
    }

    /**
     * Transforms an individual server into an API response with ownership and permission metadata.
     *
     * @return array The transformed server data and metadata.
     */
    public function index(GetServerRequest $request, Server $server): array
    {
        $preview = $request->attributes->get(SubuserPreviewContext::class);

        return $this->fractal->item($server)
            ->transformWith($this->getTransformer(ServerTransformer::class))
            ->addMeta([
                'is_server_owner' => ! ($preview instanceof SubuserPreviewContext) && $request->user()->id === $server->owner_id,
                'user_permissions' => $this->permissionsService->handle($server, $request->user()),
            ])
            ->toArray();
    }
}
