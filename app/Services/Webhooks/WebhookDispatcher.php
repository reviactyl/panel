<?php

namespace App\Services\Webhooks;

use App\Jobs\Webhooks\DeliverWebhook;
use App\Models\ActivityLog;
use App\Models\ActivityLogSubject;
use App\Models\Webhook;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class WebhookDispatcher
{
    private const SENSITIVE_KEYS = ['password', 'secret', 'token', 'totp'];

    private const SENSITIVE_PROPERTIES = [
        'server:startup.edit' => ['old', 'new'],
    ];

    public function model(string $action, Model $model): void
    {
        $resource = WebhookEvents::resourceFor($model);
        if ($resource === null) {
            return;
        }

        $event = WebhookEvents::modelEvent($resource, $action);
        $webhooks = $this->subscribers($event);
        if ($webhooks === []) {
            return;
        }

        $data = ['id' => $model->getKey(), ...$this->redact($model->attributesToArray())];

        if ($action === 'updated') {
            $changes = array_values(array_diff(
                array_keys($model->getChanges()),
                $model->getHidden(),
                [$model->getUpdatedAtColumn()],
            ));

            if ($changes === []) {
                return;
            }

            $data['changes'] = $changes;
        }

        $this->queue($webhooks, $event, $data);
    }

    public function activity(ActivityLog $activity): void
    {
        $webhooks = $this->subscribers($activity->event);
        if ($webhooks === []) {
            return;
        }

        $activity->loadMissing('actor')->load('subjects.subject');

        $this->queue($webhooks, $activity->event, [
            'id' => $activity->id,
            'description' => $activity->description,
            'ip' => $activity->ip,
            'actor' => $activity->actor ? $this->describe($activity->actor) : null,
            'subjects' => $activity->subjects
                ->map(fn (ActivityLogSubject $subject): array => $subject->subject
                    ? $this->describe($subject->subject)
                    : ['type' => $subject->subject_type, 'id' => $subject->subject_id, 'name' => null])
                ->values()
                ->all(),
            'properties' => $this->redact(Arr::except(
                $activity->properties?->toArray() ?? [],
                self::SENSITIVE_PROPERTIES[$activity->event] ?? [],
            )),
        ], $activity->timestamp);
    }

    public function followsActivity(string $event): bool
    {
        return $this->subscribers($event) !== [];
    }

    /**
     * Builds what is sent to an endpoint for an event.
     *
     * @return array{event: string, timestamp: string, data: array<string, mixed>}
     */
    public function envelope(string $event, array $data, ?CarbonInterface $at = null): array
    {
        return [
            'event' => $event,
            'timestamp' => ($at ?? Carbon::now())->toIso8601String(),
            'data' => $data,
        ];
    }

    /**
     * A made-up event used to check that an endpoint is reachable.
     *
     * @return array{event: string, timestamp: string, data: array<string, mixed>}
     */
    public function sample(): array
    {
        return $this->envelope(WebhookEvents::modelEvent('server', 'created'), [
            'id' => 1,
            'uuid' => '6f2d0b53-1b0f-4b63-8f0d-2f6f1c0a9e11',
            'uuidShort' => '6f2d0b53',
            'name' => 'Example Server',
            'description' => 'This is a test delivery, no server was created.',
            'owner_id' => 1,
            'node_id' => 1,
            'memory' => 1024,
            'disk' => 5120,
            'cpu' => 100,
        ]);
    }

    /**
     * @return int[]
     */
    private function subscribers(string $event): array
    {
        return Webhook::subscriptions()[$event] ?? [];
    }

    /**
     * @param  int[]  $webhooks
     */
    private function queue(array $webhooks, string $event, array $data, ?CarbonInterface $at = null): void
    {
        $payload = $this->envelope($event, $data, $at);

        foreach ($webhooks as $webhook) {
            DeliverWebhook::dispatch($webhook, $event, $payload)->afterCommit();
        }
    }

    /**
     * @return array{type: string, id: mixed, name: string|null}
     */
    private function describe(Model $model): array
    {
        $name = null;
        foreach (['username', 'name', 'email', 'identifier'] as $attribute) {
            $value = $model->getAttribute($attribute);
            if (is_string($value) && $value !== '') {
                $name = $value;
                break;
            }
        }

        return ['type' => $this->typeOf($model::class), 'id' => $model->getKey(), 'name' => $name];
    }

    private function typeOf(string $class): string
    {
        return array_search($class, Relation::morphMap(), true) ?: Str::snake(class_basename($class));
    }

    private function redact(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && Str::contains(strtolower($key), self::SENSITIVE_KEYS)) {
                continue;
            }

            $clean[$key] = is_array($value) ? $this->redact($value) : $value;
        }

        return $clean;
    }
}
