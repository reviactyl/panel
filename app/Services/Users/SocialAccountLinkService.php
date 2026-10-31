<?php

namespace App\Services\Users;

use App\Models\SocialLogin;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SocialAccountLinkService
{
    public function handle(User $user, array $accounts): void
    {
        foreach ($accounts as $index => $account) {
            $link = SocialLogin::query()->firstOrCreate([
                'provider' => $account['provider'],
                'provider_user_id' => $account['provider_user_id'],
            ], ['user_id' => $user->id]);

            if ($link->user_id != $user->id) {
                throw ValidationException::withMessages([
                    "social_accounts.$index.provider_user_id" => 'This social account is already linked to another user.',
                ]);
            }
        }
    }
}
