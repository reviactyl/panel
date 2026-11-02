<?php

namespace App\Services\ServerCategories;

use App\Models\Server;
use App\Models\ServerCategory;
use App\Models\ServerCategoryAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServerCategoryService
{
    /** @return Collection<int, ServerCategory> */
    public function all(int $userId): Collection
    {
        return ServerCategory::query()->with('parent')->where('user_id', $userId)
            ->orderBy('priority')->orderByRaw('LOWER(name)')->orderBy('created_at')->orderBy('id')->get();
    }

    public function save(int $userId, array $attributes, ?string $uuid = null): ServerCategory
    {
        return DB::transaction(function () use ($userId, $attributes, $uuid) {
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $folders = ServerCategory::query()->where('user_id', $userId)->lockForUpdate()->get();
            $folder = $uuid === null ? null : $folders->firstWhere('uuid', $uuid);
            abort_if($uuid !== null && $folder === null, 404);
            $parentId = $folder?->parent_id;
            if (array_key_exists('parent_uuid', $attributes)) {
                $parent = $folders->firstWhere('uuid', $attributes['parent_uuid']);
                if (! empty($attributes['parent_uuid']) && ! $parent) {
                    throw ValidationException::withMessages(['parent_uuid' => trans('validation.exists', ['attribute' => 'parent_uuid'])]);
                }
                $ancestor = $parent;
                $seen = [];
                while ($ancestor) {
                    if ($ancestor->id === $folder?->id || isset($seen[$ancestor->id])) {
                        throw ValidationException::withMessages(['parent_uuid' => trans('validation.in', ['attribute' => 'parent_uuid'])]);
                    }
                    $seen[$ancestor->id] = true;
                    $ancestor = $folders->firstWhere('id', $ancestor->parent_id);
                }
                $parentId = $parent?->id;
            }
            unset($attributes['parent_uuid']);
            $siblings = $folders->where('parent_id', $parentId);
            if ($folder && $folder->parent_id !== $parentId && ! array_key_exists('priority', $attributes)) {
                $priority = ($siblings->min('priority') ?? 0) - 1;
                $attributes['priority'] = $this->priority($priority);
            } elseif (! $folder && ! array_key_exists('priority', $attributes)) {
                $attributes['priority'] = 0;
            }
            $attributes['parent_id'] = $parentId;
            if ($folder) {
                $folder->update($attributes);
            } else {
                $folder = ServerCategory::create([...$attributes, 'uuid' => (string) Str::uuid(), 'user_id' => $userId]);
            }

            return $folder->load('parent');
        });
    }

    public function reorder(int $userId, array $uuids): void
    {
        DB::transaction(function () use ($userId, $uuids) {
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            foreach ($uuids as $index => $uuid) {
                ServerCategory::query()->where('user_id', $userId)->where('uuid', $uuid)->update(['priority' => $index, 'position' => $index]);
            }
        });
    }

    public function delete(int $userId, string $uuid): void
    {
        DB::transaction(function () use ($userId, $uuid) {
            User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            ServerCategory::query()->where('user_id', $userId)->where('uuid', $uuid)->firstOrFail()->delete();
        });
    }

    public function assign(User $user, Server $server, ?string $uuid): void
    {
        abort_unless($user->accessibleServers()->where('servers.id', $server->id)->exists(), 404);
        DB::transaction(function () use ($user, $server, $uuid) {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $folder = empty($uuid) ? null : ServerCategory::query()->where('user_id', $user->id)->where('uuid', $uuid)->first();
            if (! empty($uuid) && ! $folder) {
                throw ValidationException::withMessages(['category_uuid' => trans('validation.exists', ['attribute' => 'category_uuid'])]);
            }
            if ($folder) {
                ServerCategoryAssignment::updateOrCreate(['user_id' => $user->id, 'server_id' => $server->id], ['category_id' => $folder->id]);
            } else {
                ServerCategoryAssignment::query()->where('user_id', $user->id)->where('server_id', $server->id)->delete();
            }
        });
    }

    private function priority(int $priority): int
    {
        if ($priority < -2147483648 || $priority > 2147483647) {
            throw ValidationException::withMessages(['priority' => trans('validation.between.numeric', ['attribute' => 'priority', 'min' => -2147483648, 'max' => 2147483647])]);
        }

        return $priority;
    }
}
