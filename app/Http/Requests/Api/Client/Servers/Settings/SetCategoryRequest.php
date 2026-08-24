<?php

namespace App\Http\Requests\Api\Client\Servers\Settings;

use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\ServerCategory;
use Illuminate\Validation\Rule;

class SetCategoryRequest extends ClientApiRequest
{
    public function rules(): array
    {
        return [
            'category' => [
                'nullable',
                'string',
                Rule::exists(ServerCategory::class, 'uuid')
                    ->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
        ];
    }

    public function permission(): string
    {
        return 'settings.rename'; // Using rename permission as a proxy for "general settings"
    }
}
