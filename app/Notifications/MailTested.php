<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\Mail\EmailTemplateService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MailTested extends Notification
{
    public function __construct(private User $user) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        return app(EmailTemplateService::class)->apply('MailTested', $this->defaultMail(), ['recipient_name' => $this->user->name]);
    }

    private function defaultMail(): MailMessage
    {
        return (new MailMessage())
            ->subject('Panel Test Message')
            ->greeting('Hello '.$this->user->name.'!')
            ->line('This is a test of the Reviactyl mail system. You\'re good to go!');
    }
}
