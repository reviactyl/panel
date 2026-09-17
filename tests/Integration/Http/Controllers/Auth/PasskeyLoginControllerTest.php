<?php

namespace Tests\Integration\Http\Controllers\Auth;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Models\User;
use Tests\Integration\Http\HttpTestCase;

class PasskeyLoginControllerTest extends HttpTestCase
{
    public function test_options_endpoint_returns_challenge_for_known_user(): void
    {
        $user = User::factory()->create();

        $user->passkeys()->create([
            'name' => 'Phone',
            'credential_id' => 'Y3JlZF9sb2dpbl8x',
            'credential' => [],
        ]);

        $this->postJson(route('auth.passkey-options'), [
            'user' => $user->email,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'challenge',
            ])
            ->assertSessionHas('passkey.verification_options')
            ->assertSessionHas('passkey.login_user_id', $user->id);
    }

    public function test_login_endpoint_requires_assertion_payload(): void
    {
        $this->postJson(route('auth.passkey-login'))
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.meta.source_field', 'credential');
    }

    public function test_options_endpoint_returns_generic_error_when_user_has_no_passkeys(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('auth.passkey-options'), [
            'user' => $user->email,
        ])
            ->assertBadRequest()
            ->assertJsonPath('errors.0.detail', trans('auth.passkey-no-credentials'));
    }

    public function test_options_endpoint_requires_username_when_toggle_is_enabled(): void
    {
        app(SettingsRepositoryInterface::class)->set('settings::panel:auth:passkey_login_requires_username', 'true');

        $this->postJson(route('auth.passkey-options'))
            ->assertBadRequest()
            ->assertJsonPath('errors.0.detail', trans('auth.passkey-username-required'));
    }
}
