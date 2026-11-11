<?php

namespace App\Notifications;

use App\Models\Backup;
use App\Models\User;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackupCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Backup $backup)
    {
        $this->afterCommit();
    }

    public function via(User $notifiable): array
    {
        $event = $this->backup->is_successful ? 'backup-completed' : 'backup-failed';
        $preferences = app(NotificationPreferenceService::class);
        $channels = $preferences->isEnabled($notifiable, $event) ? ['database'] : [];
        if ($preferences->isEnabled($notifiable, $event, 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
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
