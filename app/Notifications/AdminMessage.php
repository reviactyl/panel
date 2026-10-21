<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class AdminMessage extends Notification
{
    public function __construct(public string $subject, public string $message) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $content = self::sanitize($this->message);
        $text = preg_replace_callback('/<a\b[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/is', fn (array $match): string => $match[2].' ('.$match[1].')', $content);
        $text = preg_replace('/<\/(?:td|th)>/i', "\t", $text);
        $text = preg_replace('/<(?:br\s*\/?|\/(?:p|div|h[1-6]|li|tr|blockquote))>/i', "\n", $text);

        return (new MailMessage())
            ->subject($this->subject)
            ->view(['emails.admin-message', 'emails.admin-message-text'], [
                'subject' => $this->subject,
                'content' => $content,
                'text' => html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            ]);
    }

    public static function sanitize(string $html): string
    {
        return (new HtmlSanitizer((new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowAttribute('width', ['img', 'table', 'td'])
            ->allowAttribute('height', ['img'])
            ->allowAttribute('data-id', ['img'])
            ->withMaxInputLength(200000)))
            ->sanitize($html);
    }

    public function renderHtml(): string
    {
        return view('emails.admin-message', [
            'subject' => $this->subject,
            'content' => self::sanitize($this->message),
        ])->render();
    }
}
