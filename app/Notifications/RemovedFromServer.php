<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\Mail\EmailTemplateService;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemovedFromServer extends Notification implements ShouldQueue
{
    use Queueable;

    public object $server;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $server)
    {
        $this->server = (object) $server;
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(?User $notifiable = null): array
    {
        if (! $notifiable) {
            return ['database', 'mail'];
        }

        $preferences = app(NotificationPreferenceService::class);
        $channels = $preferences->isEnabled($notifiable, 'removed') ? ['database'] : [];
        if ($preferences->isEnabled($notifiable, 'removed', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return app(EmailTemplateService::class)->apply('RemovedFromServer', $this->defaultMail(), ['recipient_name' => $this->server->user, 'server_name' => $this->server->name]);
    }

    public function toDatabase(): array
    {
        return [
            'source' => 'reviactyl',
            'event' => 'removed',
            'server' => $this->server->name,
            'url' => '/',
        ];
    }

    private function defaultMail(): MailMessage
    {
        return (new MailMessage())
            ->error()
            ->greeting('Hello '.$this->server->user.'.')
            ->line('You have been removed as a subuser for the following server.')
            ->line('Server Name: '.$this->server->name)
            ->action('Visit Panel', route('index'));
    }
}
