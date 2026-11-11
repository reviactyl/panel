<?php

namespace Tests\Integration\Http\Controllers\Auth;

use App\Http\Middleware\VerifyCaptcha;
use App\Models\User;
use Tests\Integration\Http\HttpTestCase;

class LogoutControllerTest extends HttpTestCase
{
    public function test_repeated_login_logout_cycles_return_each_regenerated_token(): void
    {
        $this->withoutMiddleware(VerifyCaptcha::class);
        $user = User::factory()->create(['password' => bcrypt('password'), 'use_totp' => false]);
        $lastToken = 'initial-token';
        $this->withSession(['_token' => $lastToken]);
        for ($cycle = 0; $cycle < 3; $cycle++) {
            $login = $this->postJson('/auth/login', ['user' => $user->username, 'password' => 'password'])->assertOk();
            $token = $login->json('data.csrf_token');
            $this->assertIsString($token);
            $this->assertNotSame($lastToken, $token);
            $login->assertSessionHas('_token', $token);
            $logout = $this->postJson('/auth/logout', ['_token' => $token])->assertOk();
            $this->assertGuest();
            $lastToken = $logout->json('data.csrf_token');
            $this->assertNotSame($token, $lastToken);
            $logout->assertSessionHas('_token', $lastToken);
        }
    }

    public function test_json_logout_invalidates_the_session_and_returns_the_rotated_csrf_token(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->withSession(['private-value' => 'private', '_token' => 'old-token'])
            ->postJson('/auth/logout')
            ->assertOk()
            ->assertSessionMissing('private-value');

        $this->assertGuest();
        $token = $response->json('data.csrf_token');
        $this->assertIsString($token);
        $this->assertNotSame('old-token', $token);
        $response->assertSessionHas('_token', $token);
        $this->getJson('/api/client')->assertUnauthorized();
    }

    public function test_document_logout_keeps_the_existing_redirect(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/auth/logout', [], ['Accept' => 'text/html'])
            ->assertRedirect('/');
        $this->assertGuest();
    }
}
