<?php

namespace App\Tests\Unit\Services\Helpers;

use App\Services\Helpers\GeoIPService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Psr\Log\LoggerInterface;
use App\Tests\TestCase;
use Mockery;

class GeoIPServiceTest extends TestCase
{
    private GeoIPService $service;
    private $logger;

    public function setUp(): void
    {
        parent::setUp();
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->service = new GeoIPService($this->logger);
        Cache::flush();
    }

    public function test_it_returns_local_network_for_loopback_ipv4()
    {
        $info = $this->service->getCountryInfo('127.0.0.1');

        $this->assertEquals(__('strings.local_network'), $info['country']);
        $this->assertEquals('LOCAL', $info['code']);
    }

    public function test_it_returns_local_network_for_loopback_ipv6()
    {
        $info = $this->service->getCountryInfo('::1');

        $this->assertEquals(__('strings.local_network'), $info['country']);
        $this->assertEquals('LOCAL', $info['code']);
    }

    public function test_it_returns_local_network_for_private_ipv4_ranges()
    {
        $privateIps = ['10.0.0.1', '172.16.0.1', '192.168.1.1']; // Private IP Ranges for Class A, B, and C (RFC1918)

        foreach ($privateIps as $ip) {
            $info = $this->service->getCountryInfo($ip);
            $this->assertEquals(__('strings.local_network'), $info['country'], "Failed for $ip");
            $this->assertEquals('LOCAL', $info['code']);
        }
    }

    public function test_it_returns_local_network_for_private_ipv6_ranges()
    {
        $privateIps = ['fc00::1', 'fd00::1']; // Unique Local Address (ULA) range for IPv6 (RFC4193 [Proposed Standard, but sometimes treated as private])

        foreach ($privateIps as $ip) {
            $info = $this->service->getCountryInfo($ip);
            $this->assertEquals(__('strings.local_network'), $info['country'], "Failed for $ip");
            $this->assertEquals('LOCAL', $info['code']);
        }
    }

    public function test_it_resolves_public_ip_and_caches_result()
    {
        $ip = '8.8.8.8';
        Http::fake([
            'http://ip-api.com/json/*' => Http::response([
                'status' => 'success',
                'country' => 'United States',
                'countryCode' => 'US',
            ]),
        ]);

        $info = $this->service->getCountryInfo($ip);

        $this->assertEquals('United States', $info['country']);
        $this->assertEquals('US', $info['code']);
        
        // Second call should be from cache, no new HTTP request
        $info2 = $this->service->getCountryInfo($ip);
        $this->assertEquals($info, $info2);
        
        Http::assertSentCount(1);
    }

    public function test_it_returns_null_on_api_failure_and_does_not_throw_exception()
    {
        $ip = '8.8.8.8';
        Http::fake([
            'http://ip-api.com/json/*' => Http::response([], 500),
        ]);

        $this->logger->shouldReceive('warning')->once();

        $info = $this->service->getCountryInfo($ip);

        $this->assertNull($info);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
