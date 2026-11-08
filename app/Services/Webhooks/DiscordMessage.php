<?php

namespace App\Services\Webhooks;

use App\Models\Webhook;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DiscordMessage
{
    /**
     * Default Discord Components V2 message settings.
     *
     * The existing message configuration keys are intentionally preserved so
     * previously configured Discord webhooks keep their customizations.
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

    private const COMPONENTS_V2_FLAG = 1 << 15;

    private const LIMITS = [
        'username' => 80,
        'content' => 500,
        'title' => 256,
        'description' => 1300,
        'footer' => 160,
    ];

    /**
     * Render a panel event as a Discord Components V2 message.
     *
     * @param  array{event: string, timestamp: string, data: array<string, mixed>}  $payload
     * @return array<string, mixed>
     */
    public static function render(Webhook $webhook, array $payload): array
    {
        $template = [...self::DEFAULTS, ...array_intersect_key($webhook->message ?? [], self::DEFAULTS)];
        $variables = self::variables($payload);

        $text = fn (string $key): ?string => self::fill(
            $template[$key] ?? null,
            $variables,
            self::LIMITS[$key],
        );

        $title = $text('title') ?? $variables['label'];
        $description = $text('description') ?? $variables['summary'];
        $icon = self::eventIcon((string) ($payload['event'] ?? ''));
        $panel = Str::limit(trim((string) ($variables['panel'] ?? 'Panel')) ?: 'Panel', 80);

        // A Section gives the header a clean hierarchy and supports a link button
        // as an accessory without requiring a bot interaction handler.
        $headerText = '-# '.Str::upper($panel).' · ACTIVITY LOG'
            ."\n## ".$icon.' '.$title
            .($description !== null ? "\n".$description : '');

        $panelUrl = filter_var($variables['panel_url'] ?? null, FILTER_VALIDATE_URL);
        if ($panelUrl !== false && $panelUrl !== null && strlen($panelUrl) <= 512) {
            $header = [
                'type' => 9,
                'components' => [
                    ['type' => 10, 'content' => $headerText],
                ],
                'accessory' => [
                    'type' => 2,
                    'style' => 5,
                    'label' => 'Open Panel',
                    'url' => $panelUrl,
                ],
            ];
        } else {
            $header = ['type' => 10, 'content' => $headerText];
        }

        $components = [$header];

        // The legacy `content` customization is retained as an additional text block.
        if ($content = $text('content')) {
            $components[] = ['type' => 10, 'content' => $content];
        }

        $details = self::details($payload);
        if ($details !== '') {
            $components[] = [
                'type' => 14,
                'divider' => true,
                'spacing' => 2,
            ];
            $components[] = ['type' => 10, 'content' => $details];
        }

        $footer = $text('footer');
        $timestamp = ! empty($template['timestamp'])
            ? self::discordTimestamp($payload['timestamp'] ?? null)
            : null;

        $footerText = '-# '.($footer ?? $panel);
        if ($timestamp !== null) {
            $footerText .= ' · '.$timestamp;
        }

        $components[] = [
            'type' => 14,
            'divider' => true,
            'spacing' => 1,
        ];
        $components[] = ['type' => 10, 'content' => $footerText];

        return array_filter([
            'username' => $text('username'),
            'avatar_url' => filter_var($template['avatar_url'], FILTER_VALIDATE_URL) ?: null,
            'flags' => self::COMPONENTS_V2_FLAG,
            'components' => [[
                'type' => 17,
                'accent_color' => self::color($template['color'] ?? null),
                'components' => $components,
            ]],
            'allowed_mentions' => ['parse' => []],
        ], fn ($value) => $value !== null);
    }

    /**
     * Values available to message templates. Event fields can be accessed with
     * dot notation, for example {{data.name}} or {{data.actor.name}}.
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

    /**
     * Build readable audit details without dumping entire model records or secrets.
     */
    private static function details(array $payload): string
    {
        $event = (string) ($payload['event'] ?? '');
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        return WebhookEvents::isActivity($event)
            ? self::activityDetails($data)
            : self::modelDetails($event, $data);
    }

    private static function activityDetails(array $data): string
    {
        $lines = [];

        $actor = is_array($data['actor'] ?? null) ? $data['actor'] : [];
        $actorName = self::entityName($actor, 'System');
        $lines[] = "**Performed by**\n".self::inline($actorName);

        $subjects = collect($data['subjects'] ?? [])
            ->filter(fn ($subject) => is_array($subject))
            ->map(fn (array $subject) => self::entityName($subject, 'Unknown target'))
            ->filter()
            ->values()
            ->all();

        if ($subjects !== []) {
            $lines[] = '**Target'.(count($subjects) === 1 ? '' : 's')."**\n".implode(', ', array_map(fn (string $subject): string => self::inline($subject), $subjects));
        }

        $description = $data['description'] ?? null;
        if (is_string($description) && trim($description) !== '') {
            $lines[] = "**Details**\n".Str::limit(trim($description), 700);
        }

        $ip = $data['ip'] ?? null;
        if (is_string($ip) && trim($ip) !== '') {
            $lines[] = "**Source IP**\n`".self::escapeCode($ip).'`';
        }

        $properties = self::safeProperties($data['properties'] ?? []);
        if ($properties !== []) {
            $propertyLines = [];
            foreach ($properties as $key => $value) {
                $propertyLines[] = '• **'.Str::headline((string) $key).'**: '.self::inline((string) $value);
            }
            $lines[] = "**Additional information**\n".implode("\n", $propertyLines);
        }

        return Str::limit(implode("\n\n", $lines), 1250);
    }

    private static function modelDetails(string $event, array $data): string
    {
        $modelEvent = Str::after($event, WebhookEvents::MODEL_PREFIX);
        [$resource] = array_pad(explode('.', $modelEvent, 2), 2, '');
        $resourceName = Str::headline(str_replace('_', ' ', $resource));

        $name = Arr::first(
            Arr::map(['name', 'username', 'email', 'database', 'short', 'uuid'], fn (string $key) => $data[$key] ?? null),
            fn ($value) => is_string($value) && $value !== '',
        );

        $record = $name !== null ? self::inline($name) : '**'.$resourceName.'**';
        if (isset($data['id']) && is_scalar($data['id'])) {
            $record .= ' · `#'.self::escapeCode((string) $data['id']).'`';
        }

        $lines = [
            "**Resource**\n".$resourceName,
            "**Record**\n".$record,
        ];

        $changes = array_values(array_filter(
            is_array($data['changes'] ?? null) ? $data['changes'] : [],
            fn ($change) => is_string($change) && $change !== '',
        ));

        if ($changes !== []) {
            $changed = array_map(fn (string $change) => '`'.self::escapeCode(Str::headline($change)).'`', $changes);
            $lines[] = "**Changed fields**\n".implode(', ', $changed);
        }

        return Str::limit(implode("\n\n", $lines), 1250);
    }

    /**
     * Keep useful scalar properties while excluding credentials and authentication data.
     * The dispatcher already redacts sensitive keys; this is a second presentation-layer guard.
     *
     * @return array<string, scalar>
     */
    private static function safeProperties(mixed $properties): array
    {
        if (is_object($properties) && method_exists($properties, 'toArray')) {
            $properties = $properties->toArray();
        }

        if (! is_array($properties)) {
            return [];
        }

        $safe = [];
        $blocked = ['password', 'secret', 'token', 'totp', 'cookie', 'authorization', 'credential', 'private_key', 'api_key', 'key'];

        foreach ($properties as $key => $value) {
            if (! is_string($key) || Str::contains(strtolower($key), $blocked) || ! is_scalar($value)) {
                continue;
            }

            $value = (string) $value;
            if (trim($value) === '' || strlen($value) > 240) {
                continue;
            }

            $safe[$key] = $value;
            if (count($safe) >= 8) {
                break;
            }
        }

        return $safe;
    }

    private static function entityName(array $entity, string $fallback): string
    {
        $name = $entity['name'] ?? null;
        $type = $entity['type'] ?? null;
        $id = $entity['id'] ?? null;

        $label = is_string($name) && trim($name) !== '' ? trim($name) : $fallback;

        if (is_string($type) && $type !== '') {
            $label .= ' · '.Str::headline(str_replace('_', ' ', $type));
        }

        if (is_scalar($id) && (string) $id !== '') {
            $label .= ' (`#'.self::escapeCode((string) $id).'`)';
        }

        return $label;
    }

    private static function inline(string $value): string
    {
        return '`'.self::escapeCode(Str::limit(trim($value), 300)).'`';
    }

    private static function escapeCode(string $value): string
    {
        return str_replace(['\\', '`', "\r", "\n"], ['\\\\', "'", ' ', ' '], $value);
    }

    private static function eventIcon(string $event): string
    {
        return match (true) {
            Str::startsWith($event, 'server:power.') => '⚡',
            Str::startsWith($event, 'server:file.'), Str::startsWith($event, 'server:sftp.') => '📁',
            Str::startsWith($event, 'server:backup.') => '💾',
            Str::startsWith($event, 'server:database.') => '🗄️',
            Str::startsWith($event, 'server:') => '🖥️',
            Str::startsWith($event, 'auth:') => '🔐',
            Str::startsWith($event, 'user:') => '👤',
            Str::startsWith($event, WebhookEvents::MODEL_PREFIX) && Str::endsWith($event, '.created') => '🟢',
            Str::startsWith($event, WebhookEvents::MODEL_PREFIX) && Str::endsWith($event, '.deleted') => '🔴',
            Str::startsWith($event, WebhookEvents::MODEL_PREFIX) => '📝',
            default => '🔔',
        };
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
                $value === null => $match[0],
                is_bool($value) => $value ? 'true' : 'false',
                is_scalar($value) => (string) $value,
                default => (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            };
        }, $template);

        return trim((string) $filled) === '' ? null : Str::limit((string) $filled, $limit - 3);
    }

    private static function discordTimestamp(mixed $timestamp): ?string
    {
        if (! is_string($timestamp) || trim($timestamp) === '') {
            return null;
        }

        $unix = strtotime($timestamp);

        return $unix === false ? null : '<t:'.$unix.':f>';
    }

    private static function color(mixed $color): int
    {
        $hex = ltrim(is_string($color) ? $color : '', '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return preg_match('/^[0-9a-f]{6}$/i', $hex)
            ? (int) hexdec($hex)
            : (int) hexdec(ltrim(self::DEFAULTS['color'], '#'));
    }
}
