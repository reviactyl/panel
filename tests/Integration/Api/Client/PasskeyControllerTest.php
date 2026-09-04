<?php

namespace Tests\Integration\Api\Client;

use App\Models\User;
use Illuminate\Http\Response;

class PasskeyControllerTest extends ClientApiIntegrationTestCase
{
    public function test_passkey_registration_options_require_valid_password(): void
    {
        $user = User::factory()->create();

        $this->withHeader('Origin', config('app.url'));

        $this->actingAs($user)
            ->postJson('/api/client/account/passkeys/register/options', [
                'password' => 'invalid',
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.0.detail', 'The password provided was not valid.');

        $this->actingAs($user)
            ->postJson('/api/client/account/passkeys/register/options', [
                'password' => 'password',
                'name' => 'Laptop',
            ])
            ->assertOk()
            ->assertJsonStructure([
                'challenge',
                'rp',
            ])
            ->assertSessionHas('passkey.registration_options');
    }

    public function test_passkeys_are_listed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $passkey = $user->passkeys()->create([
            'name' => 'Workstation',
            'credential_id' => 'Y3JlZF8x',
            'credential' => [],
        ]);

        $this->actingAs($user)
            ->getJson('/api/client/account/passkeys')
            ->assertOk()
            ->assertJsonPath('data.0.id', (string) $passkey->id)
            ->assertJsonPath('data.0.name', 'Workstation')
            ->assertJsonPath('data.0.authenticator', null);
    }

    public function test_passkey_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $passkey = $user->passkeys()->create([
            'name' => 'Temporary Key',
            'credential_id' => 'Y3JlZF9kZWxldGVfbWU',
            'credential' => [],
        ]);

        $this->actingAs($user)
            ->postJson('/api/client/account/passkeys/remove', [
                'id' => (string) $passkey->id,
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('passkeys', [
            'id' => $passkey->id,
        ]);
    }

    public function test_passkey_can_be_deleted_without_password(): void
    {
        $user = User::factory()->create();

        $passkey = $user->passkeys()->create([
            'name' => 'Protected Key',
            'credential_id' => 'Y3JlZF9jYW5ub3RfZGVsZXRl',
            'credential' => [],
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/client/account/passkeys/'.$passkey->id)
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('passkeys', [
            'id' => $passkey->id,
        ]);
    }

    public function test_passkey_remove_endpoint_requires_identifier(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/client/account/passkeys/remove')
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.0.detail', 'A passkey id must be provided.');
    }
}
