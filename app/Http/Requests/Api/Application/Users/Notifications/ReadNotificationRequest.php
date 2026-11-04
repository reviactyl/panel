<?php

namespace App\Http\Requests\Api\Application\Users\Notifications;

use App\Http\Requests\Api\Application\Users\GetUsersRequest;

class ReadNotificationRequest extends GetUsersRequest
{
    public function rules(): array
    {
        return ['page' => 'sometimes|integer|min:1', 'extension' => 'sometimes|string|max:191', 'unread' => 'sometimes|boolean'];
    }
}
