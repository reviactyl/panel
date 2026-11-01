<?php

namespace App\Notifications;

use App\Services\Mail\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPasswordReset extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $token) {}

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
    public function toMail(mixed $notifiable): MailMessage
    {
        return app(EmailTemplateService::class)->apply('SendPasswordReset', $this->defaultMail($notifiable), ['recipient_name' => $notifiable->name, 'email' => $notifiable->email]);
    }

    private function defaultMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Reset Password')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', url('/auth/password/reset/'.$this->token.'?email='.urlencode($notifiable->email)))
            ->line('If you did not request a password reset, no further action is required.');
    }
}
