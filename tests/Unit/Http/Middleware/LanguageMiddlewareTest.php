<?php

namespace App\Tests\Unit\Http\Middleware;

use Mockery as m;
use App\Models\User;
use Mockery\MockInterface;
use App\Services\Helpers\GeoIPService;
use Illuminate\Foundation\Application;
use App\Services\Helpers\GeoLocaleService;
use App\Http\Middleware\LanguageMiddleware;
use App\Contracts\Repository\SettingsRepositoryInterface;

class LanguageMiddlewareTest extends MiddlewareTestCase
{
    private MockInterface $appMock;
    private MockInterface $settingsMock;
    private MockInterface $geoIPMock;
    private GeoLocaleService $geoLocaleService;

    /**
     * Setup tests.
     */
    public function setUp(): void
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
    public function testLanguageIsSetForGuestWithGeolocateDisabled()
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
    public function testLanguageIsSetWithAuthenticatedUser()
    {
        $user = User::factory()->make(['language' => 'de']);

        $this->request->shouldReceive('user')->withNoArgs()->andReturn($user);
        $this->appMock->shouldReceive('setLocale')->with('de')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that an authenticated user's language preference takes precedence over geolocation settings.
     */
    public function testAuthenticatedUserLanguageOverridesGeolocate()
    {
        $user = User::factory()->make(['language' => 'fr']);

        $this->request->shouldReceive('user')->withNoArgs()->andReturn($user);
        $this->appMock->shouldReceive('setLocale')->with('fr')->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that geolocation resolves a locale from the visitor's IP address when enabled in settings.
     */
    public function testGeolocateResolvesLocaleFromIp()
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
    public function testGeolocateFallsBackToDefaultLocaleWhenIpIsLocal()
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
    public function testGeolocateFallsBackToDefaultLocaleWhenApiReturnsNull()
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
    public function testGeolocateFallsBackToDefaultLocaleForUnmappedCountry()
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
    public function testGeolocateFallsBackWhenIpIsNull()
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

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }
}
