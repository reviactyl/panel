<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Updates\SoftwareUpdateStatusService;
use Illuminate\Http\JsonResponse;

class SoftwareUpdateStatusController extends Controller
{
    public function __invoke(SoftwareUpdateStatusService $statuses): JsonResponse
    {
        $status = $statuses->get($statuses->panelKey());

        return response()->json([
            'state' => $status['state'] ?? 'idle',
            'version' => $status['version'] ?? null,
            'updated_at' => $status['updated_at'] ?? null,
        ]);
    }
}
