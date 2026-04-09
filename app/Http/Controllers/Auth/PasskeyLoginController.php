<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Exceptions\DisplayException;
use App\Facades\Activity;
use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;

class PasskeyLoginController extends AbstractLoginController
{
    /**
     * Return a WebAuthn assertion challenge for passkey login.
     */
    public function options(AssertionRequest $request): Responsable
    {
        $data = $request->validate([
            'user' => 'nullable|string|min:1|max:191',
        ]);

        try {
            $requireUsernameForPasskeyLogin = filter_var(
                app(SettingsRepositoryInterface::class)->get(
                    'settings::panel:auth:passkey_login_requires_username',
                    config('panel.auth.passkey_login_requires_username', false)
                ),
                FILTER_VALIDATE_BOOL
            );
        } catch (QueryException) {
            $requireUsernameForPasskeyLogin = (bool) config('panel.auth.passkey_login_requires_username', false);
        }

        if ($requireUsernameForPasskeyLogin && empty($data['user'])) {
            throw new DisplayException(trans('auth.passkey-username-required'));
        }

        if (! empty($data['user'])) {
            $field = $this->getField($data['user']);
            $user = User::query()->where($field, $data['user'])->first();

            // Return a generic message for unknown users and users without passkeys.
            if (! $user || ! $user->webAuthnCredentials()->whereNull('disabled_at')->exists()) {
                throw new DisplayException(trans('auth.passkey-no-credentials'));
            }

            return $request->secureLogin()->toVerify([
                $field => $data['user'],
            ]);
        }

        // Empty credentials enables discoverable credentials (username-less sign in).
        return $request->secureLogin()->toVerify(null);
    }

    /**
     * Complete passkey login and create a session.
     *
     * @throws DisplayException
     */
    public function login(Request $request): JsonResponse
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        $credentials = $request->validate([
            'id' => 'required|string',
            'rawId' => 'required|string',
            'response.authenticatorData' => 'required|string',
            'response.clientDataJSON' => 'required|string',
            'response.signature' => 'required|string',
            'response.userHandle' => 'sometimes|nullable|string',
            'type' => 'required|string|in:public-key',
        ]);

        if (! $this->auth->guard()->attempt($credentials, true)) {
            $this->sendFailedLoginResponse($request);
        }

        /** @var User $user */
        $user = $this->auth->guard()->user();

        if (! $user) {
            $this->sendFailedLoginResponse($request);
        }

        Activity::event('auth:passkey')->withRequestMetadata()->subject($user)->log();

        return $this->sendLoginResponse($user, $request);
    }
}
