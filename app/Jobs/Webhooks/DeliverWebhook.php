<?php

namespace App\Jobs\Webhooks;

use App\Jobs\Job;
use App\Models\Webhook;
use App\Services\Webhooks\WebhookDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class DeliverWebhook extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;

    public int $timeout = 60;

    public string $uuid;

    /**
     * @param  array{event: string, timestamp: string, data: array<string, mixed>}  $payload
     */
    public function __construct(public int $webhook, public string $event, public array $payload, ?string $uuid = null)
    {
        $this->queue = 'standard';
        $this->uuid = $uuid ?? Str::uuid()->toString();
    }

    public function tries(): int
    {
        return max(1, (int) config('webhooks.tries', 3));
    }

    public function handle(WebhookDeliveryService $service): void
    {
        $webhook = Webhook::query()->find($this->webhook);

        if ($webhook === null || ! $webhook->enabled) {
            return;
        }

        $delivery = $service->send($webhook, $this->payload, $this->uuid);

        if ($delivery->shouldRetry() && $this->attempts() < $this->tries()) {
            // Respect the delay the endpoint asked for, e.g. Discord's rate limit.
            $this->release($delivery->retryAfter ?? 30 * (2 ** ($this->attempts() - 1)));
        }
    }
}
