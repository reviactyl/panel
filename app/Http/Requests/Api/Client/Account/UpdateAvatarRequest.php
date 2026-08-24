<?php

namespace App\Http\Requests\Api\Client\Account;

use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UpdateAvatarRequest extends ClientApiRequest
{
    public function rules(): array
    {
        return [
            'avatar_style' => ['required', 'string', Rule::in(User::AVATAR_STYLES)],
            'avatar_animated' => ['required', 'boolean'],
        ];
    }
}
