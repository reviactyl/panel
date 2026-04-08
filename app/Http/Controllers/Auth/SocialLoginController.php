<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialLogin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class SocialLoginController extends Controller
{
    protected array $supportedProviders = ['google', 'github', 'discord'];

    public function redirect(string $provider)
    {
        if (! in_array($provider, $this->supportedProviders)) {
            return redirect()->route('auth.login')->with('error', 'Provider not supported.');
        }

        if (! $this->configureDriver($provider)) {
            return redirect()->route('auth.login')->with('error', 'Provider is not enabled or configured.');
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        if (! in_array($provider, $this->supportedProviders)) {
            return redirect()->route('auth.login')->with('error', 'Provider not supported.');
        }

        if (! $this->configureDriver($provider)) {
            return redirect()->route('auth.login')->with('error', 'Provider is not enabled or configured.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('auth.login')->with('error', 'Failed to authenticate with '.$provider.'.');
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

            return redirect('/');
        }

        // If a user with the same email already exists, do NOT auto-link.
        // Auto-linking allows account takeover: an attacker could create a
        // social account using the victim's email and gain full access.
        // Instead, require the user to log in with their password first,
        // then manually link the social account from Account Settings.
        return redirect()->route('auth.login')->with('error', trans('auth.social.not_linked', ['provider' => ucfirst($provider)]));
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

        return true;
    }
}
