<?php

namespace App\Notifications;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackupCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Backup $backup, public bool $sendEmail = false)
    {
        $this->afterCommit();
    }

    public function via(): array
    {
        return $this->sendEmail ? ['database', 'mail'] : ['database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $data = $this->toDatabase();

        return (new MailMessage())
            ->subject(trans('notifications.events.'.$data['event'], [], $notifiable->language ?? 'en'))
            ->line($data['server'])
            ->line($data['detail'])
            ->action(trans('notifications.view-server', [], $notifiable->language ?? 'en'), url($data['url']));
    }

    public function toDatabase(): array
    {
        return [
            'source' => 'reviactyl',
            'event' => $this->backup->is_successful ? 'backup-completed' : 'backup-failed',
            'server' => $this->backup->server->name,
            'detail' => $this->backup->name,
            'url' => '/server/'.$this->backup->server->uuidShort.'/backups',
        ];
    }
}
