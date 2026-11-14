<?php

namespace Tests\Integration\Services\Webhooks;

use App\Jobs\Webhooks\DeliverWebhook;
use App\Models\ActivityLog;
use App\Models\Location;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\Activity\ActivityLogService;
use App\Services\Webhooks\WebhookDeliveryService;
use App\Services\Webhooks\WebhookDispatcher;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\Integration\IntegrationTestCase;

class WebhookDeliveryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('webhooks.allow_private_endpoints', true);
        Webhook::forgetSubscriptions();
    }

    public function test_model_changes_are_queued_for_the_webhooks_following_them(): void
    {
        Bus::fake([DeliverWebhook::class]);

        $following = Webhook::factory()->create(['events' => ['model:location.created', 'model:location.updated']]);
        Webhook::factory()->create(['events' => ['model:location.created'], 'enabled' => false]);
        Webhook::factory()->create(['events' => ['model:node.created']]);

        $location = Location::factory()->create(['short' => 'ams']);

        Bus::assertDispatchedTimes(DeliverWebhook::class, 1);
        Bus::assertDispatched(DeliverWebhook::class, fn (DeliverWebhook $job) => $job->webhook === $following->id
            && $job->event === 'model:location.created'
            && $job->payload['data']['short'] === 'ams'
            && $job->payload['data']['id'] === $location->id);

        $location->touch();
        Bus::assertDispatchedTimes(DeliverWebhook::class, 1);

        $location->update(['long' => 'Amsterdam']);
        Bus::assertDispatched(DeliverWebhook::class, fn (DeliverWebhook $job) => $job->event === 'model:location.updated'
            && $job->payload['data']['changes'] === ['long']);

        $location->delete();
        Bus::assertDispatchedTimes(DeliverWebhook::class, 2);
    }

    public function test_secrets_never_leave_the_panel(): void
    {
        Bus::fake([DeliverWebhook::class]);
        Webhook::factory()->create(['events' => ['model:user.created', 'user:api-key.create']]);

        $user = User::factory()->create(['totp_secret' => 'hunter2']);

        Bus::assertDispatched(DeliverWebhook::class, function (DeliverWebhook $job) use ($user) {
            $this->assertSame($user->email, $job->payload['data']['email']);
            $this->assertArrayNotHasKey('password', $job->payload['data']);
            $this->assertArrayNotHasKey('totp_secret', $job->payload['data']);

            return true;
        });

        $activity = app(ActivityLogService::class)
            ->actor($user)
            ->subject($user)
            ->event('user:api-key.create')
            ->property(['identifier' => 'abc', 'token' => 'hunter2'])
            ->log();
        app(WebhookDispatcher::class)->activity($activity);

        Bus::assertDispatched(DeliverWebhook::class, fn (DeliverWebhook $job) => $job->event === 'user:api-key.create'
            && $job->payload['data']['properties'] === ['identifier' => 'abc']
            && $job->payload['data']['actor'] === ['type' => 'user', 'id' => $user->id, 'name' => $user->username]
            && $job->payload['data']['subjects'] === [['type' => 'user', 'id' => $user->id, 'name' => $user->username]]);
    }

    public function test_logged_activity_reaches_webhooks_with_its_subjects(): void
    {
        Bus::fake([DeliverWebhook::class]);
        // Activity events are faked for every integration test, this one needs the real thing.
        Event::swap((fn () => $this->dispatcher)->call(Event::getFacadeRoot()));

        Webhook::factory()->create(['events' => ['user:api-key.create']]);
        $user = User::factory()->create();

        app(ActivityLogService::class)->actor($user)->subject($user)->event('user:api-key.create')->log();
        app(ActivityLogService::class)->actor($user)->subject($user)->event('user:api-key.delete')->log();

        Bus::assertDispatchedTimes(DeliverWebhook::class, 1);
        Bus::assertDispatched(DeliverWebhook::class, fn (DeliverWebhook $job) => $job->event === 'user:api-key.create'
            && $job->payload['data']['subjects'] === [['type' => 'user', 'id' => $user->id, 'name' => $user->username]]);

        // A startup variable can be a password, so its values stay on the panel.
        Webhook::factory()->create(['events' => ['server:startup.edit']]);
        app(ActivityLogService::class)->actor($user)->event('server:startup.edit')
            ->property(['variable' => 'RCON_PASSWORD', 'old' => 'hunter1', 'new' => 'hunter2'])
            ->log();

        Bus::assertDispatched(DeliverWebhook::class, fn (DeliverWebhook $job) => $job->event === 'server:startup.edit'
            && $job->payload['data']['properties'] === ['variable' => 'RCON_PASSWORD']);
    }

    public function test_standard_webhook_is_signed_and_recorded(): void
    {
        Http::fake(['*' => Http::response('ok', 202)]);

        $webhook = Webhook::factory()->create([
            'secret' => 'shh',
            'headers' => ['Authorization' => 'Bearer abc', 'Host' => 'evil.test', "Bad\nName" => 'x'],
        ]);
        $payload = app(WebhookDispatcher::class)->sample();

        (new DeliverWebhook($webhook->id, $payload['event'], $payload, '11111111-1111-4111-8111-111111111111'))
            ->handle(app(WebhookDeliveryService::class));

        Http::assertSent(function (Request $request) use ($webhook, $payload) {
            $this->assertSame($webhook->endpoint, $request->url());
            $this->assertSame('POST', $request->method());
            $this->assertSame(['id' => '11111111-1111-4111-8111-111111111111', ...$payload], $request->data());
            $this->assertSame('Bearer abc', $request->header('Authorization')[0]);
            $this->assertNotSame('evil.test', $request->header('Host')[0] ?? null);
            $this->assertSame($payload['event'], $request->header('X-Webhook-Event')[0]);
            $this->assertSame('11111111-1111-4111-8111-111111111111', $request->header('X-Webhook-Delivery')[0]);
            $this->assertSame('sha256='.hash_hmac('sha256', $request->body(), 'shh'), $request->header('X-Webhook-Signature')[0]);

            return true;
        });

        $delivery = WebhookDelivery::query()->sole();
        $this->assertTrue($delivery->successful);
        $this->assertSame(202, $delivery->status_code);
        $this->assertSame(1, $delivery->attempts);
        $this->assertSame('ok', $delivery->response);
        $this->assertEquals($payload, $delivery->payload);
        $this->assertFalse($delivery->shouldRetry());
    }

    public function test_failed_delivery_counts_attempts_and_disabled_webhook_is_skipped(): void
    {
        Http::fake(['*' => Http::sequence()->push('down', 503)->push('gone', 404)]);

        $webhook = Webhook::factory()->create();
        $payload = app(WebhookDispatcher::class)->sample();
        $service = app(WebhookDeliveryService::class);

        $delivery = $service->send($webhook, $payload, '22222222-2222-4222-8222-222222222222');
        $this->assertFalse($delivery->successful);
        $this->assertTrue($delivery->shouldRetry());

        $delivery = $service->send($webhook, $payload, '22222222-2222-4222-8222-222222222222');
        $this->assertSame(2, $delivery->attempts);
        $this->assertSame(404, $delivery->status_code);
        $this->assertFalse($delivery->shouldRetry());
        $this->assertDatabaseCount('webhook_deliveries', 1);

        Http::fake(fn () => throw new \RuntimeException('https://hooks.example.com/secret-token broke'));
        $delivery = $service->send($webhook, $payload);
        $this->assertTrue($delivery->exists);
        $this->assertTrue($delivery->shouldRetry());
        $this->assertStringNotContainsString('secret-token', (string) $delivery->response);

        $webhook->update(['enabled' => false]);
        (new DeliverWebhook($webhook->id, $payload['event'], $payload))->handle($service);
        $this->assertDatabaseCount('webhook_deliveries', 2);
    }

    public function test_private_endpoints_are_refused_unless_allowed(): void
    {
        Http::fake();
        config()->set('webhooks.allow_private_endpoints', false);

        foreach (['http://127.0.0.1/hook', 'http://169.254.169.254/latest', 'http://[::ffff:10.0.0.1]/hook', 'http://192.168.1.10:8080/'] as $endpoint) {
            $delivery = app(WebhookDeliveryService::class)->send(
                Webhook::factory()->create(['endpoint' => $endpoint]),
                app(WebhookDispatcher::class)->sample(),
            );

            $this->assertFalse($delivery->successful, $endpoint);
            $this->assertNull($delivery->status_code);
            $this->assertStringContainsString('private or reserved', (string) $delivery->response);
        }

        Http::assertNothingSent();
    }

    public function test_discord_webhook_posts_a_message_built_from_its_template(): void
    {
        Http::fake(['*' => Http::response('', 204)]);

        $webhook = Webhook::factory()->create([
            'type' => Webhook::TYPE_DISCORD,
            'endpoint' => 'https://discord.com/api/webhooks/1/token',
            'secret' => 'ignored',
            'message' => [
                'content' => '{{data.name}} says "hi" {{nope}}',
                'title' => '{{label}}',
                'description' => '{{summary}}',
                'color' => '#ff0000',
            ],
        ]);

        app(WebhookDeliveryService::class)->send($webhook, app(WebhookDispatcher::class)->sample());

        Http::assertSent(function (Request $request) {
            $this->assertSame('Example Server says "hi" {{nope}}', $request['content']);
            $this->assertSame('Server created', $request['embeds'][0]['title']);
            $this->assertSame('Server created: Example Server', $request['embeds'][0]['description']);
            $this->assertSame(0xFF0000, $request['embeds'][0]['color']);
            $this->assertSame(['parse' => []], $request['allowed_mentions']);
            $this->assertArrayNotHasKey('data', $request->data());
            $this->assertEmpty($request->header('X-Webhook-Signature'));

            return true;
        });

        $this->assertSame(Webhook::TYPE_DISCORD, Webhook::typeForEndpoint('https://canary.discord.com/api/webhooks/1/abc'));
        $this->assertSame(Webhook::TYPE_STANDARD, Webhook::typeForEndpoint('https://discord.com.evil.test/api/webhooks/1/abc'));
    }

    public function test_old_deliveries_are_pruned(): void
    {
        $webhook = Webhook::factory()->create();
        foreach ([40, 5] as $index => $age) {
            $delivery = new WebhookDelivery(['uuid' => "3333333$index-3333-4333-8333-333333333333", 'webhook_id' => $webhook->id, 'event' => 'auth:success', 'payload' => []]);
            $delivery->save();
            WebhookDelivery::query()->whereKey($delivery->id)->update(['created_at' => Carbon::now()->subDays($age)]);
        }

        $this->artisan('model:prune', ['--model' => [WebhookDelivery::class]]);

        $this->assertSame(5, (int) round(WebhookDelivery::query()->sole()->created_at->diffInDays(Carbon::now())));
        $this->assertSame(0, ActivityLog::query()->count());
    }
}
