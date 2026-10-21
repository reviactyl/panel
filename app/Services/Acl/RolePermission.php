<?php

namespace App\Services\Acl;

use App\Models\Permission;

class RolePermission
{
    public const READ = 'read';

    public const CREATE = 'create';

    public const UPDATE = 'update';

    public const DELETE = 'delete';

    public const ASSIGN = 'assign';

    public const SEND_EMAIL = 'send_email';

    public const RESOURCE_SERVERS = 'servers';

    public const RESOURCE_USERS = 'users';

    public const RESOURCE_ROLES = 'roles';

    public const RESOURCE_NODES = 'nodes';

    public const RESOURCE_LOCATIONS = 'locations';

    public const RESOURCE_DATABASE_HOSTS = 'database_hosts';

    public const RESOURCE_NESTS = 'nests';

    public const RESOURCE_EGGS = 'eggs';

    public const RESOURCE_MOUNTS = 'mounts';

    public const RESOURCE_ALERTS = 'alerts';

    public const RESOURCE_WEBHOOKS = 'webhooks';

    public const RESOURCE_EXTENSIONS = 'extensions';

    public const RESOURCE_ACTIVITY_LOG = 'activity_log';

    public const RESOURCE_MONITORING = 'monitoring';

    public const RESOURCE_SETTINGS = 'settings';

    public const RESOURCE_DESIGNIFY = 'designify';

    public const RESOURCE_UPDATES = 'updates';

    /**
     * What being able to view servers allows on any server in the client area.
     */
    private const SERVER_READ = [
        Permission::ACTION_WEBSOCKET_CONNECT,
        Permission::ACTION_FILE_READ,
        Permission::ACTION_FILE_READ_CONTENT,
        Permission::ACTION_BACKUP_READ,
        Permission::ACTION_ALLOCATION_READ,
        Permission::ACTION_STARTUP_READ,
        Permission::ACTION_DATABASE_READ,
        Permission::ACTION_SCHEDULE_READ,
        Permission::ACTION_USER_READ,
        Permission::ACTION_ACTIVITY_READ,
    ];

    private const CRUD = [self::READ, self::CREATE, self::UPDATE, self::DELETE];

    /**
     * Every area of the panel that a role can grant access to, along with
     * the actions available within it. Application API keys are intentionally
     * absent: they carry their own access list and stay with root administrators.
     *
     * @return array<string, string[]>
     */
    public static function groups(): array
    {
        return [
            self::RESOURCE_SERVERS => self::CRUD,
            self::RESOURCE_USERS => [...self::CRUD, self::SEND_EMAIL],
            self::RESOURCE_ROLES => [...self::CRUD, self::ASSIGN],
            self::RESOURCE_NODES => self::CRUD,
            self::RESOURCE_LOCATIONS => self::CRUD,
            self::RESOURCE_DATABASE_HOSTS => self::CRUD,
            self::RESOURCE_NESTS => self::CRUD,
            self::RESOURCE_EGGS => self::CRUD,
            self::RESOURCE_MOUNTS => self::CRUD,
            self::RESOURCE_ALERTS => self::CRUD,
            self::RESOURCE_WEBHOOKS => self::CRUD,
            self::RESOURCE_EXTENSIONS => [self::READ, self::UPDATE, self::DELETE],
            self::RESOURCE_ACTIVITY_LOG => [self::READ],
            self::RESOURCE_MONITORING => [self::READ],
            self::RESOURCE_SETTINGS => [self::UPDATE],
            self::RESOURCE_DESIGNIFY => [self::UPDATE],
            self::RESOURCE_UPDATES => [self::READ, self::UPDATE],
        ];
    }

    /**
     * Return the key that a permission is stored under, such as "servers.update".
     */
    public static function key(string $resource, string $action): string
    {
        return $resource.'.'.$action;
    }

    /**
     * Return every permission key that can be granted to a role.
     *
     * @return string[]
     */
    public static function all(): array
    {
        $keys = [];
        foreach (self::groups() as $resource => $actions) {
            foreach ($actions as $action) {
                $keys[] = self::key($resource, $action);
            }
        }

        return $keys;
    }

    /**
     * Strip anything that is not a known permission key from the given list.
     *
     * @return string[]
     */
    public static function filter(array $permissions): array
    {
        return array_values(array_intersect(self::all(), $permissions));
    }

    /**
     * Returns the client area permissions that a set of role permissions gives on every
     * server: all of them when servers can be edited, a read-only set when they can only
     * be viewed, and none otherwise.
     *
     * @param  string[]  $permissions
     * @return string[]
     */
    public static function serverPermissions(array $permissions): array
    {
        if (in_array(self::key(self::RESOURCE_SERVERS, self::UPDATE), $permissions, true)) {
            return Permission::permissions()
                ->flatMap(fn (array $group, string $prefix) => array_map(
                    fn (string $key): string => $prefix.'.'.$key,
                    array_keys($group['keys']),
                ))
                ->values()
                ->all();
        }

        return in_array(self::key(self::RESOURCE_SERVERS, self::READ), $permissions, true) ? self::SERVER_READ : [];
    }
}
