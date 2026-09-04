<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Exceptions\DisplayException;
use App\Facades\Activity;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passkeys\Actions\GenerateVerificationOptions;
use Laravel\Passkeys\Actions\VerifyPasskey;
use Laravel\Passkeys\Exceptions\InvalidPasskeyException;
use Laravel\Passkeys\Http\Requests\PasskeyVerificationRequest;
use Laravel\Passkeys\Support\WebAuthn;

class PasskeyLoginController extends AbstractLoginController
{
    /**
     * Return a WebAuthn assertion challenge for passkey login.
     */
    public function options(Request $request, GenerateVerificationOptions $generate): JsonResponse
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

        $user = null;

        if (! empty($data['user'])) {
            $field = $this->getField($data['user']);
            $user = User::query()->where($field, $data['user'])->first();

            // Return a generic message for unknown users and users without passkeys.
            if (! $user || ! $user->hasPasskeysEnabled()) {
                throw new DisplayException(trans('auth.passkey-no-credentials'));
            }
        }

        $options = $generate($user);

        $request->session()->put('passkey.verification_options', WebAuthn::toJson($options));
        $request->session()->put('passkey.login_user_id', $user?->id);

        return new JsonResponse(WebAuthn::toBrowserArray($options));
    }

    /**
     * Complete passkey login and create a session.
     *
     * @throws DisplayException
     */
    public function store(PasskeyVerificationRequest $request, VerifyPasskey $verify): JsonResponse
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        $expectedUserId = $request->session()->pull('passkey.login_user_id');
        $expectedUser = is_numeric($expectedUserId) ? User::query()->find($expectedUserId) : null;

        try {
            $passkey = $verify(
                $request->credential(),
                $request->verificationOptions(),
                $expectedUser
            );
        } catch (InvalidPasskeyException) {
            $this->sendFailedLoginResponse($request);
        }

        $user = $passkey->user;

        if (! $user instanceof User) {
            $this->sendFailedLoginResponse($request);
        }

        Activity::event('auth:passkey')->withRequestMetadata()->subject($user)->log();

        if ($user->use_totp) {
            return $this->sendLoginCheckpointResponse($user, $request);
        }

        return $this->sendLoginResponse($user, $request);
    }
}
