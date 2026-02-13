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
        if (!$this->isPublicIP($ip)) {
            return [
                'country' => __('strings.local_network'),
                'code' => 'LOCAL',
            ];
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

            return null;
        });
    }

    /**
     * Determine if an IP address is a public WAN address.
     */
    private function isPublicIP(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}
