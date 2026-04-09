<?php

namespace Tests\Integration\Api\Client;

use App\Models\User;
use Illuminate\Http\Response;

class PasskeyControllerTest extends ClientApiIntegrationTestCase
{
    public function test_passkey_registration_options_require_valid_password(): void
    {
        $user = User::factory()->create();

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
            ]);
    }

    public function test_passkeys_are_listed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $user->webAuthnCredentials()->forceCreate([
            'id' => 'cred_1',
            'user_id' => $user->uuid,
            'alias' => 'Workstation',
            'counter' => 1,
            'rp_id' => 'panel.test',
            'origin' => 'https://panel.test',
            'transports' => ['internal'],
            'aaguid' => null,
            'public_key' => 'public-key',
            'attestation_format' => 'none',
            'certificates' => [],
        ]);

        $this->actingAs($user)
            ->getJson('/api/client/account/passkeys')
            ->assertOk()
            ->assertJsonPath('data.0.id', 'cred_1')
            ->assertJsonPath('data.0.name', 'Workstation')
            ->assertJsonPath('data.0.origin', 'https://panel.test');
    }

    public function test_passkey_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $user->webAuthnCredentials()->forceCreate([
            'id' => 'cred_delete_me',
            'user_id' => $user->uuid,
            'alias' => 'Temporary Key',
            'counter' => 1,
            'rp_id' => 'panel.test',
            'origin' => 'https://panel.test',
            'transports' => ['internal'],
            'aaguid' => null,
            'public_key' => 'public-key',
            'attestation_format' => 'none',
            'certificates' => [],
        ]);

        $this->actingAs($user)
            ->postJson('/api/client/account/passkeys/remove', [
                'id' => 'cred_delete_me',
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => 'cred_delete_me',
        ]);
    }

    public function test_passkey_can_be_deleted_without_password(): void
    {
        $user = User::factory()->create();

        $user->webAuthnCredentials()->forceCreate([
            'id' => 'cred_cannot_delete',
            'user_id' => $user->uuid,
            'alias' => 'Protected Key',
            'counter' => 1,
            'rp_id' => 'panel.test',
            'origin' => 'https://panel.test',
            'transports' => ['internal'],
            'aaguid' => null,
            'public_key' => 'public-key',
            'attestation_format' => 'none',
            'certificates' => [],
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/client/account/passkeys/cred_cannot_delete')
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => 'cred_cannot_delete',
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
