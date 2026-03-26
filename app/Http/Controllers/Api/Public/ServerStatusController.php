<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Wings\DaemonServerRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServerStatusController extends Controller
{
    private $repository;

    public function __construct(DaemonServerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request, string $server)
    {
        try {
            /** @var Server $serverModel */
            $serverModel = Server::query()
                ->where('uuid', $server)
                ->orWhere('uuidShort', $server)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Server not found'], 404);
        }

        $response = [
            'name' => $serverModel->name,
            'description' => $serverModel->description,
            'is_suspended' => $serverModel->isSuspended(),
            'is_installing' => !$serverModel->isInstalled(),
        ];

        $response['utilization'] = [
            'memory_bytes' => 0,
            'cpu_absolute' => 0,
            'disk_bytes' => 0,
        ];

        if ($serverModel->isSuspended() || !$serverModel->isInstalled()) {
            $response['status'] = 'offline';

            return response()->json($response);
        }

        try {
            $details = $this->repository->setServer($serverModel)->getDetails();
            $response['status'] = $details['state'] ?? 'offline';
            $response['utilization'] = $details['utilization'] ?? $response['utilization'];
        } catch (\Throwable $e) {
            $response['status'] = 'offline';
        }

        return response()->json($response);
    }
}
