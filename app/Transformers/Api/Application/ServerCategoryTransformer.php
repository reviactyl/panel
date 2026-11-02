<?php

namespace App\Transformers\Api\Application;

use App\Models\ServerCategory;

class ServerCategoryTransformer extends BaseTransformer
{
    public function getResourceName(): string
    {
        return ServerCategory::RESOURCE_NAME;
    }

    public function transform(ServerCategory $model): array
    {
        return [
            'uuid' => $model->uuid,
            'name' => $model->name,
            'parent_uuid' => $model->parent?->uuid,
            'priority' => (int) $model->priority,
            'description' => $model->description,
            'color' => $model->color,
            'created_at' => $model->created_at->toAtomString(),
            'updated_at' => $model->updated_at->toAtomString(),
        ];
    }
}
