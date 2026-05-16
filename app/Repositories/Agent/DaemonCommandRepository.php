<?php

namespace App\Repositories\Agent;

use App\Exceptions\Http\Connection\DaemonConnectionException;
use App\Models\Server;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;

/**
 * @method \App\Repositories\Agent\DaemonCommandRepository setNode(\App\Models\Node $node)
 * @method \App\Repositories\Agent\DaemonCommandRepository setServer(\App\Models\Server $server)
 */
class DaemonCommandRepository extends DaemonRepository
{
    /**
     * Sends a command or multiple commands to a running server instance.
     *
     * @throws DaemonConnectionException
     */
    public function send(array|string $command): ResponseInterface
    {
        Assert::isInstanceOf($this->server, Server::class);

        try {
            return $this->getHttpClient()->post(
                sprintf('/api/servers/%s/commands', $this->server->uuid),
                [
                    'json' => ['commands' => is_array($command) ? $command : [$command]],
                ]
            );
        } catch (TransferException $exception) {
            throw new DaemonConnectionException($exception);
        }
    }
}
