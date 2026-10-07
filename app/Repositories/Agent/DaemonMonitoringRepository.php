<?php

namespace App\Repositories\Agent;

use GuzzleHttp\Exception\GuzzleException;

class DaemonMonitoringRepository extends DaemonRepository
{
    /**
     * Get real-time system monitoring data from the Agent daemon.
     *
     * @throws GuzzleException
     */
    public function getSystemMonitoring(?int $timeout = null): array
    {
        try {
            $options = $timeout === null ? [] : ['timeout' => $timeout, 'connect_timeout' => min($timeout, 2)];
            $response = $this->getHttpClient()->get('/api/system/monitoring', $options);

            return json_decode($response->getBody()->__toString(), true);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
