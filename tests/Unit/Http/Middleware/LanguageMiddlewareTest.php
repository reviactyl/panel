<?php

namespace Tests\Unit\Http\Middleware;

use App\Contracts\Repository\SettingsRepositoryInterface;
use App\Http\Middleware\LanguageMiddleware;
use App\Models\User;
use App\Services\Helpers\GeoIPService;
use App\Services\Helpers\GeoLocaleService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Mockery as m;
use Mockery\MockInterface;

class LanguageMiddlewareTest extends MiddlewareTestCase
{
    private MockInterface $appMock;

    private MockInterface $settingsMock;

    private MockInterface $geoIPMock;

    private GeoLocaleService $geoLocaleService;

    /**
     * Setup tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->appMock = m::mock(Application::class);
        $this->settingsMock = m::mock(SettingsRepositoryInterface::class);
        $this->geoIPMock = m::mock(GeoIPService::class);
        $this->geoLocaleService = new GeoLocaleService();
    }

    /**
     * Test that a language is defined via the middleware for guests, and confirm that geolocation is not used when disabled in settings.
     */
    public function test_language_is_set_for_guest_with_geolocate_disabled()
    {
        $this->request->shouldReceive('user')->withNoArgs()->andReturnNull();
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale', m::any())
            ->andReturn('en');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale:geolocate', false)
            ->andReturn(false);
        $this->appMock->shouldReceive('setLocale')->with('en')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that a language is defined via the middleware for a user.
     */
    public function test_language_is_set_with_authenticated_user()
    {
        $user = User::factory()->make(['language' => 'de']);

        $this->request->shouldReceive('user')->withNoArgs()->andReturn($user);
        $this->appMock->shouldReceive('setLocale')->with('de')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that an authenticated user's language preference takes precedence over geolocation settings.
     */
    public function test_authenticated_user_language_overrides_geolocate()
    {
        $user = User::factory()->make(['language' => 'fr']);

        $this->request->shouldReceive('user')->withNoArgs()->andReturn($user);
        $this->appMock->shouldReceive('setLocale')->with('fr')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that geolocation resolves a locale from the visitor's IP address when enabled in settings.
     */
    public function test_geolocate_resolves_locale_from_ip()
    {
        $this->request->shouldReceive('user')->withNoArgs()->andReturnNull();
        $this->request->shouldReceive('ip')->withNoArgs()->andReturn('8.8.8.8');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale', m::any())
            ->andReturn('en');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale:geolocate', false)
            ->andReturn(true);
        $this->geoIPMock->shouldReceive('getCountryInfo')
            ->with('8.8.8.8')
            ->andReturn(['country' => 'Germany', 'code' => 'DE']);
        $this->appMock->shouldReceive('setLocale')->with('de')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that geolocation falls back to the default locale when the IP address is from a local network.
     */
    public function test_geolocate_falls_back_to_default_locale_when_ip_is_local()
    {
        $this->request->shouldReceive('user')->withNoArgs()->andReturnNull();
        $this->request->shouldReceive('ip')->withNoArgs()->andReturn('192.168.1.1');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale', m::any())
            ->andReturn('en');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale:geolocate', false)
            ->andReturn(true);
        $this->geoIPMock->shouldReceive('getCountryInfo')
            ->with('192.168.1.1')
            ->andReturn(['country' => 'Local Network', 'code' => 'LOCAL']);
        $this->appMock->shouldReceive('setLocale')->with('en')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that geolocation falls back to the default locale when the API returns null for a given IP address.
     */
    public function test_geolocate_falls_back_to_default_locale_when_api_returns_null()
    {
        $this->request->shouldReceive('user')->withNoArgs()->andReturnNull();
        $this->request->shouldReceive('ip')->withNoArgs()->andReturn('8.8.8.8');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale', m::any())
            ->andReturn('fr');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale:geolocate', false)
            ->andReturn(true);
        $this->geoIPMock->shouldReceive('getCountryInfo')
            ->with('8.8.8.8')
            ->andReturn(null);
        $this->appMock->shouldReceive('setLocale')->with('fr')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that geolocation falls back to the default locale when the API returns an unmapped country code.
     */
    public function test_geolocate_falls_back_to_default_locale_for_unmapped_country()
    {
        $this->request->shouldReceive('user')->withNoArgs()->andReturnNull();
        $this->request->shouldReceive('ip')->withNoArgs()->andReturn('8.8.8.8');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale', m::any())
            ->andReturn('en');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale:geolocate', false)
            ->andReturn(true);
        $this->geoIPMock->shouldReceive('getCountryInfo')
            ->with('8.8.8.8')
            ->andReturn(['country' => 'Unknown Country', 'code' => 'XX']);
        $this->appMock->shouldReceive('setLocale')->with('en')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that geolocation falls back to the default locale when the IP address is null.
     */
    public function test_geolocate_falls_back_when_ip_is_null()
    {
        $this->request->shouldReceive('user')->withNoArgs()->andReturnNull();
        $this->request->shouldReceive('ip')->withNoArgs()->andReturn(null);
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale', m::any())
            ->andReturn('en');
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale:geolocate', false)
            ->andReturn(true);
        $this->appMock->shouldReceive('setLocale')->with('en')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that the middleware falls back to app locale when settings are unavailable.
     */
    public function test_it_falls_back_to_config_locale_when_settings_table_is_missing(): void
    {
        config(['app.locale' => 'en']);

        $this->request->shouldReceive('user')->withNoArgs()->andReturnNull();
        $this->settingsMock->shouldReceive('get')
            ->with('settings::app:locale', m::any())
            ->andThrow(new QueryException('sqlite', 'select * from "settings" where "key" = ? limit 1', [], new \Exception('no such table: settings')));
        $this->appMock->shouldReceive('setLocale')->with('en')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Return an instance of the middleware using mocked dependencies.
     */
    private function getMiddleware(): LanguageMiddleware
    {
        return new LanguageMiddleware(
            $this->appMock,
            $this->settingsMock,
            $this->geoIPMock,
            $this->geoLocaleService,
        );
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
