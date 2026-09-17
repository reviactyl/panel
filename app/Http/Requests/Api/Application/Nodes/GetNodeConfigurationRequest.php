<?php

namespace App\Http\Requests\Api\Application\Nodes;

use App\Services\Acl\Api\AdminAcl;

class GetNodeConfigurationRequest extends GetNodesRequest
{
    protected int $permission = AdminAcl::WRITE;
}
