<?php

namespace App\Observers;

use App\Models\Backup;
use App\Models\Permission;
use App\Models\User;
use App\Notifications\BackupCompleted;

class BackupObserver
{
    public function updated(Backup $backup): void
    {
        if ($backup->wasChanged('completed_at') && $backup->completed_at !== null && $backup->getOriginal('completed_at') === null) {
            $server = $backup->server;
            $server->loadMissing('subusers');
            $users = User::query()->with('roles')->where(function ($query) use ($server) {
                $query->where('id', $server->owner_id)
                    ->orWhereIn('id', $server->subusers->pluck('user_id'))
                    ->orWhere('root_admin', true)
                    ->orWhereHas('roles', fn ($roles) => $roles
                        ->whereJsonContains('permissions', 'servers.read')
                        ->orWhereJsonContains('permissions', 'servers.update'));
            });
            foreach ($users->lazyById() as $user) {
                if ($user->can(Permission::ACTION_BACKUP_READ, $server)) {
                    $user->notify(new BackupCompleted($backup));
                }
            }
        }
    }
}
