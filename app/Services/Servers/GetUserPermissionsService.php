<?php

namespace App\Services\Servers;

use App\Models\Server;
use App\Models\Subuser;
use App\Models\User;
use App\Services\Subusers\SubuserPreviewContext;

class GetUserPermissionsService
{
    /**
     * Determines the permissions a user has for a server.
     *
     * @return array The user's preview, administrative, owner, or subuser permissions, or an empty array when none apply.
     */
    public function handle(Server $server, User $user): array
    {
        $preview = request()->attributes->get(SubuserPreviewContext::class);
        if ($preview instanceof SubuserPreviewContext) {
            return $preview->isServer($server) ? $preview->permissions() : [];
        }

        if ($user->root_admin || $user->id === $server->owner_id) {
            $permissions = ['*'];

            if ($user->root_admin) {
                $permissions[] = 'admin.websocket.errors';
                $permissions[] = 'admin.websocket.install';
                $permissions[] = 'admin.websocket.transfer';
            }

            return $permissions;
        }

        /** @var Subuser|null $subuserPermissions */
        $subuserPermissions = $server->subusers()->where('user_id', $user->id)->first();

        return $subuserPermissions ? $subuserPermissions->permissions : [];
    }
}
