<?php

namespace App\Transformers\Api\Client;

use App\Models\Alert;

class AlertTransformer extends BaseClientTransformer
{
    public function getResourceName(): string
    {
        return Alert::RESOURCE_NAME;
    }

    public function transform(Alert $model): array
    {
        return [
            'uuid' => $model->uuid,
            'type' => $model->type,
            'title' => $model->title,
            'message' => $model->message,
            'color' => $model->color,
            'buttons' => $model->publicButtons(),
            'dismissible' => $model->dismissible,
            'dismiss_on_action' => $model->hidesAfterAction(),
            'redisplay_after_days' => $model->redisplay_after_days,
            'dismissals_reset_at' => $model->dismissals_reset_at?->toAtomString(),
        ];
    }
}
