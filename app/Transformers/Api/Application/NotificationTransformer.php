<?php

namespace App\Transformers\Api\Application;

use App\Services\Notifications\NotificationService;
use Illuminate\Notifications\DatabaseNotification;

class NotificationTransformer extends BaseTransformer
{
    public function getResourceName(): string
    {
        return 'notification';
    }

    public function transform(DatabaseNotification $model): array
    {
        $notification = app(NotificationService::class)->serialize($model);

        return [
            'id' => $model->id,
            'data' => [
                'source' => (string) ($notification['data']['source'] ?? 'reviactyl'),
                'event' => (string) ($notification['data']['event'] ?? 'custom'),
                'title' => isset($notification['data']['title']) ? (string) $notification['data']['title'] : null,
                'body' => isset($notification['data']['body']) ? (string) $notification['data']['body'] : null,
                'server' => isset($notification['data']['server']) ? (string) $notification['data']['server'] : null,
                'detail' => isset($notification['data']['detail']) ? (string) $notification['data']['detail'] : null,
                'url' => (string) ($notification['data']['url'] ?? '/'),
                'status' => (string) ($notification['data']['status'] ?? (str_contains($notification['data']['event'] ?? '', 'fail') ? 'danger' : 'info')),
                'extension' => isset($notification['data']['extension']) ? (string) $notification['data']['extension'] : null,
            ],
            'read_at' => $model->read_at?->toISOString(),
            'created_at' => $model->created_at?->toISOString(),
        ];
    }
}
