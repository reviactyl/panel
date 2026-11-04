<?php

namespace App\Http\Requests\Api\Application\Users\Notifications;

use App\Services\Notifications\NotificationService;

class CreateNotificationRequest extends WriteNotificationRequest
{
    public function rules(): array
    {
        return NotificationService::rules(true);
    }
}
