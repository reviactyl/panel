<?php

namespace App\Http\Middleware;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Services\Helpers\GeoIPService;
use App\Services\Helpers\GeoLocaleService;
use App\Traits\Helpers\AvailableLanguages;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

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
    ) {}

    public function handle(Request $request, \Closure $next): mixed
    {
        $user = $request->user();
        $fallbackLocale = config('app.locale', 'en');

        try {
            if ($user !== null) {
                if ($user->language === 'geo') {
                    $defaultLocale = $this->settings->get('settings::app:locale', $fallbackLocale);
                    $locale = $this->resolveGeoLocale($request, $defaultLocale);
                } else {
                    $locale = $user->language;
                }
            } else {
                $defaultLocale = $this->settings->get('settings::app:locale', $fallbackLocale);
                $geolocateEnabled = (bool) $this->settings->get('settings::app:locale:geolocate', false);

                if ($geolocateEnabled) {
                    $locale = $this->resolveGeoLocale($request, $defaultLocale);
                } else {
                    $locale = $defaultLocale;
                }
            }
        } catch (QueryException $exception) {
            // During fresh installs the settings table is not present yet.
            $locale = $fallbackLocale;
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
