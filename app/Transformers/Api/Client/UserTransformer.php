<?php

namespace App\Transformers\Api\Client;

use App\Models\User;

class UserTransformer extends BaseClientTransformer
{
    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return User::RESOURCE_NAME;
    }

    /**
     * Transforms a User model into a representation that can be shown to regular
     * users of the API.
     */
    public function transform(User $model): array
    {
        return [
            'uuid' => $model->uuid,
            'identifier' => $model->identifier,
            'username' => $model->username,
            'email' => $model->email,
            'image' => $model->avatar_url,
            '2fa_enabled' => $model->use_totp,
            'created_at' => $model->created_at->toAtomString(),
        ];
    }
}
