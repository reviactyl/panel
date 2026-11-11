<?php

namespace App\Http\Middleware;

use App\Exceptions\Http\TwoFactorAuthRequiredException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Prologue\Alerts\AlertsMessageBag;

class RequireTwoFactorAuthentication
{
    public const LEVEL_NONE = 0;

    public const LEVEL_ADMIN = 1;

    public const LEVEL_ALL = 2;

    /**
     * The route to redirect a user to enable 2FA.
     */
    protected string $redirectRoute = '/account';

    /**
     * RequireTwoFactorAuthentication constructor.
     */
    public function __construct(private AlertsMessageBag $alert) {}

    public static function requiresSetup(User $user): bool
    {
        if ($user->use_totp) {
            return false;
        }

        return match ((int) config('panel.auth.2fa_required')) {
            self::LEVEL_NONE => false,
            self::LEVEL_ADMIN => $user->isAdmin(),
            default => true,
        };
    }

    /**
     * Check the user state on the incoming request to determine if they should be allowed to
     * proceed or not. This checks if the Panel is configured to require 2FA on an account in
     * order to perform actions. If so, we check the level at which it is required (all users
     * or just admins) and then check if the user has enabled it for their account.
     *
     * @throws TwoFactorAuthRequiredException
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $user = $request->user();
        $uri = rtrim($request->getRequestUri(), '/').'/';
        $current = $request->route()->getName();

        if (! $user || Str::startsWith($uri, ['/auth/']) || Str::startsWith($current, ['auth.', 'account.'])) {
            return $next($request);
        }

        if (! self::requiresSetup($user)) {
            return $next($request);
        }

        // For API calls return an exception which gets rendered nicely in the API response.
        if ($request->isJson() || Str::startsWith($uri, '/api/')) {
            throw new TwoFactorAuthRequiredException();
        }

        $this->alert->danger(trans('auth.2fa_must_be_enabled'))->flash();

        return redirect()->to($this->redirectRoute);
    }
}
