<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ExtensionFailed extends Notification
{
    public function __construct(public string $name) {}

    public function via(): array
    {
        return ['database'];
    }

    public function toDatabase(): array
    {
        return ['source' => 'reviactyl', 'event' => 'extension-failed', 'server' => $this->name, 'url' => '/admin/extensions'];
    }
}
