<?php

namespace App\Services\Subusers;

use App\Models\Server;
use App\Models\SubuserPreviewSession;

class SubuserPreviewContext
{
    /** Stores the subuser preview session used by this context. */
    public function __construct(private readonly SubuserPreviewSession $session) {}

    /**
     * Retrieves the current subuser preview session.
     *
     * @return SubuserPreviewSession The preview session.
     */
    public function session(): SubuserPreviewSession
    {
        return $this->session;
    }

    /**
     * Determines whether the server belongs to the preview session.
     *
     * @param  Server  $server  The server to compare with the session.
     * @return bool `true` if the server ID matches the session server ID, `false` otherwise.
     */
    public function isServer(Server $server): bool
    {
        return $server->id === $this->session->server_id;
    }

    /**
     * Determines whether an identifier matches the session server.
     *
     * @param  string  $identifier  The UUID, short UUID, or identifier to compare.
     * @return bool `true` if the identifier matches the session server, `false` otherwise.
     */
    public function isServerIdentifier(string $identifier): bool
    {
        $server = $this->session->server;

        return in_array($identifier, [$server->uuid, $server->uuidShort, $server->identifier], true);
    }

    /**
     * Retrieves the subuser's permissions.
     *
     * @return array The subuser's permissions, or an empty array when unavailable.
     */
    public function permissions(): array
    {
        return $this->session->subuser->permissions ?? [];
    }

    /**
     * Determines whether the subuser has a specified permission.
     *
     * @param  string  $permission  The permission to check.
     * @return bool `true` if the permission is granted, `false` otherwise.
     */
    public function allows(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }
}
