<?php

namespace App\Services\Servers;

use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use Illuminate\Database\ConnectionInterface;

class ReinstallServerService
{
    /**
     * ReinstallService constructor.
     */
    public function __construct(
        private ConnectionInterface $connection,
        private DaemonServerRepository $daemonServerRepository,
    ) {}

    /**
     * Reinstall a server on the remote daemon.
     *
     * @throws \Throwable
     */
    public function handle(Server $server): Server
    {
        return $this->connection->transaction(function () use ($server) {
            $server->fill(['status' => Server::STATUS_INSTALLING])->save();

            $this->daemonServerRepository->setServer($server)->reinstall();

            return $server->refresh();
        });
    }
}
