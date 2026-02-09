<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class LanguageMiddleware
{
    /**
     * LanguageMiddleware constructor.
     */
    public function __construct(
        private Application $app,
        private \App\Contracts\Repository\SettingsRepositoryInterface $settings,
    ) {
    }

    /**
     * Handle an incoming request and set the user's preferred language.
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $locale = $request->user()?->language ?? $this->settings->get('settings::app:locale', config('app.locale', 'en'));

        $this->app->setLocale($locale);
        config(['app.locale' => $locale]);

        return $next($request);
    }
}
