<?php

namespace App\Services\Webhooks;

use App\Models\Webhook;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DiscordMessage
{
    /**
     * What a Discord webhook says until its message is customized.
     */
    public const DEFAULTS = [
        'username' => null,
        'avatar_url' => null,
        'content' => null,
        'title' => '{{label}}',
        'description' => '{{summary}}',
        'color' => '#5865f2',
        'footer' => '{{panel}}',
        'timestamp' => true,
    ];

    /**
     * The longest Discord accepts for each part of a message.
     */
    private const LIMITS = [
        'username' => 80,
        'content' => 2000,
        'title' => 256,
        'description' => 4096,
        'footer' => 2048,
    ];

    /**
     * Turns an event into the message Discord expects, filling in any {{variables}}
     * used by the webhook's message template.
     *
     * @param  array{event: string, timestamp: string, data: array<string, mixed>}  $payload
     * @return array<string, mixed>
     */
    public static function render(Webhook $webhook, array $payload): array
    {
        $template = [...self::DEFAULTS, ...array_intersect_key($webhook->message ?? [], self::DEFAULTS)];
        $variables = self::variables($payload);

        $text = fn (string $key): ?string => self::fill($template[$key] ?? null, $variables, self::LIMITS[$key]);

        $embed = array_filter([
            'title' => $text('title'),
            'description' => $text('description'),
        ]);

        if ($embed !== []) {
            $embed['color'] = self::color($template['color'] ?? null);

            if ($footer = $text('footer')) {
                $embed['footer'] = ['text' => $footer];
            }

            if ($template['timestamp']) {
                $embed['timestamp'] = $payload['timestamp'];
            }
        }

        $content = $text('content');

        return array_filter([
            'username' => $text('username'),
            'avatar_url' => filter_var($template['avatar_url'], FILTER_VALIDATE_URL) ?: null,
            // Discord rejects a message with nothing in it, so there is always something to show.
            'content' => $content ?? ($embed === [] ? Str::limit($variables['summary'], self::LIMITS['content']) : null),
            'embeds' => $embed === [] ? null : [$embed],
            'allowed_mentions' => ['parse' => []],
        ], fn ($value) => $value !== null);
    }

    /**
     * The values a message template can refer to. Anything in the event itself is
     * reachable with dot notation, such as {{data.name}}.
     *
     * @return array<string, mixed>
     */
    public static function variables(array $payload): array
    {
        $event = (string) ($payload['event'] ?? '');
        $label = WebhookEvents::label($event);

        return [
            ...$payload,
            'label' => $label,
            'summary' => self::summary($event, $label, $payload['data'] ?? []),
            'panel' => (string) config('app.name'),
            'panel_url' => (string) config('app.url'),
        ];
    }

    private static function summary(string $event, string $label, array $data): string
    {
        if (! WebhookEvents::isActivity($event)) {
            $name = Arr::first(
                Arr::map(['name', 'username', 'email', 'database', 'short', 'uuid'], fn (string $key) => $data[$key] ?? null),
                fn ($value) => is_string($value) && $value !== '',
            ) ?? '#'.($data['id'] ?? '?');

            return trans('admin/webhooks.summary.model', ['label' => $label, 'name' => $name]);
        }

        $subjects = collect($data['subjects'] ?? [])
            ->map(fn ($subject) => $subject['name'] ?? null)
            ->filter()
            ->implode(', ');

        return trans($subjects === '' ? 'admin/webhooks.summary.activity' : 'admin/webhooks.summary.activity_on', [
            'actor' => $data['actor']['name'] ?? trans('admin/webhooks.summary.system'),
            'label' => $label,
            'event' => $event,
            'subjects' => $subjects,
        ]);
    }

    /**
     * @param  array<string, mixed>  $variables
     */
    private static function fill(mixed $template, array $variables, int $limit): ?string
    {
        if (! is_string($template) || trim($template) === '') {
            return null;
        }

        $filled = preg_replace_callback('/{{\s*([A-Za-z0-9_.\-]+)\s*}}/', function (array $match) use ($variables): string {
            $value = data_get($variables, $match[1]);

            return match (true) {
                // A variable that does not exist is left as written so a typo is easy to spot.
                $value === null => $match[0],
                is_bool($value) => $value ? 'true' : 'false',
                is_scalar($value) => (string) $value,
                default => (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            };
        }, $template);

        return trim((string) $filled) === '' ? null : Str::limit((string) $filled, $limit - 3);
    }

    private static function color(mixed $color): int
    {
        $hex = ltrim(is_string($color) ? $color : '', '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return preg_match('/^[0-9a-f]{6}$/i', $hex) ? (int) hexdec($hex) : (int) hexdec(ltrim(self::DEFAULTS['color'], '#'));
    }
}
