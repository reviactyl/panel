<?php

namespace App\Services\Users;

use App\Models\User;
use App\Traits\Services\HasUserLevels;
use Illuminate\Contracts\Hashing\Hasher;

class UserUpdateService
{
    use HasUserLevels;

    /**
     * UserUpdateService constructor.
     */
    public function __construct(private Hasher $hasher, private SocialAccountLinkService $socialAccountLinkService) {}

    /**
     * Update the user model instance and return the updated model.
     *
     * @throws \Throwable
     */
    public function handle(User $user, array $data): User
    {
        if (! empty(array_get($data, 'password'))) {
            $data['password'] = $this->hasher->make($data['password']);
        } else {
            unset($data['password']);
        }

        $accounts = $data['social_accounts'] ?? [];
        unset($data['social_accounts']);

        $user->getConnection()->transaction(function () use ($user, $data, $accounts) {
            $user->forceFill($data)->saveOrFail();
            $this->socialAccountLinkService->handle($user, $accounts);
        });

        return $user->refresh();
    }
}
