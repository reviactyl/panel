<?php

namespace App\Services\Mail;

use App\Models\Setting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class EmailTemplateService
{
    public const TYPES = [
        'AccountCreated' => 'Account created',
        'AddedToServer' => 'Added to server',
        'RemovedFromServer' => 'Removed from server',
        'ServerInstalled' => 'Server installed',
        'SendPasswordReset' => 'Password reset',
        'MailTested' => 'Test email',
    ];

    private function actionText(string $type): string
    {
        return match ($type) {
            'SendPasswordReset' => 'Reset Password', 'AccountCreated' => 'Setup Your Account', 'AddedToServer' => 'Visit Server', 'RemovedFromServer' => 'Visit Panel', default => 'Login and Begin Using',
        };
    }

    public function defaults(string $type): array
    {
        $this->assertType($type);
        $lines = match ($type) {
            'AccountCreated' => '<p>You are receiving this email because an account has been created for you on {{ panel_name }}.</p><p>Username: {{ username }}</p><p>Email: {{ email }}</p>',
            'AddedToServer' => '<p>You have been added as a subuser for the following server, allowing you certain control over the server.</p><p>Server Name: {{ server_name }}</p>',
            'RemovedFromServer' => '<p>You have been removed as a subuser for the following server.</p><p>Server Name: {{ server_name }}</p>',
            'ServerInstalled' => '<p>Your server has finished installing and is now ready for you to use.</p><p>Server Name: {{ server_name }}</p>',
            'SendPasswordReset' => '<p>You are receiving this email because we received a password reset request for your account.</p><p>If you did not request a password reset, no further action is required.</p>',
            default => '<p>This is a test of the Reviactyl mail system. You’re good to go!</p>',
        };

        return ['subject' => match ($type) {
            'MailTested' => 'Panel Test Message',
            'SendPasswordReset' => 'Reset Password',
            default => Str::headline($type),
        }, 'html' => '<h1>Hello {{ recipient_name }}!</h1>'.$lines.($type === 'MailTested' ? '' : '<p><a href="{{ action_url }}">'.e($this->actionText($type)).'</a></p>').'<p>Regards,<br>{{ panel_name }}</p>'];
    }

    public function load(string $type): array
    {
        $this->assertType($type);
        $value = Setting::query()->where('key', 'settings::email-template:'.$type)->value('value');
        $template = $value ? json_decode($value, true) : null;

        $template = is_array($template) ? $template : $this->defaults($type);
        $template['html'] = preg_replace('/{{\s*action_text\s*}}/', e($this->actionText($type)), $template['html']);

        return $template;
    }

    public function validateTemplate(string $type, array $template): array
    {
        $this->assertType($type);
        validator($template, [
            'subject' => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'html' => ['required', 'string', 'max:50000'],
        ])->validate();
        preg_match_all('/{{\s*(.*?)\s*}}/', $template['subject'].$template['html'], $matches);
        if (array_diff($matches[1], array_keys($this->sampleData($type)))) {
            throw ValidationException::withMessages(['html' => trans('admin/email.unknown')]);
        }
        // Protect the URL token while validating it as an actual link.
        $sentinel = 'https://email-placeholder.invalid/action';
        $html = $this->sanitize(preg_replace('/{{\s*action_url\s*}}/', $sentinel, $template['html']));
        if ($type !== 'MailTested' && ! str_contains($html, 'href="'.$sentinel.'"')) {
            throw ValidationException::withMessages(['html' => trans('admin/email.required')]);
        }

        return ['subject' => $template['subject'], 'html' => str_replace($sentinel, '{{ action_url }}', $html)];
    }

    public function save(string $type, array $template): array
    {
        $template = $this->validateTemplate($type, $template);
        $value = json_encode($template, JSON_THROW_ON_ERROR);
        validator(['html' => $value], ['html' => ['max:65535']])->validate();
        Setting::query()->updateOrCreate(['key' => 'settings::email-template:'.$type], ['value' => $value]);

        return $template;
    }

    public function reset(string $type): void
    {
        $this->assertType($type);
        Setting::query()->where('key', 'settings::email-template:'.$type)->delete();
    }

    public function sampleData(string $type): array
    {
        $data = ['panel_name' => config('app.name'), 'recipient_name' => 'User'];
        if ($type !== 'MailTested') {
            $data += ['action_url' => url('/')];
        }
        if ($type === 'AccountCreated') {
            $data += ['username' => 'User', 'email' => 'Email'];
        } elseif ($type === 'SendPasswordReset') {
            $data += ['email' => 'Email'];
        } elseif (in_array($type, ['AddedToServer', 'RemovedFromServer', 'ServerInstalled'], true)) {
            $data += ['server_name' => 'Server'];
        }

        return $data;
    }

    public function render(array $template, array $data): array
    {
        $replace = fn (string $value, bool $escape): string => preg_replace_callback('/{{\s*(\w+)\s*}}/', fn (array $match): string => $escape ? e((string) ($data[$match[1]] ?? '')) : (string) ($data[$match[1]] ?? ''), $value);
        $subject = str_replace(["\r", "\n"], '', $replace($template['subject'], false));
        if (empty($data['action_url'])) {
            $template['html'] = preg_replace('/<a\b[^>]*href="{{\s*action_url\s*}}"[^>]*>.*?<\/a>/is', '', $template['html']);
        }
        $content = $this->sanitize($replace($template['html'], true));
        $text = preg_replace_callback('/<a\b[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/is', fn (array $match): string => $match[2].' ('.$match[1].')', $content);
        $text = preg_replace('/<(?:br\s*\/?|\/(?:p|div|h[1-6]|li|tr))>/i', "\n", $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return ['subject' => $subject, 'html' => view('notifications::email', ['customContent' => $content])->render(), 'content' => $content, 'text' => $text];
    }

    public function apply(string $type, MailMessage $original, array $data): MailMessage
    {
        if (config('panel.load_environment_only')) {
            return $original;
        }
        $template = $this->load($type);
        $rendered = $this->render($template, array_merge(['panel_name' => config('app.name'), 'action_url' => $original->actionUrl ?? ''], $data));

        return (new MailMessage())->subject($rendered['subject'])->view(['notifications::email', 'emails.admin-message-text'], array_merge($rendered, ['customContent' => $rendered['content']]));
    }

    public function sanitize(string $html): string
    {
        $config = (new HtmlSanitizerConfig())->allowSafeElements()
            ->allowAttribute('style', '*')
            ->allowAttribute('width', ['table', 'td', 'img'])
            ->allowAttribute('height', ['img'])
            ->withMaxInputLength(200000);
        $html = (new HtmlSanitizer($config))->sanitize($html);

        return preg_replace_callback('/ style="([^"]*)"/i', function (array $match): string {
            $safe = [];
            foreach (explode(';', html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')) as $declaration) {
                $parts = explode(':', $declaration, 2);
                if (count($parts) !== 2) {
                    continue;
                }
                [$property, $value] = array_map('trim', $parts);
                if (! preg_match('/^(color|background-color|font-size|font-weight|font-family|font-style|text-align|text-decoration(?:-line)?|line-height|width|max-width|height|padding(?:-(?:top|right|bottom|left))?|margin(?:-(?:top|right|bottom|left))?|border(?:-(?:color|width|style|radius|collapse))?)$/i', $property)) {
                    continue;
                }
                if (! preg_match('/^[a-z0-9#.,%\s\-]+$/i', $value) && ! (in_array(strtolower($property), ['color', 'background-color'], true) && preg_match('/^(?:rgb|rgba|hsl|hsla)\([0-9.,%\s\/]+\)$/i', $value))) {
                    continue;
                }
                $safe[] = $property.':'.$value;
            }

            return $safe ? ' style="'.e(implode(';', $safe)).'"' : '';
        }, $html);
    }

    private function assertType(string $type): void
    {
        abort_unless(array_key_exists($type, self::TYPES), 422);
    }
}
