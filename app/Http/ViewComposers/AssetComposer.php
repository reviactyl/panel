<?php

namespace App\Http\ViewComposers;

use App\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class AssetComposer
{
    public function __construct(private SettingsRepositoryInterface $settings) {}

    /**
     * Provide access to the asset service in the views.
     */
    public function compose(View $view): void
    {
        try {
            $passkeyLoginRequiresUsername = $this->settings->get(
                'settings::panel:auth:passkey_login_requires_username',
                config('panel.auth.passkey_login_requires_username', false)
            );
        } catch (QueryException) {
            $passkeyLoginRequiresUsername = config('panel.auth.passkey_login_requires_username', false);
        }

        $view->with('siteConfiguration', [
            'name' => config('app.name') ?? 'Reviactyl',
            'logo' => config('app.logo') ?? '/reviactyl/logo.png',
            'icon' => config('app.icon') ?? '/favicons/favicon.ico',
            'locale' => config('app.locale') ?? 'en',
            'pwa' => config('app.pwa', false),
            'debug' => config('app.debug', false),
            'avatar' => config('app.avatar') ?? 'gravatar',
            'registrationEnabled' => config('panel.auth.registration_enabled', true),
            'captcha' => [
                'provider' => config('captcha.provider', 'disable'),
                'recaptcha' => [
                    'siteKey' => config('captcha.recaptcha.website_key') ?? '',
                ],
                'turnstile' => [
                    'siteKey' => config('captcha.turnstile.site_key') ?? '',
                ],
            ],
            'passkeys' => [
                'loginRequiresUsername' => filter_var($passkeyLoginRequiresUsername, FILTER_VALIDATE_BOOL),
            ],
        ]);
    }
}
