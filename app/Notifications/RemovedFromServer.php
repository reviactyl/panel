<?php

namespace App\Notifications;

use App\Services\Mail\EmailTemplateService;
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
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return app(EmailTemplateService::class)->apply('RemovedFromServer', $this->defaultMail(), ['recipient_name' => $this->server->user, 'server_name' => $this->server->name]);
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
