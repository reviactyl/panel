<?php

namespace Tests\Integration\Services\Webhooks;

use App\Models\Webhook;
use App\Services\Webhooks\DiscordMessage;
use App\Services\Webhooks\WebhookDeliveryService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Integration\IntegrationTestCase;

class DiscordMessageTest extends IntegrationTestCase
{
    public function test_activity_card_explains_actor_target_and_action_details(): void
    {
        $webhook = Webhook::factory()->create(['type' => Webhook::TYPE_DISCORD]);
        $payload = [
            'event' => 'server:power.stop',
            'timestamp' => '2026-11-09T03:25:00+06:00',
            'data' => [
                'id' => 4097,
                'description' => 'Power Stop',
                'ip' => '203.0.113.10',
                'actor' => ['type' => 'user', 'id' => 1, 'name' => 'tasfiq'],
                'subjects' => [['type' => 'server', 'id' => 3, 'name' => 'Tasfiq SMP']],
                'properties' => ['signal' => 'stop', 'api_token' => 'must-not-appear'],
            ],
        ];

        $message = DiscordMessage::render($webhook, $payload);
        $text = implode("\n", $this->textDisplays($message['components']));

        $this->assertSame(32768, $message['flags']);
        $this->assertStringContainsString('Power: Stop', $text);
        $this->assertStringContainsString('Performed by', $text);
        $this->assertStringContainsString('tasfiq', $text);
        $this->assertStringContainsString('Target', $text);
        $this->assertStringContainsString('Tasfiq SMP', $text);
        $this->assertStringContainsString('203.0.113.10', $text);
        $this->assertStringNotContainsString('must-not-appear', $text);
        $this->assertStringNotContainsString('api_token', $text);
        $this->assertStringNotContainsString('\\n', $text);
    }

    public function test_model_card_shows_record_and_changed_fields_without_duplicate_summary(): void
    {
        $webhook = Webhook::factory()->create(['type' => Webhook::TYPE_DISCORD]);
        $payload = [
            'event' => 'model:user.updated',
            'timestamp' => '2026-11-09T04:04:00+06:00',
            'data' => [
                'id' => 1,
                'username' => 'tasfiq',
                'actor' => ['type' => 'user', 'id' => 1, 'name' => 'tasfiq'],
                'changes' => ['last_seen'],
            ],
        ];

        $message = DiscordMessage::render($webhook, $payload);
        $text = implode("\n", $this->textDisplays($message['components']));

        $this->assertStringContainsString('User updated', $text);
        $this->assertStringContainsString('Performed by', $text);
        $this->assertStringContainsString('Resource', $text);
        $this->assertStringContainsString('Record', $text);
        $this->assertStringContainsString('tasfiq', $text);
        $this->assertStringContainsString('Changed fields', $text);
        $this->assertStringContainsString('Last Seen', $text);
        $this->assertStringNotContainsString('User updated: tasfiq', $text);
    }

    public function test_long_custom_text_stays_within_components_v2_limits(): void
    {
        $webhook = Webhook::factory()->create([
            'type' => Webhook::TYPE_DISCORD,
            'message' => [
                'content' => str_repeat('c', 2000),
                'description' => str_repeat('d', 4096),
                'footer' => str_repeat('f', 2048),
            ],
        ]);

        $message = DiscordMessage::render($webhook, [
            'event' => 'model:user.updated',
            'timestamp' => '2026-11-09T04:04:00+06:00',
            'data' => ['id' => 1, 'username' => 'tasfiq', 'changes' => ['last_seen']],
        ]);

        $container = $message['components'][0]['components'];
        $texts = $this->textDisplays($message['components']);

        $this->assertLessThanOrEqual(10, count($container));
        $this->assertLessThanOrEqual(4000, array_sum(array_map('mb_strlen', $texts)));
        foreach ($texts as $text) {
            $this->assertLessThanOrEqual(2000, mb_strlen($text));
        }
    }


    public function test_invalid_utf8_in_custom_text_is_replaced_before_rendering(): void
    {
        $webhook = Webhook::factory()->create([
            'type' => Webhook::TYPE_DISCORD,
            'message' => ['content' => "Bad \xC3\x28 text"],
        ]);
        $message = DiscordMessage::render($webhook, [
            'event' => 'model:user.updated',
            'timestamp' => '2026-11-09T04:04:00+06:00',
            'data' => ['id' => 1, 'username' => 'tasfiq', 'changes' => ['last_seen']],
        ]);

        $json = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $this->assertStringContainsString('Bad', $json);
        $this->assertStringContainsString("\u{FFFD}", $json);
    }


    public function test_discord_delivery_preserves_query_parameters_and_enables_components(): void
    {
        config()->set('webhooks.allow_private_endpoints', true);
        Http::fake(['*' => Http::response('', 204)]);

        $webhook = Webhook::factory()->create([
            'type' => Webhook::TYPE_DISCORD,
            'endpoint' => 'https://discord.com/api/webhooks/1/token?wait=true&thread_id=42&with_components=false',
        ]);

        app(WebhookDeliveryService::class)->send($webhook, [
            'event' => 'model:user.updated',
            'timestamp' => '2026-11-09T04:04:00+06:00',
            'data' => ['id' => 1, 'username' => 'tasfiq', 'changes' => ['last_seen']],
        ]);

        Http::assertSent(function (Request $request): bool {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            $this->assertSame('true', $query['with_components'] ?? null);
            $this->assertSame('true', $query['wait'] ?? null);
            $this->assertSame('42', $query['thread_id'] ?? null);
            $this->assertSame(32768, $request->data()['flags'] ?? null);
            $this->assertArrayNotHasKey('content', $request->data());
            $this->assertArrayNotHasKey('embeds', $request->data());

            return true;
        });
    }

    /** @return string[] */
    private function textDisplays(array $components): array
    {
        $texts = [];
        foreach ($components as $component) {
            if (($component['type'] ?? null) === 10) {
                $texts[] = $component['content'];
            }
            if (isset($component['components']) && is_array($component['components'])) {
                $texts = [...$texts, ...$this->textDisplays($component['components'])];
            }
        }

        return $texts;
    }
}
