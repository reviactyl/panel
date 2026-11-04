<?php

namespace App\Http\Requests\Api\Application\Users\Notifications;

use App\Services\Acl\Api\AdminAcl;

class WriteNotificationRequest extends ReadNotificationRequest
{
    protected int $permission = AdminAcl::WRITE;

    public function rules(): array
    {
        return [];
    }
}
