<?php

namespace Tests\Integration\Http;

use App\Models\User;
use Illuminate\Testing\TestResponse;

class InitialApiResponsesTest extends HttpTestCase
{
    /**
     * @return array<string, string>
     */
    private function embedded(TestResponse $response): array
    {
        $html = $response->assertOk()->getContent();
        if (! preg_match('#<script type="application/json" id="reviactyl-initial-responses">(.*?)</script>#s', $html, $matches)) {
            return [];
        }

        return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
    }

    private function apiOnly(array $embedded): array
    {
        return array_filter($embedded, fn (string $uri) => str_starts_with($uri, '/api/'), ARRAY_FILTER_USE_KEY);
    }

    private function assertMatchesApi(User $user, array $embedded): void
    {
        foreach ($embedded as $uri => $body) {
            $this->assertSame($this->actingAs($user)->getJson($uri)->assertOk()->getContent(), $body, "Embedded response for {$uri} differs from the API.");
        }
    }

    public function test_dashboard_embeds_its_api_responses_exactly_as_the_api_returns_them(): void
    {
        [$user, $server] = $this->generateTestAccount();

        $embedded = $this->apiOnly($this->embedded($this->actingAs($user)->get('/?page=1')));

        $this->assertSame([
            '/api/client?include=category&page=1',
            '/api/client/account/categories',
            '/api/client/alerts?placement=dashboard',
            '/api/client/eggs',
            '/api/client/extensions',
            '/api/client/notifications?unread=0&page=1',
        ], array_keys($embedded));
        $this->assertSame($server->uuid, json_decode($embedded['/api/client?include=category&page=1'], true)['data'][0]['attributes']['uuid']);
        $this->assertMatchesApi($user, $embedded);
    }

    public function test_server_pages_embed_the_server_and_its_alerts(): void
    {
        [$user, $server] = $this->generateTestAccount();

        $embedded = $this->apiOnly($this->embedded($this->actingAs($user)->get("/server/{$server->uuidShort}/files")));

        $websocket = "/api/client/servers/{$server->uuid}/websocket";
        $this->assertSame([
            "/api/client/servers/{$server->uuidShort}?include=allocations,variables,category",
            '/api/client/alerts?placement=server&server='.$server->uuid,
            $websocket,
            '/api/client/extensions',
            '/api/client/notifications?unread=0&page=1',
        ], array_keys($embedded));
        // Tokens are signed per request, so compare its shape; everything else byte for byte.
        $this->assertNotEmpty(json_decode($embedded[$websocket], true)['data']['token']);
        unset($embedded[$websocket]);
        $this->assertMatchesApi($user, $embedded);
    }

    public function test_servers_the_user_cannot_access_are_not_embedded(): void
    {
        $user = User::factory()->create();
        $server = $this->createServerModel();

        $embedded = $this->embedded($this->actingAs($user)->get("/server/{$server->uuidShort}"));

        $this->assertArrayNotHasKey("/api/client/servers/{$server->uuidShort}?include=allocations,variables,category", $embedded);
        $this->assertStringNotContainsString($server->uuid, json_encode($embedded));
    }

    public function test_guests_get_translations_only_and_unrelated_pages_get_nothing(): void
    {
        $embedded = $this->embedded($this->get('/auth/login'));
        $this->assertSame(['/locales/locale.json?locale=en&namespace=strings%2Croutes%2Cauth%2Cdashboard%2Faccount%2Cserver%2Fusers'], array_keys($embedded));

        $this->assertSame([], $this->embedded($this->actingAs(User::factory()->create())->get('/status/abcdef12')));
    }

    public function test_translations_match_the_translation_endpoint_for_the_users_language(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $user->update(['language' => 'de']);

        $embedded = $this->embedded($this->actingAs($user)->get("/server/{$server->uuidShort}/files"));
        $uri = array_key_first($embedded);

        $this->assertStringStartsWith('/locales/locale.json?locale=de%2Ben&namespace=strings%2Croutes%2Cdashboard%2Faccount', $uri);
        $this->assertStringContainsString('server%2Ffiles', $uri);
        $this->assertSame(
            $this->getJson($uri)->assertOk()->json(),
            json_decode($embedded[$uri], true),
        );
    }

    public function test_automatic_language_uses_the_panel_locale(): void
    {
        [$user] = $this->generateTestAccount();
        $user->update(['language' => 'geo']);
        config()->set('app.locale', 'fr');

        $this->assertStringStartsWith('/locales/locale.json?locale=fr%2Ben&', array_key_first($this->embedded($this->actingAs($user)->get('/'))));
    }

    public function test_flash_messages_and_the_page_request_survive_the_embedded_requests(): void
    {
        [$user] = $this->generateTestAccount();

        $html = $this->actingAs($user)
            ->withSession(['error' => 'Something broke', '_flash' => ['old' => [], 'new' => ['error']]])
            ->get('/account?tab=security')
            ->assertOk()
            ->getContent();

        // The flash script renders right after the embedded responses, from the page's own session.
        $this->assertStringContainsString('error: "Something broke"', $html);
        $this->assertStringContainsString('reviactyl-initial-responses', $html);
    }

    public function test_page_data_returns_what_a_page_requests_in_one_response(): void
    {
        [$user, $server] = $this->generateTestAccount();

        $data = $this->actingAs($user)
            ->getJson('/api/client/page-data?path='.rawurlencode("/server/{$server->uuidShort}/files"))
            ->assertOk()
            ->assertJsonPath('object', 'page_data')
            ->json('data');

        $websocket = "/api/client/servers/{$server->uuid}/websocket";
        $this->assertArrayHasKey($websocket, $data);
        $this->assertNotEmpty(json_decode($data[$websocket], true)['data']['token']);
        unset($data[$websocket]);
        $this->assertNotEmpty(array_filter(array_keys($data), fn (string $uri) => str_starts_with($uri, '/locales/')));
        $this->assertMatchesApi($user, $this->apiOnly($data));
        $this->assertCount(4, $this->apiOnly($data));
    }

    public function test_page_data_validates_the_path_and_hides_other_servers(): void
    {
        $user = User::factory()->create();
        $other = $this->createServerModel();

        $this->actingAs($user)->getJson('/api/client/page-data')->assertStatus(422);
        $this->actingAs($user)->getJson('/api/client/page-data?path=https://evil.test/')->assertStatus(422);
        $data = $this->actingAs($user)->getJson('/api/client/page-data?path=/server/'.$other->uuidShort)->assertOk()->json('data');

        $this->assertStringNotContainsString($other->uuid, json_encode($data));
    }
}
