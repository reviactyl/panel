<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\DirectLogin;
use App\Http\Controllers\Controller;
use App\Models\SocialLogin;
use App\Models\User;
use App\Support\LoginDestination;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;
use RuntimeException;
use SocialiteProviders\Discord\Provider;
use Throwable;

class SocialLoginController extends Controller
{
    protected array $supportedProviders = ['google', 'github', 'discord'];

    public function redirect(string $provider)
    {
        if (! in_array($provider, $this->supportedProviders)) {
            return $this->redirectWithError('Provider not supported.');
        }

        try {
            if (! $this->configureDriver($provider)) {
                return $this->redirectWithError('Provider is not enabled or configured.');
            }

            return Socialite::driver($provider)->redirect();
        } catch (Throwable $exception) {
            return $this->handleProviderFailure($provider, $exception);
        }
    }

    public function callback(Request $request, string $provider)
    {
        if (! in_array($provider, $this->supportedProviders)) {
            return $this->redirectWithError('Provider not supported.');
        }

        try {
            if (! $this->configureDriver($provider)) {
                return $this->redirectWithError('Provider is not enabled or configured.');
            }

            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $exception) {
            return $this->handleProviderFailure($provider, $exception);
        }

        // User is already logged in (Linking Account)
        if (Auth::check()) {
            $currentUser = Auth::user();

            // Check if this social account is already linked to ANOTHER user
            $existingLink = SocialLogin::where('provider', $provider)
                ->where('provider_user_id', $socialUser->getId())
                ->first();

            if ($existingLink) {
                if ($existingLink->user_id === $currentUser->id) {
                    return redirect()->route('account')->with('success', 'Account already linked.');
                }

                return redirect()->route('account')->with('error', 'This social account is already linked to another user.');
            }

            // Link the account
            $providerToken = $socialUser instanceof SocialiteUser ? $socialUser->token : null;
            $providerRefreshToken = $socialUser instanceof SocialiteUser ? $socialUser->refreshToken : null;

            $currentUser->socialLogins()->create([
                'provider' => $provider,
                'provider_user_id' => $socialUser->getId(),
                'provider_token' => $providerToken,
                'provider_refresh_token' => $providerRefreshToken,
            ]);

            return redirect()->route('account')->with('success', 'Account linked successfully.');
        }

        // Guest User (Logging In)

        // Check if social login exists
        $socialLogin = SocialLogin::where('provider', $provider)
            ->where('provider_user_id', $socialUser->getId())
            ->first();

        if ($socialLogin && $socialLogin->user instanceof User) {
            Auth::login($socialLogin->user);

            $request->session()->regenerate();

            Event::dispatch(new DirectLogin($socialLogin->user, false));

            return redirect(LoginDestination::pull($request));
        }

        // If a user with the same email already exists, do NOT auto-link.
        // Auto-linking allows account takeover: an attacker could create a
        // social account using the victim's email and gain full access.
        // Instead, require the user to log in with their password first,
        // then manually link the social account from Account Settings.
        return redirect()->route('auth.login')->with('error', trans('auth.social.not_linked', ['provider' => ucfirst($provider)]));
    }

    private function handleProviderFailure(string $provider, Throwable $exception): RedirectResponse
    {
        if ($exception instanceof TransferException) {
            // Provider responses can contain credentials, so report only the status.
            $status = $exception instanceof RequestException ? $exception->getResponse()?->getStatusCode() : null;
            $details = $status === null ? 'before receiving a response' : 'with HTTP '.$status;
            report(new RuntimeException("OAuth request to {$provider} failed {$details}."));
        } elseif (! $exception instanceof InvalidStateException) {
            report($exception);
        }

        return $this->redirectWithError('Failed to authenticate with '.$provider.'.');
    }

    protected function redirectWithError(string $message): RedirectResponse
    {
        return redirect()->route(Auth::check() ? 'account' : 'auth.login')->with('error', $message);
    }

    protected function generateUsername(string $name): string
    {
        $base = Str::slug($name);
        if (empty($base)) {
            $base = 'user';
        }

        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }

    protected function configureDriver(string $provider): bool
    {
        $enabled = config('panel.auth.'.$provider.'_enabled');
        if (! $enabled) {
            return false;
        }

        $clientId = config('panel.auth.'.$provider.'_client_id');
        $clientSecret = config('panel.auth.'.$provider.'_client_secret');
        $redirectUri = route('auth.social.callback', ['provider' => $provider]);

        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }

        config(['services.'.$provider.'.client_id' => $clientId]);
        config(['services.'.$provider.'.client_secret' => $clientSecret]);
        config(['services.'.$provider.'.redirect' => $redirectUri]);

        $this->registerExtendedProvider($provider);

        return true;
    }

    protected function registerExtendedProvider(string $provider): void
    {
        $extendedProviders = [
            'discord' => Provider::class,
        ];

        if (! isset($extendedProviders[$provider])) {
            return;
        }

        $providerClass = $extendedProviders[$provider];

        app(Factory::class)
            ->extend($provider, function ($app) use ($provider, $providerClass) {
                $config = $app['config']['services.'.$provider];

                return app($providerClass, [
                    'request' => $app['request'],
                    'clientId' => $config['client_id'],
                    'clientSecret' => $config['client_secret'],
                    'redirectUrl' => $config['redirect'],
                ]);
            });
    }
}
