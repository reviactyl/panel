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
     * Resolve an IP address to country information.
     * 
     * @return array{country: string, code: string}|null
     */
    public function getCountryInfo(string $ip): ?array
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return ['country' => 'Localhost', 'code' => 'UN'];
        }

        return Cache::remember('geoip:v2:' . $ip, 86400, function () use ($ip) {
            try {
                $response = Http::get(self::API_URL . $ip, [
                    'fields' => 'status,message,country,countryCode',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (($data['status'] ?? '') === 'success') {
                        return [
                            'country' => $data['country'] ?? 'Unknown',
                            'code' => $data['countryCode'] ?? 'UN',
                        ];
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
