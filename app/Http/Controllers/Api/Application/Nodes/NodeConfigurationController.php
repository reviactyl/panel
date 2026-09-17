<?php

namespace App\Http\Controllers\Api\Application\Nodes;

use App\Http\Controllers\Api\Application\ApplicationApiController;
use App\Http\Requests\Api\Application\Nodes\GetNodeConfigurationRequest;
use App\Models\Node;
use Illuminate\Http\JsonResponse;

class NodeConfigurationController extends ApplicationApiController
{
    /**
     * Returns the configuration information for a node. This allows for automated deployments
     * to remote machines so long as an API key is provided to the machine to make the request
     * with, and the node is known.
     */
    public function __invoke(GetNodeConfigurationRequest $_, Node $node): JsonResponse
    {
        return new JsonResponse($node->getConfiguration());
    }
}
