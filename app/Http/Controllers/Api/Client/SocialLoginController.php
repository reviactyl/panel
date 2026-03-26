<?php

namespace App\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Transformers\Api\Client\SocialLoginTransformer;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class SocialLoginController extends ClientApiController
{
    /**
     * Return all the social logins for the authenticated user.
     */
    public function index(ClientApiRequest $request): array
    {
        return $this->fractal->collection($request->user()->socialLogins)
            ->transformWith($this->getTransformer(SocialLoginTransformer::class))
            ->toArray();
    }

    /**
     * Remove a social login from the account.
     */
    public function delete(ClientApiRequest $request, string $provider): JsonResponse
    {
        $user = $request->user();

        // Find the social login
        $socialLogin = $user->socialLogins()->where('provider', $provider)->firstOrFail();

        $count = $user->socialLogins()->count();
        // Check if the user has a password set. assume if the password field is not empty, it is set.
        $hasPassword = !empty($user->password);

        if ($count <= 1 && !$hasPassword) {
            throw new ConflictHttpException(__('exceptions.social.unlink_only_login'));
        }

        $socialLogin->delete();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
