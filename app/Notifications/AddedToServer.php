<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\Mail\EmailTemplateService;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddedToServer extends Notification implements ShouldQueue
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
        $channels = $preferences->isEnabled($notifiable, 'added') ? ['database'] : [];
        if ($preferences->isEnabled($notifiable, 'added', 'email')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return app(EmailTemplateService::class)->apply('AddedToServer', $this->defaultMail(), ['recipient_name' => $this->server->user, 'server_name' => $this->server->name]);
    }

    public function toDatabase(): array
    {
        return [
            'source' => 'reviactyl',
            'event' => 'added',
            'server' => $this->server->name,
            'url' => '/server/'.$this->server->uuidShort,
        ];
    }

    private function defaultMail(): MailMessage
    {
        return (new MailMessage())
            ->greeting('Hello '.$this->server->user.'!')
            ->line('You have been added as a subuser for the following server, allowing you certain control over the server.')
            ->line('Server Name: '.$this->server->name)
            ->action('Visit Server', url('/server/'.$this->server->uuidShort));
    }
}
