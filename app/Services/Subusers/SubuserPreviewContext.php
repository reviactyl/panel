<?php

namespace App\Services\Subusers;

use App\Models\Server;
use App\Models\SubuserPreviewSession;

class SubuserPreviewContext
{
    public function __construct(private readonly SubuserPreviewSession $session) {}

    public function session(): SubuserPreviewSession
    {
        return $this->session;
    }

    public function isServer(Server $server): bool
    {
        return $server->id === $this->session->server_id;
    }

    public function isServerIdentifier(string $identifier): bool
    {
        $server = $this->session->server;

        return in_array($identifier, [$server->uuid, $server->uuidShort, $server->identifier], true);
    }

    public function permissions(): array
    {
        return $this->session->subuser->permissions ?? [];
    }

    public function allows(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
