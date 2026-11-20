<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Requests\Api\Client\GetPageDataRequest;
use App\Services\Http\InitialApiResponses;

class PageDataController extends ClientApiController
{
    public function __construct(private InitialApiResponses $responses)
    {
        parent::__construct();
    }

    public function __invoke(GetPageDataRequest $request): array
    {
        return [
            'object' => 'page_data',
            'data' => (object) $this->responses->forPage(
                $request,
                (string) $request->input('path'),
                (int) $request->input('page', 1),
                websocket: true,
            ),
        ];
    }
}
