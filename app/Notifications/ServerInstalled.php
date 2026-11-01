<?php

namespace App\Notifications;

use App\Contracts\Core\ReceivesEvents;
use App\Events\Event;
use App\Events\Server\Installed;
use App\Models\Server;
use App\Models\User;
use App\Services\Mail\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerInstalled extends Notification implements ReceivesEvents, ShouldQueue
{
    use Queueable;

    public Server $server;

    public User $user;

    /**
     * Handle a direct call to this notification from the server installed event. This is configured
     * in the event service provider.
     *
     * @phpstan-param Installed $event
     */
    public function handle(Event|Installed $event): void
    {
        $event->server->loadMissing('user');

        $this->server = $event->server;
        $this->user = $event->server->user;

        // Since we are calling this notification directly from an event listener we need to fire off the dispatcher
        // to send the email now. Don't use send() or you'll end up firing off two different events.
        Container::getInstance()->make(Dispatcher::class)->sendNow($this->user, $this);
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
        return app(EmailTemplateService::class)->apply('ServerInstalled', $this->defaultMail(), ['recipient_name' => $this->user->username, 'server_name' => $this->server->name]);
    }

    private function defaultMail(): MailMessage
    {
        return (new MailMessage())
            ->greeting('Hello '.$this->user->username.'.')
            ->line('Your server has finished installing and is now ready for you to use.')
            ->line('Server Name: '.$this->server->name)
            ->action('Login and Begin Using', route('index'));
    }
}
