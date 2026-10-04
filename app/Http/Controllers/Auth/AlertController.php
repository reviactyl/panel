<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Services\Alerts\AlertVisibilityService;
use App\Transformers\Api\Client\AlertTransformer;

class AlertController extends Controller
{
    /**
     * @return array{object: string, data: array<int, array{object: string, attributes: array<string, mixed>}>}
     */
    public function __invoke(AlertVisibilityService $alerts, AlertTransformer $transformer): array
    {
        return [
            'object' => 'list',
            'data' => $alerts->forGuests()
                ->map(fn (Alert $alert) => [
                    'object' => Alert::RESOURCE_NAME,
                    'attributes' => $transformer->transform($alert),
                ])
                ->all(),
        ];
    }
}
