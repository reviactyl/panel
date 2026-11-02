<?php

namespace App\Transformers\Api\Client;

use App\Models\ServerCategory;

class ServerCategoryTransformer extends BaseClientTransformer
{
    public function getResourceName(): string
    {
        return ServerCategory::RESOURCE_NAME;
    }

    public function transform(ServerCategory $model): array
    {
        return [
            'uuid' => $model->uuid,
            'priority' => (int) $model->priority,
            'parent_uuid' => $model->parent?->uuid,
            'name' => $model->name,
            'description' => $model->description,
            'color' => $model->color,
            'created_at' => $model->created_at->toAtomString(),
            'updated_at' => $model->updated_at->toAtomString(),
        ];
    }
}
