<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Services\Helpers\GeoIPService;
use Illuminate\Foundation\Application;
use App\Services\Helpers\GeoLocaleService;
use App\Traits\Helpers\AvailableLanguages;
use App\Contracts\Repository\SettingsRepositoryInterface;

class LanguageMiddleware
{
    /**
     * LanguageMiddleware constructor.
     */
    use AvailableLanguages;

    public function __construct(
        private Application $app,
        private SettingsRepositoryInterface $settings,
        private GeoIPService $geoIP,
        private GeoLocaleService $geoLocale,
    ) {
    }

    public function handle(Request $request, \Closure $next): mixed
    {
        $user = $request->user();

        if ($user !== null) {
            if ($user->language === 'geo') {
                $defaultLocale = $this->settings->get('settings::app:locale', config('app.locale', 'en'));
                $locale = $this->resolveGeoLocale($request, $defaultLocale);
            } else {
                $locale = $user->language;
            }
        } else {
            $defaultLocale = $this->settings->get('settings::app:locale', config('app.locale', 'en'));
            $geolocateEnabled = (bool) $this->settings->get('settings::app:locale:geolocate', false);

            if ($geolocateEnabled) {
                $locale = $this->resolveGeoLocale($request, $defaultLocale);
            } else {
                $locale = $defaultLocale;
            }
        }

        $this->app->setLocale($locale);
        config(['app.locale' => $locale]);

        return $next($request);
    }

    private function resolveGeoLocale(Request $request, string $fallback): string
    {
        $ip = $request->ip();

        if ($ip === null) {
            return $fallback;
        }

        $countryInfo = $this->geoIP->getCountryInfo($ip);

        if ($countryInfo === null || $countryInfo['code'] === 'LOCAL') {
            return $fallback;
        }

        $availableLocales = array_keys($this->getAvailableLanguages());
        $resolved = $this->geoLocale->resolveLocale($countryInfo['code'], $availableLocales);

        return $resolved ?? $fallback;
    }
}
