<?php

namespace Tests\Integration\Http\Controllers\Auth;

use App\Http\Middleware\VerifyCaptcha;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Integration\Http\HttpTestCase;

class LoginDestinationTest extends HttpTestCase
{
    #[TestWith(['/admin/users?page=2', true])]
    #[TestWith(['/admin/users?page=2', false])]
    #[TestWith(['/account?tab=security', false])]
    public function test_login_returns_to_requested_page(string $destination, bool $admin): void
    {
        $this->withoutMiddleware(VerifyCaptcha::class);
        $user = User::factory()->create(['root_admin' => $admin, 'password' => bcrypt('password'), 'use_totp' => false]);

        $this->get($destination, ['Accept' => 'text/html'])
            ->assertRedirect('/auth/login')
            ->assertSessionHas('url.intended', $destination);

        $this->postJson('/auth/login', ['user' => $user->username, 'password' => 'wrong'])
            ->assertStatus(400)
            ->assertSessionHas('url.intended', $destination);

        $this->postJson('/auth/login', ['user' => $user->username, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('data.intended', $destination)
            ->assertSessionMissing('url.intended');

        $response = $this->get($destination, ['Accept' => 'text/html']);
        if (str_starts_with($destination, '/admin') && ! $admin) {
            $response->assertForbidden();
        } else {
            $response->assertOk();
        }
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_server_destination_keeps_server_authorization(bool $owner): void
    {
        $this->withoutMiddleware(VerifyCaptcha::class);
        $user = User::factory()->create(['password' => bcrypt('password'), 'use_totp' => false]);
        $server = $this->createServerModel($owner ? ['owner_id' => $user->id] : []);
        $destination = '/server/'.$server->uuidShort.'/files?directory=%2Flogs';
        $this->get($destination, ['Accept' => 'text/html'])->assertRedirect('/auth/login');
        $this->postJson('/auth/login', ['user' => $user->username, 'password' => 'password'])
            ->assertOk()->assertJsonPath('data.intended', $destination);
        $response = $this->getJson('/api/client/servers/'.$server->uuidShort);
        $owner ? $response->assertOk() : $response->assertNotFound();
    }

    public function test_social_login_returns_to_session_destination(): void
    {
        config(['panel.auth.github_enabled' => true, 'panel.auth.github_client_id' => 'test', 'panel.auth.github_client_secret' => 'test']);
        $user = User::factory()->create();
        $user->socialLogins()->create(['provider' => 'github', 'provider_user_id' => '123']);
        $socialUser = new \Laravel\Socialite\Two\User();
        $socialUser->id = '123';
        Socialite::shouldReceive('driver')->with('github')->andReturnSelf();
        Socialite::shouldReceive('user')->once()->andReturn($socialUser);
        $this->withSession(['url.intended' => '/account?tab=security'])
            ->get('/auth/login/github/callback', ['Accept' => 'text/html'])
            ->assertRedirect('/account?tab=security')->assertSessionMissing('url.intended');
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_non_admin_is_denied_without_login_redirect(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => false]))
            ->get('/admin', ['Accept' => 'text/html'])
            ->assertForbidden();
    }

    #[TestWith(['https://example.com'])]
    #[TestWith(['//example.com'])]
    #[TestWith(['/\\example.com'])]
    #[TestWith(['/%2fexample.com'])]
    #[TestWith(['/auth/login'])]
    #[TestWith(["/\n/example.com"])]
    public function test_unsafe_destinations_fall_back_to_home(string $destination): void
    {
        $this->withoutMiddleware(VerifyCaptcha::class);
        $user = User::factory()->create(['password' => bcrypt('password'), 'use_totp' => false]);
        $this->withSession(['url.intended' => $destination])
            ->postJson('/auth/login', ['user' => $user->username, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('data.intended', '/');
    }
}
