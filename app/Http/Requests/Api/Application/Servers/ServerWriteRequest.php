<?php

namespace App\Http\Requests\Api\Application\Servers;

use App\Http\Requests\Api\Application\ApplicationApiRequest;
use App\Models\Server;
use App\Services\Acl\Api\AdminAcl;

class ServerWriteRequest extends ApplicationApiRequest
{
    protected ?string $resource = AdminAcl::RESOURCE_SERVERS;

    protected int $permission = AdminAcl::WRITE;

    /**
     * Return server update rules, retaining the current server's unique-rule
     * exceptions during requests while allowing Scramble to evaluate the base
     * rules when it instantiates this request without a route.
     */
    protected function serverUpdateRules(): array
    {
        if (! $this->route()) {
            return Server::getRules();
        }

        return Server::getRulesForUpdate($this->parameter('server', Server::class));
    }
}
