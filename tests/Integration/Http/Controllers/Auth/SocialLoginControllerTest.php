<?php

namespace Tests\Integration\Http\Controllers\Auth;

use App\Events\Auth\DirectLogin;
use App\Models\SocialLogin;
use App\Models\User;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\Integration\Http\HttpTestCase;

class SocialLoginControllerTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([DirectLogin::class]);
        config(['panel.auth.google_enabled' => true, 'panel.auth.google_client_id' => 'test-client', 'panel.auth.google_client_secret' => 'test-secret']);
    }

    public static function unavailableProviders(): array
    {
        $cases = [];
        foreach ([false, true] as $authenticated) {
            foreach (['auth.social.redirect', 'auth.social.callback'] as $route) {
                foreach (['unsupported', 'disabled', 'unconfigured'] as $failure) {
                    $cases[] = [$authenticated, $route, $failure];
                }
            }
        }

        return $cases;
    }

    #[DataProvider('unavailableProviders')]
    public function test_unavailable_provider_errors_preserve_authentication_context(bool $authenticated, string $route, string $failure): void
    {
        if ($authenticated) {
            $this->actingAs(User::factory()->create());
        }

        config(['panel.auth.google_enabled' => $failure !== 'disabled']);
        if ($failure === 'unconfigured') {
            config(['panel.auth.google_client_secret' => null]);
        }

        Socialite::shouldReceive('driver')->never();
        $this->get(route($route, ['provider' => $failure === 'unsupported' ? 'unknown' : 'google']))
            ->assertRedirect(route($authenticated ? 'account' : 'auth.login'))
            ->assertSessionHas('error', $failure === 'unsupported' ? 'Provider not supported.' : 'Provider is not enabled or configured.');

        Event::assertNotDispatched(DirectLogin::class);
    }

    public static function providerFailures(): array
    {
        $cases = [];
        foreach ([false, true] as $authenticated) {
            foreach (['auth.social.redirect', 'auth.social.callback'] as $route) {
                foreach (['cancelled', 'response', 'error'] as $failure) {
                    $cases[] = [$authenticated, $route, $failure];
                }
            }
        }

        return $cases;
    }

    #[DataProvider('providerFailures')]
    public function test_provider_failures_preserve_authentication_context(bool $authenticated, string $route, string $failure): void
    {
        Exceptions::fake();
        if ($authenticated) {
            $this->actingAs(User::factory()->create());
        }

        $exception = match ($failure) {
            'cancelled' => new InvalidStateException(),
            'response' => new RequestException('Private provider response details', new Request('POST', 'https://oauth.example.test/token'), new Response(503)),
            'error' => new \Error('Synthetic provider error'),
        };
        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive($route === 'auth.social.redirect' ? 'redirect' : 'user')->once()->andThrow($exception);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);

        $this->get(route($route, ['provider' => 'google', 'error' => 'access_denied']))
            ->assertRedirect(route($authenticated ? 'account' : 'auth.login'))
            ->assertSessionHas('error', 'Failed to authenticate with google.');

        $this->assertDatabaseCount('social_logins', 0);
        Event::assertNotDispatched(DirectLogin::class);

        match ($failure) {
            'cancelled' => Exceptions::assertNothingReported(),
            'response' => Exceptions::assertReported(fn (\RuntimeException $reported) => $reported->getMessage() === 'OAuth request to google failed with HTTP 503.'),
            'error' => Exceptions::assertReported(fn (\Error $reported) => $reported === $exception),
        };
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_enabled_provider_redirects_to_authorization(bool $authenticated): void
    {
        if ($authenticated) {
            $this->actingAs(User::factory()->create());
        }

        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('redirect')->once()->andReturn(redirect('https://oauth.example.test/authorize'));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);

        $this->get(route('auth.social.redirect', ['provider' => 'google']))->assertRedirect('https://oauth.example.test/authorize');
        Event::assertNotDispatched(DirectLogin::class);
    }

    public function test_guest_login_dispatches_direct_login_after_authentication(): void
    {
        $user = User::factory()->create();
        $user->socialLogins()->create(['provider' => 'google', 'provider_user_id' => 'provider-user']);
        $this->mockSocialUser();
        $this->withSession(['url.intended' => '/account/api']);
        $sessionId = session()->getId();

        $this->get(route('auth.social.callback', ['provider' => 'google']))
            ->assertRedirect('/account/api')
            ->assertSessionMissing('url.intended');

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($sessionId, session()->getId());
        Event::assertDispatchedTimes(DirectLogin::class, 1);
        Event::assertDispatched(fn (DirectLogin $event) => $event->user->is($user) && ! $event->remember);
    }

    public function test_unlinked_guest_is_not_authenticated_or_automatically_linked(): void
    {
        User::factory()->create(['email' => 'oauth@example.test']);
        $this->mockSocialUser();

        $this->get(route('auth.social.callback', ['provider' => 'google']))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error', trans('auth.social.not_linked', ['provider' => 'Google']));

        $this->assertGuest();
        $this->assertDatabaseCount('social_logins', 0);
        Event::assertNotDispatched(DirectLogin::class);
    }

    #[TestWith(['new'])]
    #[TestWith(['same'])]
    #[TestWith(['other'])]
    public function test_authenticated_linking_does_not_dispatch_a_login(string $existing): void
    {
        $user = User::factory()->create();
        if ($existing !== 'new') {
            ($existing === 'same' ? $user : User::factory()->create())->socialLogins()->create(['provider' => 'google', 'provider_user_id' => 'provider-user']);
        }
        $this->mockSocialUser();

        $this->actingAs($user)->get(route('auth.social.callback', ['provider' => 'google']))
            ->assertRedirect(route('account'))
            ->assertSessionHas($existing === 'other' ? 'error' : 'success');

        $link = SocialLogin::where('provider_user_id', 'provider-user')->sole();
        $this->assertSame($existing !== 'other', $link->user_id === $user->id);
        $this->assertAuthenticatedAs($user);
        Event::assertNotDispatched(DirectLogin::class);
    }

    private function mockSocialUser(): void
    {
        $socialUser = (new SocialiteUser())->map(['id' => 'provider-user', 'email' => 'oauth@example.test']);
        $driver = Mockery::mock(Provider::class);
        $driver->shouldReceive('user')->once()->andReturn($socialUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);
    }
}
