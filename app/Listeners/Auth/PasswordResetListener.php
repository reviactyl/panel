<?php

namespace App\Listeners\Auth;

use App\Facades\Activity;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;

class PasswordResetListener
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle(PasswordReset $event): void
    {
        Activity::event('auth:password-reset')
            ->withRequestMetadata()
            ->subject($event->user)
            ->log();
    }
}
