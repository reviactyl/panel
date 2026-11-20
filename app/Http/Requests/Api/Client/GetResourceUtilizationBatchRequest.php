<?php

namespace App\Http\Requests\Api\Client;

class GetResourceUtilizationBatchRequest extends ClientApiRequest
{
    public const MAX_SERVERS = 50;

    public function rules(): array
    {
        return [
            'servers' => ['required', 'string', 'max:8192'],
        ];
    }

    public function uuids(): array
    {
        $uuids = array_values(array_unique(array_filter(
            explode(',', (string) $this->input('servers')),
            fn (string $uuid) => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1,
        )));

        return array_slice($uuids, 0, self::MAX_SERVERS);
    }
}
