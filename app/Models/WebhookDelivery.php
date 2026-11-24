<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property int $webhook_id
 * @property string $event
 * @property array<string, mixed> $payload
 * @property bool $successful
 * @property int|null $status_code
 * @property int $attempts
 * @property int|null $duration_ms
 * @property string|null $response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Webhook $webhook
 */
class WebhookDelivery extends Model
{
    use MassPrunable;

    public const RESOURCE_NAME = 'webhook_delivery';

    /**
     * How much of an endpoint's response is kept for troubleshooting.
     */
    public const RESPONSE_LIMIT = 2000;

    /**
     * The longest wait, in seconds, an endpoint can ask for before a delivery is retried.
     */
    public const MAX_RETRY_AFTER = 900;

    /**
     * Seconds the endpoint asked to wait before the next attempt. Not stored: it only
     * lives long enough for the job to schedule the retry.
     */
    public ?int $retryAfter = null;

    protected $table = 'webhook_deliveries';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $attributes = [
        'successful' => false,
        'attempts' => 0,
    ];

    protected $casts = [
        'webhook_id' => 'int',
        'payload' => 'array',
        'successful' => 'bool',
        'status_code' => 'int',
        'attempts' => 'int',
        'duration_ms' => 'int',
    ];

    public static array $validationRules = [
        'uuid' => ['required', 'uuid'],
        'webhook_id' => ['required', 'integer'],
        'event' => ['required', 'string', 'max:191'],
        'payload' => ['present', 'array'],
        'successful' => ['boolean'],
        'status_code' => ['nullable', 'integer'],
        'attempts' => ['integer', 'min:0'],
        'duration_ms' => ['nullable', 'integer', 'min:0'],
        'response' => ['nullable', 'string'],
    ];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * @return BelongsTo<Webhook, $this>
     */
    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function shouldRetry(): bool
    {
        return ! $this->successful
            && ($this->status_code === null || $this->status_code === 429 || $this->status_code >= 500);
    }

    /**
     * Returns models to be pruned.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        if (is_null(config('webhooks.prune_days'))) {
            throw new \LogicException('Cannot prune webhook deliveries: no "prune_days" configuration value is set.');
        }

        return static::query()->where('created_at', '<=', Carbon::now()->subDays((int) config('webhooks.prune_days')));
    }
}
