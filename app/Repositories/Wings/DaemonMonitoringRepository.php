<?php

namespace App\Repositories\Wings;

use GuzzleHttp\Exception\GuzzleException;

class DaemonMonitoringRepository extends DaemonRepository
{
    /**
     * Get real-time system monitoring data from the Wings daemon.
     *
     * @throws GuzzleException
     */
    public function getSystemMonitoring(): array
    {
        try {
            $response = $this->getHttpClient()->get('/api/system/monitoring');

            return json_decode($response->getBody()->__toString(), true);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
