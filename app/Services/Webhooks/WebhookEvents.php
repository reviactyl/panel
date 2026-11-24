<?php

namespace App\Services\Webhooks;

use App\Models\Alert;
use App\Models\Backup;
use App\Models\Database;
use App\Models\DatabaseHost;
use App\Models\Egg;
use App\Models\Location;
use App\Models\Mount;
use App\Models\Nest;
use App\Models\Node;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Server;
use App\Models\Subuser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebhookEvents
{
    public const MODEL_PREFIX = 'model:';

    public const MODEL_ACTIONS = ['created', 'updated', 'deleted'];

    public const GROUP_RESOURCES = 'resources';

    /**
     * The records a webhook can follow the lifecycle of.
     *
     * @var array<string, class-string<Model>>
     */
    public const MODELS = [
        'server' => Server::class,
        'user' => User::class,
        'subuser' => Subuser::class,
        'node' => Node::class,
        'location' => Location::class,
        'nest' => Nest::class,
        'egg' => Egg::class,
        'mount' => Mount::class,
        'database_host' => DatabaseHost::class,
        'database' => Database::class,
        'backup' => Backup::class,
        'schedule' => Schedule::class,
        'role' => Role::class,
        'alert' => Alert::class,
    ];

    /**
     * The activity log entries a webhook can follow, grouped the way they are offered
     * when setting one up.
     *
     * @var array<string, string[]>
     */
    public const ACTIVITY = [
        'auth' => [
            'auth:success',
            'auth:fail',
            'auth:passkey',
            'auth:checkpoint',
            'auth:token',
            'auth:recovery-token',
            'auth:reset-password',
            'auth:password-reset',
            'auth:ip-blocked',
            'auth:sftp.fail',
        ],
        'account' => [
            'user:user.create',
            'user:roles-update',
            'user:account.email-changed',
            'user:account.password-changed',
            'user:account.language-changed',
            'user:account.file-editor-changed',
            'user:account.avatar-changed',
            'user:account.archive-format-changed',
            'user:api-key.create',
            'user:api-key.delete',
            'user:ssh-key.create',
            'user:ssh-key.delete',
            'user:two-factor.create',
            'user:two-factor.delete',
            'user:passkey.create',
            'user:passkey.delete',
        ],
        'server_power' => [
            'server:power.start',
            'server:power.stop',
            'server:power.restart',
            'server:power.kill',
            'server:console.command',
            'server:reinstall',
        ],
        'server_files' => [
            'server:file.read',
            'server:file.write',
            'server:file.copy',
            'server:file.rename',
            'server:file.delete',
            'server:file.restore',
            'server:file.download',
            'server:file.uploaded',
            'server:file.pull',
            'server:file.compress',
            'server:file.decompress',
            'server:file.create-directory',
            'server:sftp.create',
            'server:sftp.write',
            'server:sftp.rename',
            'server:sftp.delete',
            'server:sftp.create-directory',
            'server:sftp.denied',
        ],
        'server_backups' => [
            'server:backup.start',
            'server:backup.complete',
            'server:backup.fail',
            'server:backup.rename',
            'server:backup.download',
            'server:backup.preview',
            'server:backup.delete',
            'server:backup.lock',
            'server:backup.unlock',
            'server:backup.restore',
            'server:backup.restore-complete',
            'server:backup.restore-failed',
        ],
        'server_databases' => [
            'server:database.create',
            'server:database.delete',
            'server:database.rotate-password',
            'server:database.import',
            'server:database.export',
        ],
        'server_schedules' => [
            'server:schedule.create',
            'server:schedule.update',
            'server:schedule.execute',
            'server:schedule.delete',
            'server:task.create',
            'server:task.update',
            'server:task.delete',
        ],
        'server_settings' => [
            'server:settings.rename',
            'server:settings.description',
            'server:settings.category',
            'server:settings.timezone',
            'server:startup.edit',
            'server:startup.image',
            'server:allocation.create',
            'server:allocation.notes',
            'server:allocation.primary',
            'server:allocation.delete',
            'server:subuser.create',
            'server:subuser.update',
            'server:subuser.delete',
        ],
        'admin' => [
            'server:create',
            'server:build',
            'server:delete',
            'server:transfer',
            'server:toggle-install',
            'server:database-create',
            'server:database-delete',
            'server:database-host.create',
            'server:database-host.update',
            'server:database-host.delete',
            'node:create',
            'node:update',
            'node:delete',
            'node:allocation.create',
            'node:allocation.delete',
            'mount:create',
            'mount:update',
            'mount:delete',
            'role:create',
            'role:update',
            'role:delete',
            'alert:create',
            'alert:update',
            'alert:delete',
            'webhook:create',
            'webhook:update',
            'webhook:delete',
        ],
    ];

    /**
     * Returns every event that can be chosen, keyed by the group it is listed under.
     *
     * @return array<string, string[]>
     */
    public static function groups(): array
    {
        $resources = [];
        foreach (array_keys(self::MODELS) as $resource) {
            foreach (self::MODEL_ACTIONS as $action) {
                $resources[] = self::modelEvent($resource, $action);
            }
        }

        return [
            self::GROUP_RESOURCES => $resources,
            ...self::ACTIVITY,
        ];
    }

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return array_merge(...array_values(self::groups()));
    }

    /**
     * Strip anything that is not a known event from the given list.
     *
     * @return string[]
     */
    public static function filter(array $events): array
    {
        return array_values(array_intersect(self::all(), $events));
    }

    public static function modelEvent(string $resource, string $action): string
    {
        return self::MODEL_PREFIX.$resource.'.'.$action;
    }

    public static function resourceFor(Model $model): ?string
    {
        $resource = array_search($model::class, self::MODELS, true);

        return $resource === false ? null : $resource;
    }

    public static function isActivity(string $event): bool
    {
        return ! Str::startsWith($event, self::MODEL_PREFIX);
    }

    public static function label(string $event): string
    {
        if (! self::isActivity($event)) {
            [$resource, $action] = array_pad(explode('.', Str::after($event, self::MODEL_PREFIX), 2), 2, '');

            return trans('admin/webhooks.events.model', [
                'resource' => trans("admin/webhooks.resources.$resource"),
                'action' => trans("admin/webhooks.model_actions.$action"),
            ]);
        }

        [$scope, $name] = array_pad(explode(':', $event, 2), 2, '');
        $parts = array_map(fn (string $part): string => Str::headline($part), explode('.', $name === '' ? $scope : $name));

        if ($name !== '' && (count($parts) === 1 || ! in_array($scope, ['server', 'user'], true))) {
            array_unshift($parts, Str::headline($scope));
        }

        return count($parts) > 1
            ? $parts[0].': '.implode(' ', array_slice($parts, 1))
            : $parts[0];
    }
}
