<?php

namespace App\Tests\Unit\Services\Helpers;

use App\Tests\TestCase;
use App\Services\Helpers\GeoLocaleService;

class GeoLocaleServiceTest extends TestCase
{
    private GeoLocaleService $service;
    private array $availableLocales;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new GeoLocaleService();
        $this->availableLocales = ['ar', 'de', 'en', 'es', 'fr', 'hi', 'id', 'kn', 'pt', 'sv', 'tr', 'zh'];
    }

    public function testItResolvesEnglishForUs()
    {
        $this->assertEquals('en', $this->service->resolveLocale('US', $this->availableLocales));
    }

    public function testItResolvesEnglishForGb()
    {
        $this->assertEquals('en', $this->service->resolveLocale('GB', $this->availableLocales));
    }

    public function testItResolvesGermanForDe()
    {
        $this->assertEquals('de', $this->service->resolveLocale('DE', $this->availableLocales));
    }

    public function testItResolvesGermanForAustria()
    {
        $this->assertEquals('de', $this->service->resolveLocale('AT', $this->availableLocales));
    }

    public function testItResolvesFrenchForFr()
    {
        $this->assertEquals('fr', $this->service->resolveLocale('FR', $this->availableLocales));
    }

    public function testItResolvesSpanishForEs()
    {
        $this->assertEquals('es', $this->service->resolveLocale('ES', $this->availableLocales));
    }

    public function testItResolvesSpanishForMexico()
    {
        $this->assertEquals('es', $this->service->resolveLocale('MX', $this->availableLocales));
    }

    public function testItResolvesPortugueseForBrazil()
    {
        $this->assertEquals('pt', $this->service->resolveLocale('BR', $this->availableLocales));
    }

    public function testItResolvesPortugueseForPortugal()
    {
        $this->assertEquals('pt', $this->service->resolveLocale('PT', $this->availableLocales));
    }

    public function testItResolvesArabicForSaudiArabia()
    {
        $this->assertEquals('ar', $this->service->resolveLocale('SA', $this->availableLocales));
    }

    public function testItResolvesArabicForEgypt()
    {
        $this->assertEquals('ar', $this->service->resolveLocale('EG', $this->availableLocales));
    }

    public function testItResolvesHindiForIndia()
    {
        $this->assertEquals('hi', $this->service->resolveLocale('IN', $this->availableLocales));
    }

    public function testItResolvesIndonesianForIndonesia()
    {
        $this->assertEquals('id', $this->service->resolveLocale('ID', $this->availableLocales));
    }

    public function testItResolvesSwedishForSweden()
    {
        $this->assertEquals('sv', $this->service->resolveLocale('SE', $this->availableLocales));
    }

    public function testItResolvesTurkishForTurkey()
    {
        $this->assertEquals('tr', $this->service->resolveLocale('TR', $this->availableLocales));
    }

    public function testItResolvesChineseForChina()
    {
        $this->assertEquals('zh', $this->service->resolveLocale('CN', $this->availableLocales));
    }

    public function testItResolvesChineseForTaiwan()
    {
        $this->assertEquals('zh', $this->service->resolveLocale('TW', $this->availableLocales));
    }

    public function testItReturnsNullForUnmappedCountry()
    {
        $this->assertNull($this->service->resolveLocale('XX', $this->availableLocales));
    }

    public function testItReturnsNullWhenMappedLocaleIsNotAvailable()
    {
        $limitedLocales = ['en', 'fr'];
        $this->assertNull($this->service->resolveLocale('DE', $limitedLocales));
    }

    public function testItIsCaseInsensitiveForCountryCodes()
    {
        $this->assertEquals('fr', $this->service->resolveLocale('fr', $this->availableLocales));
        $this->assertEquals('de', $this->service->resolveLocale('de', $this->availableLocales));
    }

    public function testItReturnsNullForEmptyAvailableLocales()
    {
        $this->assertNull($this->service->resolveLocale('US', []));
    }
}
