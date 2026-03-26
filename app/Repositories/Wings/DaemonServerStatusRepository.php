<?php

namespace App\Repositories\Wings;

use GuzzleHttp\Exception\GuzzleException;

class DaemonServerStatusRepository extends DaemonRepository
{
    /**
     * Fetch the status and resource utilization of all servers on this node.
     *
     * Wings returns an array of APIResponse objects, each containing:
     *   - state        (string)  e.g. "running", "offline", "starting"
     *   - is_suspended (bool)
     *   - utilization  (object)  cpu_absolute, memory_bytes, memory_limit_bytes,
     *                            disk_bytes, network.rx_bytes, network.tx_bytes, uptime
     *   - configuration.uuid (string)
     *
     * @return array<int, array>
     *
     * @throws GuzzleException
     */
    public function getAllServerStatus(): array
    {
        try {
            $response = $this->getHttpClient()->get('/api/servers');

            return json_decode($response->getBody()->__toString(), true) ?? [];
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
