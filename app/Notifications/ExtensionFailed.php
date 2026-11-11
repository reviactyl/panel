<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Notifications\Notification;

class ExtensionFailed extends Notification
{
    public function __construct(public string $name) {}

    public function via(?User $notifiable = null): array
    {
        return ! $notifiable || app(NotificationPreferenceService::class)->isEnabled($notifiable, 'extension-failed')
            ? ['database']
            : [];
    }

    public function toDatabase(): array
    {
        return ['source' => 'reviactyl', 'event' => 'extension-failed', 'server' => $this->name, 'url' => '/admin/extensions'];
    }
}
