<?php

namespace App\Observers;

use App\Models\Backup;
use App\Models\Permission;
use App\Notifications\BackupCompleted;
use Illuminate\Support\Facades\DB;

class BackupObserver
{
    public function updated(Backup $backup): void
    {
        if ($backup->wasChanged('completed_at') && $backup->completed_at !== null && $backup->getOriginal('completed_at') === null) {
            $server = $backup->server;
            $candidates = collect([$server->user])->concat($server->subusers()->with('user')->get()->map->user)->unique('id');
            $preferences = DB::table('backup_notification_preferences')->where('server_id', $server->id)->get()->keyBy('user_id');
            foreach ($candidates as $user) {
                $settings = $preferences->get($user->id);
                $enabled = $backup->is_scheduled ? ($settings->scheduled ?? false) : ($settings->manual ?? false);
                if ($enabled && $user->can(Permission::ACTION_BACKUP_READ, $server)) {
                    $user->notify(new BackupCompleted($backup, (bool) ($settings->email ?? false)));
                }
            }
        }
    }
}
