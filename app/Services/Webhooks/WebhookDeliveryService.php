<?php

namespace App\Services\Webhooks;

use App\Exceptions\Service\Webhook\WebhookEndpointException;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebhookDeliveryService
{
    public const USER_AGENT = 'Reviactyl-Webhooks/1.0';

    public function __construct(private WebhookEndpointGuard $guard) {}

    /**
     * Sends an event to a webhook's endpoint and records how it went. Sending the same
     * delivery again, identified by its UUID, counts as another attempt at it rather
     * than a new delivery.
     *
     * @param  array{event: string, timestamp: string, data: array<string, mixed>}  $payload
     */
    public function send(Webhook $webhook, array $payload, ?string $uuid = null): WebhookDelivery
    {
        $uuid ??= Str::uuid()->toString();

        /** @var WebhookDelivery $delivery */
        $delivery = WebhookDelivery::query()->firstOrNew(['uuid' => $uuid]);
        $delivery->fill([
            'webhook_id' => $webhook->id,
            'event' => $payload['event'],
            'payload' => $payload,
            'attempts' => $delivery->attempts + 1,
            'successful' => false,
            'status_code' => null,
            'response' => null,
        ]);

        $body = (string) json_encode(
            $webhook->isDiscord() ? DiscordMessage::render($webhook, $payload) : ['id' => $uuid, ...$payload],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        $started = microtime(true);

        try {
            $target = $this->guard->resolve($webhook->endpoint);

            $response = Http::withHeaders($this->headers($webhook, $payload['event'], $uuid, $body))
                ->withOptions($this->options($target))
                ->connectTimeout(5)
                ->timeout((int) config('webhooks.timeout', 10))
                ->withBody($body, 'application/json')
                ->post($webhook->endpoint);

            $delivery->status_code = $response->status();
            $delivery->successful = $response->successful();
            $delivery->response = $this->excerpt($response->body());
        } catch (WebhookEndpointException $exception) {
            $delivery->response = $exception->getMessage();
        } catch (\Throwable) {
            // The underlying message names the full endpoint, which is usually the secret.
            $delivery->response = trans('admin/webhooks.errors.unreachable', ['host' => $webhook->endpointHost()]);
        }

        $delivery->duration_ms = (int) round((microtime(true) - $started) * 1000);
        $delivery->save();

        return $delivery;
    }

    /**
     * @return array<string, string>
     */
    private function headers(Webhook $webhook, string $event, string $uuid, string $body): array
    {
        $headers = [];

        if (! $webhook->isDiscord()) {
            foreach ($webhook->headers ?? [] as $name => $value) {
                $name = trim((string) $name);
                if (! preg_match(Webhook::HEADER_NAME_PATTERN, $name)
                    || in_array(strtolower($name), Webhook::RESERVED_HEADERS, true)
                    || preg_match('/[\r\n]/', (string) $value)) {
                    continue;
                }

                $headers[$name] = (string) $value;
            }

            $headers['X-Webhook-Event'] = $event;
            $headers['X-Webhook-Delivery'] = $uuid;

            if (! empty($webhook->secret)) {
                $headers['X-Webhook-Signature'] = 'sha256='.hash_hmac('sha256', $body, $webhook->secret);
            }
        }

        $headers['User-Agent'] = self::USER_AGENT;

        return $headers;
    }

    /**
     * @param  array{host: string, port: int, address: string}|null  $target
     * @return array<string, mixed>
     */
    private function options(?array $target): array
    {
        $options = ['allow_redirects' => false];

        if ($target !== null && $target['host'] !== $target['address']) {
            if (! extension_loaded('curl')) {
                throw new WebhookEndpointException(trans('admin/webhooks.errors.curl_required'));
            }

            $address = str_contains($target['address'], ':') ? '['.$target['address'].']' : $target['address'];
            $options['curl'] = [CURLOPT_RESOLVE => [$target['host'].':'.$target['port'].':'.$address]];
        }

        return $options;
    }

    private function excerpt(string $body): ?string
    {
        $body = trim($body);

        return $body === '' ? null : Str::limit($body, WebhookDelivery::RESPONSE_LIMIT);
    }
}
