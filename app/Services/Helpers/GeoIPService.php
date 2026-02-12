<?php

namespace App\Services\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Psr\Log\LoggerInterface;

class GeoIPService
{
    private const API_URL = 'http://ip-api.com/json/';

    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Resolve an IP address to a country name.
     */
    public function getCountry(string $ip): ?string
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }

        return Cache::remember('geoip:' . $ip, 86400, function () use ($ip) {
            try {
                $response = Http::get(self::API_URL . $ip, [
                    'fields' => 'status,message,country',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'success') {
                        return $data['country'] ?? 'Unknown';
                    }
                }

                $this->logger->warning('GeoIP resolution failed for IP: ' . $ip, [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            } catch (\Exception $e) {
                $this->logger->error('GeoIP resolution exception for IP: ' . $ip . ' - ' . $e->getMessage());
            }

            return 'Unknown';
        });
    }
}
