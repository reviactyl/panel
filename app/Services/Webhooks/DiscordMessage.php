<?php

namespace App\Services\Webhooks;

use App\Models\Webhook;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DiscordMessage
{
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
    private const MESSAGE_TEXT_LIMIT = 4000;
    private const TEXT_DISPLAY_LIMIT = 2000;
    private const DETAILS_LIMIT = 1500;

    /**
     * Render an event as a Discord Components V2 message.
     *
     * @param array{event: string, timestamp: string, data: array<string, mixed>} $payload
     * @return array<string, mixed>
     */
    public static function render(Webhook $webhook, array $payload): array
    {
        $payload = self::cleanUtf8($payload);
        $message = self::cleanUtf8($webhook->message ?? []);
        $template = [...self::DEFAULTS, ...array_intersect_key($message, self::DEFAULTS)];
        $variables = self::variables($payload);
        $event = (string) ($payload['event'] ?? '');

        $text = fn (string $key): ?string => self::fill($template[$key] ?? null, $variables);

        $username = $text('username');
        $title = Str::limit($text('title') ?? $variables['label'], 256);
        $description = $text('description');

        // The default model summary repeats the title and record name. Model cards
        // already show those separately, so only use the event description by default.
        if (($template['description'] ?? null) === self::DEFAULTS['description']) {
            $description = self::defaultDescription($payload);
        }

        $content = $text('content');
        $panel = Str::limit(trim((string) ($variables['panel'] ?? 'Panel')) ?: 'Panel', 80);
        $headerLabel = '-# '.Str::upper($panel).' · '.self::eventGroup($event);
        $headerTitle = '## '.self::eventIcon($event).' '.$title;

        $header = [
            'type' => 9,
            'components' => [
                ['type' => 10, 'content' => $headerLabel],
                ['type' => 10, 'content' => $headerTitle],
            ],
        ];

        $panelUrl = filter_var($variables['panel_url'] ?? null, FILTER_VALIDATE_URL);
        if ($panelUrl !== false && $panelUrl !== null && strlen($panelUrl) <= 512) {
            $header['accessory'] = [
                'type' => 2,
                'style' => 5,
                'label' => 'Open Panel',
                'url' => $panelUrl,
            ];
        }

        $details = Str::limit(self::details($payload), self::DETAILS_LIMIT);
        $footer = $text('footer') ?? $panel;

        if (! empty($template['timestamp'])) {
            $timestamp = self::discordTimestamp($payload['timestamp'] ?? null);
            if ($timestamp !== null) {
                $footer .= ' · '.$timestamp;
            }
        }

        if (WebhookEvents::isActivity($event) && isset($payload['data']['id']) && is_scalar($payload['data']['id'])) {
            $footer .= ' · Log #'.self::escapeCode((string) $payload['data']['id']);
        }

        $footer = Str::limit('-# '.$footer, 300);
        $available = max(0, self::MESSAGE_TEXT_LIMIT - mb_strlen($headerLabel) - mb_strlen($headerTitle));
        $reserved = min($available, mb_strlen($details) + mb_strlen($footer));
        $templateBudget = max(0, $available - $reserved);
        $templateRemaining = $templateBudget;
        $components = [$header];

        // Preserve custom text first, while reserving space for audit details and the footer.
        self::appendText($components, $content, $templateRemaining, 1);
        self::appendText($components, $description, $templateRemaining, 1);

        $usedTemplate = $templateBudget - $templateRemaining;
        $remaining = max(0, $available - $usedTemplate);
        self::appendText($components, $details, $remaining, 2);
        self::appendText($components, $footer, $remaining, 1);

        return array_filter([
            'username' => $username === null ? null : Str::limit($username, 80),
            'avatar_url' => filter_var($template['avatar_url'], FILTER_VALIDATE_URL) ?: null,
            'flags' => self::COMPONENTS_V2_FLAG,
            'components' => [[
                'type' => 17,
                'accent_color' => self::color($template['color'] ?? null, $event),
                'components' => $components,
            ]],
            'allowed_mentions' => ['parse' => []],
        ], fn ($value) => $value !== null);
    }

    /** @return array<string, mixed> */
    public static function variables(array $payload): array
    {
        $event = (string) ($payload['event'] ?? '');
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $label = WebhookEvents::label($event);

        return [
            ...$payload,
            'label' => $label,
            'summary' => self::summary($event, $label, $data),
            'panel' => (string) config('app.name'),
            'panel_url' => (string) config('app.url'),
        ];
    }

    private static function appendText(array &$components, ?string $text, int &$remaining, int $spacing): void
    {
        if ($text === null || trim($text) === '' || $remaining <= 0) {
            return;
        }

        $text = self::cleanUtf8($text);
        if (mb_strlen($text) > $remaining) {
            $text = $remaining > 1 ? mb_substr($text, 0, $remaining - 1).'…' : '…';
        }

        $chunks = [];
        while ($text !== '') {
            $chunk = mb_substr($text, 0, self::TEXT_DISPLAY_LIMIT);
            $chunkLength = mb_strlen($chunk);

            if ($chunkLength === self::TEXT_DISPLAY_LIMIT && mb_strlen($text) > self::TEXT_DISPLAY_LIMIT) {
                $breakAt = mb_strrpos($chunk, "\n");
                if ($breakAt === false || $breakAt < intdiv(self::TEXT_DISPLAY_LIMIT, 2)) {
                    $breakAt = mb_strrpos($chunk, ' ');
                }

                if ($breakAt !== false && $breakAt >= intdiv(self::TEXT_DISPLAY_LIMIT, 2)) {
                    $chunk = mb_substr($chunk, 0, $breakAt + 1);
                    $chunkLength = mb_strlen($chunk);
                }
            }

            $chunks[] = ['type' => 10, 'content' => $chunk];
            $remaining -= $chunkLength;
            $text = mb_substr($text, $chunkLength);
        }

        if ($chunks !== []) {
            $components[] = ['type' => 14, 'divider' => true, 'spacing' => $spacing];
            array_push($components, ...$chunks);
        }
    }

    private static function cleanUtf8(mixed $value): mixed
    {
        if (is_string($value)) {
            return json_decode(
                json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        }

        if (is_array($value)) {
            $clean = [];
            foreach ($value as $key => $item) {
                $cleanKey = is_string($key) ? self::cleanUtf8($key) : $key;
                $clean[$cleanKey] = self::cleanUtf8($item);
            }

            return $clean;
        }

        return $value;
    }

    private static function defaultDescription(array $payload): ?string
    {
        $event = (string) ($payload['event'] ?? '');
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        if (! WebhookEvents::isActivity($event)) {
            return null;
        }

        $description = $data['description'] ?? null;

        return is_string($description) && trim($description) !== '' ? trim($description) : null;
    }

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
        $lines[] = "**Performed by**\n".self::inline(self::entityName($actor, 'System'));

        $subjects = collect($data['subjects'] ?? [])
            ->filter(fn ($subject) => is_array($subject))
            ->map(fn (array $subject) => self::entityName($subject, 'Unknown target'))
            ->filter()
            ->values()
            ->all();

        if ($subjects !== []) {
            $lines[] = '**Target'.(count($subjects) === 1 ? '' : 's')."**\n".implode(', ', array_map(self::inline(...), $subjects));
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
            $lines[] = "**Details**\n".implode("\n", $propertyLines);
        }

        return implode("\n\n", $lines);
    }

    private static function modelDetails(string $event, array $data): string
    {
        $modelEvent = Str::after($event, WebhookEvents::MODEL_PREFIX);
        [$resource, $action] = array_pad(explode('.', $modelEvent, 2), 2, '');
        $resourceName = $resource !== '' ? Str::headline(str_replace('_', ' ', $resource)) : 'Record';
        $lines = [];

        $actor = is_array($data['actor'] ?? null) ? $data['actor'] : [];
        if ($actor !== []) {
            $lines[] = "**Performed by**\n".self::inline(self::entityName($actor, 'System'));
        }

        $name = Arr::first(
            Arr::map(['name', 'username', 'short', 'uuidShort', 'identifier', 'database'], fn (string $key) => $data[$key] ?? null),
            fn ($value) => is_string($value) && $value !== '',
        );

        $record = $name !== null ? self::inline($name) : '**'.$resourceName.'**';
        if (isset($data['id']) && is_scalar($data['id'])) {
            $record .= ' · `#'.self::escapeCode((string) $data['id']).'`';
        }

        $lines[] = "**Resource**\n".$resourceName;
        $lines[] = "**Record**\n".$record;

        $changes = array_values(array_filter(
            is_array($data['changes'] ?? null) ? $data['changes'] : [],
            fn ($change) => is_string($change) && $change !== '',
        ));

        if ($changes !== []) {
            $shown = array_slice($changes, 0, 12);
            $changeLines = array_map(
                fn (string $change): string => '• '.Str::headline(str_replace(['_', '-'], ' ', $change)),
                $shown,
            );
            if (count($changes) > count($shown)) {
                $changeLines[] = '• And '.(count($changes) - count($shown)).' more';
            }
            $lines[] = "**Changed fields**\n".implode("\n", $changeLines);
        }

        if ($action === 'created') {
            $highlights = self::modelHighlights($resource, $data);
            if ($highlights !== []) {
                $highlightLines = [];
                foreach ($highlights as $label => $value) {
                    $highlightLines[] = '• **'.$label.'**: '.self::inline((string) $value);
                }
                $lines[] = "**Details**\n".implode("\n", $highlightLines);
            }
        }

        return implode("\n\n", $lines);
    }

    /** @return array<string, scalar> */
    private static function modelHighlights(string $resource, array $data): array
    {
        $fields = match ($resource) {
            'server' => ['uuidShort' => 'Short ID', 'node_id' => 'Node ID', 'memory' => 'Memory', 'disk' => 'Disk', 'cpu' => 'CPU'],
            'node' => ['fqdn' => 'Address', 'scheme' => 'Protocol'],
            'location' => ['short' => 'Short code', 'long' => 'Location'],
            'database_host' => ['host' => 'Host', 'port' => 'Port'],
            'backup' => ['name' => 'Name', 'is_locked' => 'Locked'],
            'schedule' => ['name' => 'Name', 'is_active' => 'Active'],
            default => [],
        };

        $highlights = [];
        foreach ($fields as $key => $label) {
            $value = $data[$key] ?? null;
            if (is_scalar($value) && (string) $value !== '') {
                $highlights[$label] = self::formatModelValue($key, $value);
            }
        }

        return $highlights;
    }

    private static function formatModelValue(string $key, mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (in_array($key, ['memory', 'disk'], true) && is_numeric($value)) {
            return number_format((float) $value).' MB';
        }
        if ($key === 'cpu' && is_numeric($value)) {
            return number_format((float) $value).'%';
        }
        if ($key === 'node_id' && is_scalar($value)) {
            return '#'.$value;
        }

        return (string) $value;
    }

    /** @return array<string, scalar> */
    private static function safeProperties(mixed $properties): array
    {
        if (is_object($properties) && method_exists($properties, 'toArray')) {
            $properties = $properties->toArray();
        }
        if (! is_array($properties)) {
            return [];
        }

        $safe = [];
        $sensitive = '/(?:password|secret|token|totp|cookie|authorization|credential|private[_-]?key|api[_-]?key|access[_-]?key|ssh[_-]?key)/i';
        foreach ($properties as $key => $value) {
            if (! is_string($key) || preg_match($sensitive, $key) || ! is_scalar($value)) {
                continue;
            }

            $value = is_bool($value) ? ($value ? 'Yes' : 'No') : (string) $value;
            if (trim($value) === '' || mb_strlen($value) > 160) {
                continue;
            }

            $safe[$key] = $value;
            if (count($safe) >= 10) {
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
            $label .= ' (#'.self::escapeCode((string) $id).')';
        }

        return $label;
    }

    private static function inline(string $value): string
    {
        return '`'.self::escapeCode(Str::limit(trim($value), 240)).'`';
    }

    private static function escapeCode(string $value): string
    {
        return str_replace(['\\', '`', "\r", "\n"], ['\\\\', "'", ' ', ' '], $value);
    }

    private static function eventGroup(string $event): string
    {
        if (! WebhookEvents::isActivity($event)) {
            return 'RECORD EVENT';
        }

        [$scope] = array_pad(explode(':', $event, 2), 2, '');

        return strtoupper($scope !== '' ? Str::headline($scope).' ACTIVITY' : 'ACTIVITY LOG');
    }

    private static function eventIcon(string $event): string
    {
        return match (true) {
            Str::contains($event, ['fail', 'denied', 'blocked']) => '⛔',
            Str::contains($event, ['delete', 'deleted', 'kill']) => '🗑️',
            Str::contains($event, ['complete', 'success', 'created']) => '✅',
            Str::startsWith($event, 'server:power.') => '⚡',
            Str::startsWith($event, 'server:file.'), Str::startsWith($event, 'server:sftp.') => '📁',
            Str::startsWith($event, 'server:backup.') => '💾',
            Str::startsWith($event, 'server:database.') => '🗄️',
            Str::startsWith($event, 'server:') => '🖥️',
            Str::startsWith($event, 'auth:') => '🔐',
            Str::startsWith($event, 'user:') => '👤',
            Str::startsWith($event, WebhookEvents::MODEL_PREFIX) && Str::endsWith($event, '.updated') => '✏️',
            Str::startsWith($event, WebhookEvents::MODEL_PREFIX) => '📋',
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

    private static function fill(mixed $template, array $variables): ?string
    {
        if (! is_string($template) || trim($template) === '') {
            return null;
        }

        $template = self::cleanUtf8($template);
        $filled = preg_replace_callback('/{{\s*([A-Za-z0-9_.\-]+)\s*}}/', function (array $match) use ($variables): string {
            $value = data_get($variables, $match[1]);

            return match (true) {
                $value === null => $match[0],
                is_bool($value) => $value ? 'true' : 'false',
                is_scalar($value) => (string) $value,
                default => (string) json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            };
        }, $template);

        return trim((string) $filled) === '' ? null : trim((string) $filled);
    }

    private static function discordTimestamp(mixed $timestamp): ?string
    {
        if (! is_string($timestamp) || trim($timestamp) === '') {
            return null;
        }

        $unix = strtotime($timestamp);

        return $unix === false ? null : '<t:'.$unix.':f> · <t:'.$unix.':R>';
    }

    private static function color(mixed $color, string $event): int
    {
        $hex = ltrim(is_string($color) ? $color : '', '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (preg_match('/^[0-9a-f]{6}$/i', $hex) && strtolower($hex) !== '5865f2') {
            return (int) hexdec($hex);
        }

        return self::eventColor($event);
    }

    private static function eventColor(string $event): int
    {
        return match (true) {
            Str::contains($event, ['fail', 'denied', 'blocked', 'delete', 'deleted', 'kill']) => 0xED4245,
            Str::contains($event, ['complete', 'success', 'created']) => 0x57F287,
            Str::startsWith($event, 'server:power.') => 0xFEE75C,
            Str::startsWith($event, 'server:file.'), Str::startsWith($event, 'server:sftp.') => 0x3498DB,
            Str::startsWith($event, 'server:backup.') => 0x9B59B6,
            Str::startsWith($event, 'auth:') => 0xE67E22,
            default => 0x5865F2,
        };
    }
}
