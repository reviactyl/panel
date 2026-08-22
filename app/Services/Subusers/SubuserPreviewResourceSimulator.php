<?php

namespace App\Services\Subusers;

use App\Contracts\Extensions\HashidsInterface;
use App\Models\Allocation;
use App\Models\DatabaseHost;
use App\Models\Permission;
use App\Models\Schedule;
use App\Models\Server;
use App\Models\Task;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubuserPreviewResourceSimulator
{
    public function __construct(
        private readonly HashidsInterface $hashids,
        private readonly Encrypter $encrypter,
    ) {}

    public function readGlobal(Request $request, \Closure $next, SubuserPreviewContext $context): mixed
    {
        $response = $next($request);
        if (! $response instanceof JsonResponse || ! $request->is('api/client')) {
            return $response;
        }

        $payload = $response->getData(true);
        if (isset($payload['data'])) {
            foreach ($payload['data'] as &$item) {
                if (Arr::get($item, 'attributes.uuid') === $context->session()->server->uuid) {
                    $this->applyServerState($item, $context);
                }
            }
            unset($item);
        }
        $response->setData($payload);

        return $response;
    }

    public function read(Request $request, \Closure $next, SubuserPreviewContext $context, string $path): mixed
    {
        $path = rtrim($path, '/');

        if (str_ends_with($path, '/schedules/{schedule}')) {
            $this->authorize($context, Permission::ACTION_SCHEDULE_READ);
            $overlay = $this->overlay($context, 'schedules', (string) $request->route('schedule'));
            if ($overlay['exists']) {
                return $this->overlayResponse($overlay['value']);
            }
        }

        if (str_ends_with($path, '/backups/{backup}')) {
            $this->authorize($context, Permission::ACTION_BACKUP_READ);
            $overlay = $this->overlay($context, 'backups', (string) $request->route('backup'));
            if ($overlay['exists']) {
                return $this->overlayResponse($overlay['value']);
            }
        }

        if (str_ends_with($path, '/users/{user}')) {
            $this->authorize($context, Permission::ACTION_USER_READ);
            $overlay = $this->overlay($context, 'subusers', (string) $request->route('user'));
            if ($overlay['exists']) {
                return $this->overlayResponse($overlay['value']);
            }
        }

        $response = $next($request);
        if (! $response instanceof JsonResponse) {
            return $response;
        }

        if (str_ends_with($path, '/databases')) {
            return $this->mergeCollection($response, $context, 'databases', 'id');
        }

        if (str_ends_with($path, '/schedules')) {
            return $this->mergeCollection($response, $context, 'schedules', 'id');
        }

        if (str_ends_with($path, '/schedules/{schedule}')) {
            return $this->replaceItem($response, $context, 'schedules', (string) $request->route('schedule'));
        }

        if (str_ends_with($path, '/network/allocations')) {
            return $this->mergeCollection($response, $context, 'allocations', 'id');
        }

        if (str_ends_with($path, '/backups')) {
            return $this->mergeCollection($response, $context, 'backups', 'uuid');
        }

        if (str_ends_with($path, '/backups/{backup}')) {
            return $this->replaceItem($response, $context, 'backups', (string) $request->route('backup'));
        }

        if (str_ends_with($path, '/startup') || str_ends_with((string) $request->route()?->getActionName(), 'StartupController@index')) {
            return $this->applyStartupState($response, $context);
        }

        if (str_ends_with($path, '/users')) {
            return $this->mergeCollection($response, $context, 'subusers', 'uuid');
        }

        if (str_ends_with($path, '/users/{user}')) {
            return $this->replaceItem($response, $context, 'subusers', (string) $request->route('user'));
        }

        if ($path === '/api/client/servers/{server}') {
            $payload = $response->getData(true);
            $this->applyServerState($payload, $context);
            $response->setData($payload);
        }

        return $response;
    }

    public function write(Request $request, SubuserPreviewContext $context, string $path): ?JsonResponse
    {
        $path = rtrim($path, '/');

        if (str_ends_with($path, '/databases')) {
            return $this->createDatabase($request, $context);
        }

        if (str_ends_with($path, '/databases/{database}/rotate-password')) {
            return $this->rotateDatabasePassword($request, $context);
        }

        if (str_ends_with($path, '/databases/{database}')) {
            return $this->deleteResource($request, $context, 'databases', 'database', Permission::ACTION_DATABASE_DELETE);
        }

        if (str_ends_with($path, '/schedules')) {
            return $this->createSchedule($request, $context);
        }

        if (str_ends_with($path, '/schedules/{schedule}')) {
            if ($request->isMethod('delete')) {
                return $this->deleteResource($request, $context, 'schedules', 'schedule', Permission::ACTION_SCHEDULE_DELETE);
            }

            return $this->updateSchedule($request, $context);
        }

        if (str_ends_with($path, '/schedules/{schedule}/execute')) {
            return $this->executeSchedule($request, $context);
        }

        if (str_ends_with($path, '/schedules/{schedule}/tasks')) {
            return $this->createScheduleTask($request, $context);
        }

        if (str_ends_with($path, '/schedules/{schedule}/tasks/{task}')) {
            return $request->isMethod('delete')
                ? $this->deleteScheduleTask($request, $context)
                : $this->updateScheduleTask($request, $context);
        }

        if (str_ends_with($path, '/network/allocations')) {
            return $this->createAllocation($context);
        }

        if (str_ends_with($path, '/network/allocations/{allocation}/primary')) {
            return $this->setPrimaryAllocation($request, $context);
        }

        if (str_ends_with($path, '/network/allocations/{allocation}')) {
            return $request->isMethod('delete')
                ? $this->deleteAllocation($request, $context)
                : $this->updateAllocation($request, $context);
        }

        if (str_ends_with($path, '/backups')) {
            return $this->createBackup($request, $context);
        }

        if (str_ends_with($path, '/backups/{backup}/lock')) {
            return $this->toggleBackupLock($request, $context);
        }

        if (str_ends_with($path, '/backups/{backup}/restore')) {
            $this->authorize($context, Permission::ACTION_BACKUP_RESTORE);
            validator($request->all(), ['truncate' => 'required|boolean'])->validate();
            $this->backupResource($context, (string) $request->route('backup'));

            return response()->json([], 204);
        }

        if (str_ends_with($path, '/backups/{backup}')) {
            return $this->deleteBackup($request, $context);
        }

        if (str_ends_with($path, '/startup/variable')) {
            return $this->updateStartupVariable($request, $context);
        }

        if (str_ends_with($path, '/settings/rename')) {
            return $this->renameServer($request, $context);
        }

        if (str_ends_with($path, '/settings/reinstall')) {
            $this->authorize($context, Permission::ACTION_SETTINGS_REINSTALL);
            $this->setServerState($context, 'status', Server::STATUS_INSTALLING);

            return response()->json([], 202);
        }

        if (str_ends_with($path, '/settings/docker-image')) {
            return $this->setDockerImage($request, $context);
        }

        if (str_ends_with($path, '/settings/category')) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.categories_unavailable'));
        }

        if (str_ends_with($path, '/users')) {
            return $this->createSubuser($request, $context);
        }

        if (str_ends_with($path, '/users/{user}')) {
            return $request->isMethod('delete')
                ? $this->deleteSubuser($request, $context)
                : $this->updateSubuser($request, $context);
        }

        return null;
    }

    private function createDatabase(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_DATABASE_CREATE);
        validator($request->all(), [
            'database' => 'required|alpha_dash|min:3|max:48',
            'remote' => 'required|string|regex:#^[\\w\\-/.%:]+$#',
        ])->validate();
        $server = $context->session()->server;
        if ($this->atLimit($context, 'databases', $server->databases->pluck('id')->map(fn (int $id) => $this->hashids->encode($id))->all(), $server->database_limit)) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.database_limit'));
        }
        $database = $server->databases()->with('host')->first();
        $host = $database ? $database->host : DatabaseHost::query()->where('node_id', $server->node_id)->first();

        if (! $host) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.database_host_unavailable'));
        }

        $id = 'preview_'.Str::lower(Str::random(12));
        $password = Str::password(24);
        $item = $this->item('server_database', [
            'id' => $id,
            'host' => ['address' => $host->host, 'port' => $host->port],
            'name' => (string) $request->input('database'),
            'username' => 'preview_'.Str::lower(Str::random(8)),
            'connections_from' => (string) $request->input('remote', '%'),
            'max_connections' => null,
            'relationships' => $context->allows(Permission::ACTION_DATABASE_VIEW_PASSWORD)
                ? ['password' => ['object' => 'database_password', 'attributes' => ['password' => $password]]]
                : [],
        ]);

        $this->storeOverlay($context, 'databases', $id, $item);

        return response()->json($item, 201);
    }

    private function rotateDatabasePassword(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_DATABASE_UPDATE);
        $id = (string) $request->route('database');
        $item = $this->databaseResource($context, $id);
        $item['attributes']['relationships'] = $context->allows(Permission::ACTION_DATABASE_VIEW_PASSWORD)
            ? ['password' => ['object' => 'database_password', 'attributes' => ['password' => Str::password(24)]]]
            : [];
        $this->storeOverlay($context, 'databases', $id, $item);

        return response()->json($item);
    }

    private function createSchedule(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_SCHEDULE_CREATE);
        $this->validateSchedule($request);
        $id = $this->previewId();
        $item = $this->item('server_schedule', $this->scheduleAttributes($request, $id));
        $this->storeOverlay($context, 'schedules', (string) $id, $item);

        return response()->json($item, 201);
    }

    private function updateSchedule(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_SCHEDULE_UPDATE);
        $this->validateSchedule($request);
        $id = (string) $request->route('schedule');
        $item = $this->scheduleResource($context, $id);
        $attributes = array_replace($item['attributes'], $this->scheduleAttributes($request, (int) $id));
        $attributes['relationships'] = $item['attributes']['relationships'] ?? ['tasks' => ['object' => 'list', 'data' => []]];
        $item['attributes'] = $attributes;
        $this->storeOverlay($context, 'schedules', $id, $item);

        return response()->json($item);
    }

    private function executeSchedule(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_SCHEDULE_UPDATE);
        $id = (string) $request->route('schedule');
        $item = $this->scheduleResource($context, $id);
        $item['attributes']['last_run_at'] = Carbon::now()->toAtomString();
        $this->storeOverlay($context, 'schedules', $id, $item);

        return response()->json([], 202);
    }

    private function createScheduleTask(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_SCHEDULE_UPDATE);
        $this->validateScheduleTask($request, $context);
        $scheduleId = (string) $request->route('schedule');
        $schedule = $this->scheduleResource($context, $scheduleId);
        $tasks = Arr::get($schedule, 'attributes.relationships.tasks.data', []);
        if (count($tasks) >= config('panel.client_features.schedules.per_schedule_task_limit', 10)) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.task_limit'));
        }
        $task = $this->item('schedule_task', $this->taskAttributes($request, $this->previewId(), count($tasks) + 1));
        $tasks[] = $task;
        $schedule['attributes']['relationships']['tasks'] = ['object' => 'list', 'data' => $tasks];
        $this->storeOverlay($context, 'schedules', $scheduleId, $schedule);

        return response()->json($task, 201);
    }

    private function updateScheduleTask(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_SCHEDULE_UPDATE);
        $this->validateScheduleTask($request, $context);
        [$scheduleId, $taskId] = [(string) $request->route('schedule'), (string) $request->route('task')];
        $schedule = $this->scheduleResource($context, $scheduleId);
        $tasks = Arr::get($schedule, 'attributes.relationships.tasks.data', []);
        $found = false;
        foreach ($tasks as &$task) {
            if ((string) Arr::get($task, 'attributes.id') === $taskId) {
                $task['attributes'] = array_replace($task['attributes'], $this->taskAttributes(
                    $request,
                    (int) $taskId,
                    (int) Arr::get($task, 'attributes.sequence_id', 1)
                ));
                $found = true;
            }
        }
        unset($task);
        if (! $found) {
            throw new NotFoundHttpException();
        }
        $schedule['attributes']['relationships']['tasks']['data'] = $tasks;
        $this->storeOverlay($context, 'schedules', $scheduleId, $schedule);

        return response()->json(collect($tasks)->first(
            fn (array $task) => (string) Arr::get($task, 'attributes.id') === $taskId
        ));
    }

    private function deleteScheduleTask(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_SCHEDULE_UPDATE);
        [$scheduleId, $taskId] = [(string) $request->route('schedule'), (string) $request->route('task')];
        $schedule = $this->scheduleResource($context, $scheduleId);
        $existingTasks = collect(Arr::get($schedule, 'attributes.relationships.tasks.data', []));
        if (! $existingTasks->contains(fn (array $task) => (string) Arr::get($task, 'attributes.id') === $taskId)) {
            throw new NotFoundHttpException();
        }
        $tasks = $existingTasks
            ->reject(fn (array $task) => (string) Arr::get($task, 'attributes.id') === $taskId)
            ->values()
            ->map(function (array $task, int $index) {
                $task['attributes']['sequence_id'] = $index + 1;

                return $task;
            })->all();
        $schedule['attributes']['relationships']['tasks']['data'] = $tasks;
        $this->storeOverlay($context, 'schedules', $scheduleId, $schedule);

        return response()->json([], 204);
    }

    private function createAllocation(SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_ALLOCATION_CREATE);
        $server = $context->session()->server;
        if ($this->atLimit($context, 'allocations', $server->allocations->pluck('id')->all(), $server->allocation_limit)) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.allocation_limit'));
        }
        $used = array_keys($this->resources($context, 'allocations'));
        $allocation = Allocation::query()
            ->where('node_id', $server->node_id)
            ->whereNull('server_id')
            ->whereNotIn('id', $used)
            ->first();
        if (! $allocation) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.allocation_unavailable'));
        }

        $item = $this->allocationItem($allocation, false);
        $this->storeOverlay($context, 'allocations', (string) $allocation->id, $item);

        return response()->json($item, 201);
    }

    private function updateAllocation(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_ALLOCATION_UPDATE);
        validator($request->all(), ['notes' => 'present|nullable|string|max:256'])->validate();
        $id = (string) $request->route('allocation');
        $item = $this->allocationResource($context, $id);
        $item['attributes']['notes'] = $request->input('notes');
        $this->storeOverlay($context, 'allocations', $id, $item);

        return response()->json($item);
    }

    private function setPrimaryAllocation(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_ALLOCATION_UPDATE);
        $id = (string) $request->route('allocation');
        $target = $this->allocationResource($context, $id);
        foreach ($context->session()->server->allocations as $allocation) {
            $item = $this->allocationResource($context, (string) $allocation->id);
            $item['attributes']['is_default'] = (string) $allocation->id === $id;
            $this->storeOverlay($context, 'allocations', (string) $allocation->id, $item);
            if ((string) $allocation->id === $id) {
                $target = $item;
            }
        }
        foreach ($this->resources($context, 'allocations') as $allocationId => $item) {
            if ($item === null) {
                continue;
            }
            $item['attributes']['is_default'] = (string) $allocationId === $id;
            $this->storeOverlay($context, 'allocations', (string) $allocationId, $item);
            if ((string) $allocationId === $id) {
                $target = $item;
            }
        }

        return response()->json($target);
    }

    private function deleteAllocation(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_ALLOCATION_DELETE);
        $id = (string) $request->route('allocation');
        if ($this->allocationResource($context, $id)['attributes']['is_default']) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.primary_allocation'));
        }
        $this->storeOverlay($context, 'allocations', $id, null);

        return response()->json([], 204);
    }

    private function createBackup(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_BACKUP_CREATE);
        validator($request->all(), [
            'name' => 'nullable|string|max:191',
            'is_locked' => 'nullable|boolean',
            'ignored' => 'nullable|string',
        ])->validate();
        $server = $context->session()->server;
        if ($this->atLimit($context, 'backups', $server->backups->pluck('uuid')->all(), $server->backup_limit)) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.backup_limit'));
        }
        $uuid = Str::uuid()->toString();
        $now = Carbon::now()->toAtomString();
        $item = $this->item('backup', [
            'uuid' => $uuid,
            'is_successful' => true,
            'is_locked' => $request->boolean('is_locked'),
            'name' => $request->input('name') ?: 'Backup at '.Carbon::now()->toDateTimeString(),
            'ignored_files' => $request->input('ignored') ?: '',
            'checksum' => null,
            'bytes' => 0,
            'created_at' => $now,
            'completed_at' => $now,
        ]);
        $this->storeOverlay($context, 'backups', $uuid, $item);

        return response()->json($item, 201);
    }

    private function toggleBackupLock(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_BACKUP_DELETE);
        $uuid = (string) $request->route('backup');
        $item = $this->backupResource($context, $uuid);
        $item['attributes']['is_locked'] = ! $item['attributes']['is_locked'];
        $this->storeOverlay($context, 'backups', $uuid, $item);

        return response()->json($item);
    }

    private function deleteBackup(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_BACKUP_DELETE);
        $uuid = (string) $request->route('backup');
        if ($this->backupResource($context, $uuid)['attributes']['is_locked']) {
            throw new ConflictHttpException(trans('exceptions.subuser_preview.locked_backup'));
        }
        $this->storeOverlay($context, 'backups', $uuid, null);

        return response()->json([], 204);
    }

    private function updateStartupVariable(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_STARTUP_UPDATE);
        validator($request->all(), ['key' => 'required|string', 'value' => 'present'])->validate();
        $server = $context->session()->server;
        $variable = $server->variables()->where('env_variable', $request->input('key'))->first();
        if (! $variable || ! $variable->user_viewable || ! $variable->user_editable) {
            throw new BadRequestHttpException(trans('exceptions.subuser_preview.variable_unavailable'));
        }

        validator(['value' => $request->input('value')], ['value' => $variable->rules])->validate();
        $value = (string) $request->input('value', '');
        $this->updateState($context, function (array $state) use ($variable, $value) {
            $state['startup']['variables'][$variable->env_variable] = $value;

            return $state;
        });
        $attributes = [
            'name' => $variable->name,
            'description' => $variable->description,
            'env_variable' => $variable->env_variable,
            'default_value' => $variable->default_value,
            'server_value' => $value,
            'is_editable' => $variable->user_editable,
            'rules' => $variable->rules,
        ];

        return response()->json(array_replace($this->item('egg_variable', $attributes), [
            'meta' => [
                'startup_command' => $this->startupCommand($context),
                'raw_startup_command' => $server->startup,
            ],
        ]));
    }

    private function renameServer(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_SETTINGS_RENAME);
        validator($request->all(), [
            'name' => Server::getRules()['name'],
            'description' => 'string|nullable',
        ])->validate();
        $this->updateState($context, function (array $state) use ($request) {
            $state['server']['name'] = (string) $request->input('name');
            if ($request->has('description')) {
                $state['server']['description'] = (string) $request->input('description');
            }

            return $state;
        });

        return response()->json([], 204);
    }

    private function setDockerImage(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_STARTUP_DOCKER_IMAGE);
        validator($request->all(), ['docker_image' => 'required|string|max:191'])->validate();
        $image = (string) $request->input('docker_image');
        if (! in_array($image, array_values($context->session()->server->egg->docker_images), true)) {
            throw new BadRequestHttpException(trans('exceptions.subuser_preview.docker_image_unavailable'));
        }
        $this->setServerState($context, 'docker_image', $image);

        return response()->json([], 204);
    }

    private function createSubuser(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_USER_CREATE);
        $this->validateSubuser($request, true);
        $email = Str::lower((string) $request->input('email'));
        validator(['email' => $email], ['email' => 'required|email'])->validate();
        $uuid = Str::uuid()->toString();
        $item = $this->item('server_subuser', [
            'uuid' => $uuid,
            'identifier' => 'preview_'.Str::lower(Str::random(8)),
            'username' => Str::before($email, '@'),
            'email' => $email,
            'image' => 'https://gravatar.com/avatar/'.md5($email),
            '2fa_enabled' => false,
            'created_at' => Carbon::now()->toAtomString(),
            'permissions' => $this->cleanPermissions($request),
        ]);
        $this->storeOverlay($context, 'subusers', $uuid, $item);

        return response()->json($item, 201);
    }

    private function updateSubuser(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_USER_UPDATE);
        $this->validateSubuser($request, false);
        $uuid = (string) $request->route('user');
        $this->guardPreviewIdentity($context, $uuid);
        $item = $this->subuserResource($context, $uuid);
        $item['attributes']['permissions'] = $this->cleanPermissions($request);
        $this->storeOverlay($context, 'subusers', $uuid, $item);

        return response()->json($item);
    }

    private function deleteSubuser(Request $request, SubuserPreviewContext $context): JsonResponse
    {
        $this->authorize($context, Permission::ACTION_USER_DELETE);
        $uuid = (string) $request->route('user');
        $this->guardPreviewIdentity($context, $uuid);
        $this->storeOverlay($context, 'subusers', $uuid, null);

        return response()->json([], 204);
    }

    private function subuserResource(SubuserPreviewContext $context, string $uuid): array
    {
        $overlay = $this->overlay($context, 'subusers', $uuid);
        if ($overlay['exists']) {
            return $this->requiredResource($overlay['value']);
        }

        $subuser = $context->session()->server->subusers()->with('user')->whereRelation('user', 'uuid', $uuid)->first();
        if (! $subuser) {
            throw new NotFoundHttpException();
        }
        $user = $subuser->user;

        return $this->item('server_subuser', [
            'uuid' => $user->uuid,
            'identifier' => $user->identifier,
            'username' => $user->username,
            'email' => $user->email,
            'image' => 'https://gravatar.com/avatar/'.md5(Str::lower($user->email)),
            '2fa_enabled' => $user->use_totp,
            'created_at' => $user->created_at->toAtomString(),
            'permissions' => $subuser->permissions,
        ]);
    }

    private function guardPreviewIdentity(SubuserPreviewContext $context, string $uuid): void
    {
        if ($context->session()->subuser->user->uuid === $uuid) {
            throw new AccessDeniedHttpException(trans('exceptions.subusers.editing_self'));
        }
    }

    private function cleanPermissions(Request $request): array
    {
        $allowed = Permission::permissions()->flatMap(
            fn (array $group, string $prefix) => array_map(
                fn (string $key) => "$prefix.$key",
                array_keys($group['keys'])
            )
        )->all();

        return array_values(array_unique(array_merge(
            array_intersect((array) $request->input('permissions', []), $allowed),
            [Permission::ACTION_WEBSOCKET_CONNECT]
        )));
    }

    private function applyStartupState(JsonResponse $response, SubuserPreviewContext $context): JsonResponse
    {
        $payload = $response->getData(true);
        $variables = Arr::get($context->session()->state ?? [], 'startup.variables', []);
        if (isset($payload['data'])) {
            foreach ($payload['data'] as &$item) {
                $key = Arr::get($item, 'attributes.env_variable');
                if (is_string($key) && array_key_exists($key, $variables)) {
                    $item['attributes']['server_value'] = $variables[$key];
                }
            }
            unset($item);
        }
        if ($variables !== []) {
            $payload['meta']['startup_command'] = $this->startupCommand($context);
        }
        $serverState = Arr::get($context->session()->state ?? [], 'server', []);
        if (isset($serverState['docker_image'])) {
            $payload['meta']['docker_image'] = $serverState['docker_image'];
        }
        $response->setData($payload);

        return $response;
    }

    private function applyServerState(array &$item, SubuserPreviewContext $context): void
    {
        $state = Arr::get($context->session()->state ?? [], 'server', []);
        foreach (['name', 'description', 'status', 'docker_image'] as $key) {
            if (array_key_exists($key, $state)) {
                $item['attributes'][$key] = $state[$key];
            }
        }

        $variableState = Arr::get($context->session()->state ?? [], 'startup.variables', []);
        if (isset($item['attributes']['relationships']['variables']['data'])) {
            foreach ($item['attributes']['relationships']['variables']['data'] as &$variable) {
                $key = Arr::get($variable, 'attributes.env_variable');
                if (is_string($key) && array_key_exists($key, $variableState)) {
                    $variable['attributes']['server_value'] = $variableState[$key];
                }
            }
            unset($variable);
        }

        $allocations = $this->resources($context, 'allocations');
        if ($allocations !== []) {
            $data = collect(Arr::get($item, 'attributes.relationships.allocations.data', []))
                ->keyBy(fn (array $allocation) => (string) Arr::get($allocation, 'attributes.id'));
            foreach ($allocations as $id => $allocation) {
                $allocation === null ? $data->forget((string) $id) : $data->put((string) $id, $allocation);
            }
            $item['attributes']['relationships']['allocations']['data'] = $data->values()->all();
        }
    }

    private function startupCommand(SubuserPreviewContext $context): string
    {
        $server = $context->session()->server;
        $values = $server->variables->mapWithKeys(fn ($variable) => [$variable->env_variable => $variable->server_value])
            ->merge(Arr::get($context->session()->state ?? [], 'startup.variables', []));

        return preg_replace_callback('/{{([A-Z0-9_]+)}}/', fn (array $match) => (string) $values->get($match[1], $match[0]), $server->startup) ?? $server->startup;
    }

    private function scheduleAttributes(Request $request, int $id): array
    {
        $now = Carbon::now();

        return [
            'id' => $id,
            'name' => (string) $request->input('name'),
            'cron' => [
                'day_of_week' => (string) $request->input('day_of_week', '*'),
                'day_of_month' => (string) $request->input('day_of_month', '*'),
                'month' => (string) $request->input('month', '*'),
                'hour' => (string) $request->input('hour', '*'),
                'minute' => (string) $request->input('minute', '*'),
            ],
            'is_active' => $request->boolean('is_active'),
            'is_processing' => false,
            'only_when_online' => $request->boolean('only_when_online'),
            'last_run_at' => null,
            'next_run_at' => $now->clone()->addMinute()->toAtomString(),
            'created_at' => $now->toAtomString(),
            'updated_at' => $now->toAtomString(),
            'relationships' => ['tasks' => ['object' => 'list', 'data' => []]],
        ];
    }

    private function taskAttributes(Request $request, int $id, int $sequence): array
    {
        $now = Carbon::now()->toAtomString();

        return [
            'id' => $id,
            'sequence_id' => $request->integer('sequence_id', $sequence),
            'action' => (string) $request->input('action'),
            'payload' => (string) $request->input('payload', ''),
            'time_offset' => $request->integer('time_offset'),
            'is_queued' => false,
            'continue_on_failure' => $request->boolean('continue_on_failure'),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function scheduleResource(SubuserPreviewContext $context, string $id): array
    {
        $overlay = $this->overlay($context, 'schedules', $id);
        if ($overlay['exists']) {
            return $this->requiredResource($overlay['value']);
        }

        $schedule = $context->session()->server->schedules()->with('tasks')->find($id);
        if (! $schedule) {
            throw new NotFoundHttpException();
        }

        return $this->scheduleItem($schedule);
    }

    private function scheduleItem(Schedule $schedule): array
    {
        return $this->item('server_schedule', [
            'id' => $schedule->id,
            'name' => $schedule->name,
            'cron' => [
                'day_of_week' => $schedule->cron_day_of_week,
                'day_of_month' => $schedule->cron_day_of_month,
                'month' => $schedule->cron_month,
                'hour' => $schedule->cron_hour,
                'minute' => $schedule->cron_minute,
            ],
            'is_active' => $schedule->is_active,
            'is_processing' => $schedule->is_processing,
            'only_when_online' => $schedule->only_when_online,
            'last_run_at' => $schedule->last_run_at?->toAtomString(),
            'next_run_at' => $schedule->next_run_at?->toAtomString(),
            'created_at' => $schedule->created_at->toAtomString(),
            'updated_at' => $schedule->updated_at->toAtomString(),
            'relationships' => [
                'tasks' => [
                    'object' => 'list',
                    'data' => $schedule->tasks->map(fn (Task $task) => $this->item('schedule_task', [
                        'id' => $task->id,
                        'sequence_id' => $task->sequence_id,
                        'action' => $task->action,
                        'payload' => $task->payload,
                        'time_offset' => $task->time_offset,
                        'is_queued' => $task->is_queued,
                        'continue_on_failure' => $task->continue_on_failure,
                        'created_at' => $task->created_at->toAtomString(),
                        'updated_at' => $task->updated_at->toAtomString(),
                    ]))->all(),
                ],
            ],
        ]);
    }

    private function databaseResource(SubuserPreviewContext $context, string $id): array
    {
        $overlay = $this->overlay($context, 'databases', $id);
        if ($overlay['exists']) {
            return $this->requiredResource($overlay['value']);
        }

        $databaseId = $this->hashids->decodeFirst($id);
        $database = $context->session()->server->databases()->with('host')->find($databaseId);
        if (! $database) {
            throw new NotFoundHttpException();
        }

        return $this->item('server_database', [
            'id' => $id,
            'host' => ['address' => $database->host->host, 'port' => $database->host->port],
            'name' => $database->database,
            'username' => $database->username,
            'connections_from' => $database->remote,
            'max_connections' => $database->max_connections,
            'relationships' => $context->allows(Permission::ACTION_DATABASE_VIEW_PASSWORD)
                ? ['password' => ['object' => 'database_password', 'attributes' => ['password' => $this->encrypter->decrypt($database->password)]]]
                : [],
        ]);
    }

    private function allocationResource(SubuserPreviewContext $context, string $id): array
    {
        $overlay = $this->overlay($context, 'allocations', $id);
        if ($overlay['exists']) {
            return $this->requiredResource($overlay['value']);
        }

        $allocation = $context->session()->server->allocations()->find($id);
        if (! $allocation) {
            throw new NotFoundHttpException();
        }

        return $this->allocationItem($allocation, $context->session()->server->allocation_id === $allocation->id);
    }

    private function allocationItem(Allocation $allocation, bool $primary): array
    {
        return $this->item('allocation', [
            'id' => $allocation->id,
            'ip' => $allocation->ip,
            'ip_alias' => $allocation->ip_alias,
            'port' => $allocation->port,
            'notes' => $allocation->notes,
            'is_default' => $primary,
        ]);
    }

    private function backupResource(SubuserPreviewContext $context, string $uuid): array
    {
        $overlay = $this->overlay($context, 'backups', $uuid);
        if ($overlay['exists']) {
            return $this->requiredResource($overlay['value']);
        }

        $backup = $context->session()->server->backups()->where('uuid', $uuid)->first();
        if (! $backup) {
            throw new NotFoundHttpException();
        }

        return $this->item('backup', [
            'uuid' => $backup->uuid,
            'is_successful' => $backup->is_successful,
            'is_locked' => $backup->is_locked,
            'name' => $backup->name,
            'ignored_files' => $backup->ignored_files,
            'checksum' => $backup->checksum,
            'bytes' => $backup->bytes,
            'created_at' => $backup->created_at->toAtomString(),
            'completed_at' => $backup->completed_at?->toAtomString(),
        ]);
    }

    private function deleteResource(
        Request $request,
        SubuserPreviewContext $context,
        string $resource,
        string $parameter,
        string $permission,
    ): JsonResponse {
        $this->authorize($context, $permission);
        $id = (string) $request->route($parameter);
        match ($resource) {
            'databases' => $this->databaseResource($context, $id),
            'schedules' => $this->scheduleResource($context, $id),
            default => null,
        };
        $this->storeOverlay($context, $resource, $id, null);

        return response()->json([], 204);
    }

    private function mergeCollection(
        JsonResponse $response,
        SubuserPreviewContext $context,
        string $resource,
        string $key,
    ): JsonResponse {
        $payload = $response->getData(true);
        $items = collect($payload['data'] ?? [])->keyBy(fn (array $item) => (string) Arr::get($item, "attributes.$key"));
        foreach ($this->resources($context, $resource) as $id => $item) {
            $item === null ? $items->forget((string) $id) : $items->put((string) $id, $item);
        }
        $payload['data'] = $items->values()->all();
        $response->setData($payload);

        return $response;
    }

    private function replaceItem(JsonResponse $response, SubuserPreviewContext $context, string $resource, string $id): JsonResponse
    {
        $overlay = $this->overlay($context, $resource, $id);
        if (! $overlay['exists']) {
            return $response;
        }

        return $this->overlayResponse($overlay['value']);
    }

    private function overlayResponse(?array $item): JsonResponse
    {
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        return response()->json($item);
    }

    private function requiredResource(?array $item): array
    {
        if ($item === null) {
            throw new NotFoundHttpException();
        }

        return $item;
    }

    private function item(string $object, array $attributes): array
    {
        return ['object' => $object, 'attributes' => $attributes];
    }

    private function resources(SubuserPreviewContext $context, string $resource): array
    {
        return Arr::get($context->session()->state ?? [], "resources.$resource", []);
    }

    private function overlay(SubuserPreviewContext $context, string $resource, string $id): array
    {
        $resources = $this->resources($context, $resource);

        return ['exists' => array_key_exists($id, $resources), 'value' => $resources[$id] ?? null];
    }

    private function storeOverlay(SubuserPreviewContext $context, string $resource, string $id, ?array $item): void
    {
        $this->updateState($context, function (array $state) use ($resource, $id, $item) {
            $state['resources'][$resource][$id] = $item;

            return $state;
        });
    }

    private function setServerState(SubuserPreviewContext $context, string $key, mixed $value): void
    {
        $this->updateState($context, function (array $state) use ($key, $value) {
            $state['server'][$key] = $value;

            return $state;
        });
    }

    private function updateState(SubuserPreviewContext $context, callable $callback): void
    {
        $session = $context->session()->fresh();
        $session->state = $callback($session->state ?? ['power_status' => null, 'files' => []]);
        $session->save();
        $context->session()->refresh();
    }

    private function authorize(SubuserPreviewContext $context, string $permission): void
    {
        if (! $context->allows($permission)) {
            throw new AccessDeniedHttpException(trans('exceptions.subuser_preview.permission_denied'));
        }
    }

    private function validateSchedule(Request $request): void
    {
        validator($request->all(), [
            'name' => 'required|string|max:191',
            'is_active' => 'filled|boolean',
            'minute' => 'required|string',
            'hour' => 'required|string',
            'day_of_month' => 'required|string',
            'day_of_week' => 'required|string',
            'only_when_online' => 'sometimes|boolean',
        ])->validate();
    }

    private function validateScheduleTask(Request $request, SubuserPreviewContext $context): void
    {
        validator($request->all(), [
            'action' => 'required|in:command,power,backup',
            'payload' => 'required_unless:action,backup|string|nullable',
            'time_offset' => 'required|numeric|min:0|max:900',
            'sequence_id' => 'sometimes|required|numeric|min:1',
            'continue_on_failure' => 'sometimes|required|boolean',
        ])->validate();

        $permission = Task::permissionForAction((string) $request->input('action'), $request->input('payload'));
        if ($permission !== null) {
            $this->authorize($context, $permission);
        }
    }

    private function validateSubuser(Request $request, bool $creating): void
    {
        $rules = [
            'permissions' => 'required|array',
            'permissions.*' => 'string',
        ];
        if ($creating) {
            $rules['email'] = 'required|email:strict|between:1,191';
        }

        validator($request->all(), $rules)->validate();
    }

    private function previewId(): int
    {
        return random_int(900000000, 999999999);
    }

    private function atLimit(
        SubuserPreviewContext $context,
        string $resource,
        array $liveIds,
        ?int $limit,
    ): bool {
        if ($limit === null || $limit < 0) {
            return false;
        }

        $ids = collect($liveIds)->mapWithKeys(fn (mixed $id) => [(string) $id => true]);
        foreach ($this->resources($context, $resource) as $id => $item) {
            $item === null ? $ids->forget((string) $id) : $ids->put((string) $id, true);
        }

        return $ids->count() >= $limit;
    }
}
