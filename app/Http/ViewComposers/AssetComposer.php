<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Services\Helpers\AssetHashService;

class AssetComposer
{
    /**
     * AssetComposer constructor.
     */
    public function __construct(private AssetHashService $assetHashService)
    {
    }

    /**
     * Provide access to the asset service in the views.
     */
    public function compose(View $view): void
    {
        $view->with('asset', $this->assetHashService);
        $view->with('siteConfiguration', [
            'name' => config('app.name') ?? 'Reviactyl',
            'logo' => config('app.logo') ?? '/reviactyl/logo.png',
            'icon' => config('app.icon') ?? '/favicons/favicon.ico',
            'locale' => config('app.locale') ?? 'en',
            'pwa' => config('app.pwa', false),
            'debug' => config('app.debug', false),
            'avatar' => config('app.avatar') ?? 'gravatar',
            'captcha' => [
                'provider' => config('captcha.provider', 'disable'),
                'recaptcha' => [
                    'siteKey' => config('captcha.recaptcha.website_key') ?? '',
                ],
                'turnstile' => [
                    'siteKey' => config('captcha.turnstile.site_key') ?? '',
                ],
            ],
        ]);
    }
}