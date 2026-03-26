<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\JsonResponse;
use App\Services\Users\UserUpdateService;
use App\Transformers\Api\Client\AccountTransformer;
use App\Http\Requests\Api\Client\Account\UpdateEmailRequest;
use App\Http\Requests\Api\Client\Account\UpdatePasswordRequest;

class AccountController extends ClientApiController
{
    /**
     * AccountController constructor.
     */
    public function __construct(private AuthManager $manager, private UserUpdateService $updateService)
    {
        parent::__construct();
    }

    public function index(Request $request): array
    {
        return $this->fractal->item($request->user())
            ->transformWith($this->getTransformer(AccountTransformer::class))
            ->toArray();
    }

    /**
     * Update the authenticated user's email address.
     */
    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        $original = $request->user()->email;
        $this->updateService->handle($request->user(), $request->validated());

        if ($original !== $request->input('email')) {
            Activity::event('user:account.email-changed')
                ->property(['old' => $original, 'new' => $request->input('email')])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Update the authenticated user's password. All existing sessions will be logged
     * out immediately.
     *
     * @throws \Throwable
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $this->updateService->handle($request->user(), $request->validated());

        $guard = $this->manager->guard();
        // If you do not update the user in the session you'll end up working with a
        // cached copy of the user that does not include the updated password. Do this
        // to correctly store the new user details in the guard and allow the logout
        // other devices functionality to work.
        $guard->setUser($user);

        // This method doesn't exist in the stateless Sanctum world.
        if (method_exists($guard, 'logoutOtherDevices')) { // @phpstan-ignore function.alreadyNarrowedType
            $guard->logoutOtherDevices($request->input('password'));
        }

        Activity::event('user:account.password-changed')->log();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function updateLanguage(Request $request): JsonResponse
    {
        $request->validate([
            'language' => ['required', 'string', 'max:5'],
        ]);

        $user = $request->user();
        $original = $user->language;
        $user->language = $request->input('language');
        $user->save();

        if ($original !== $user->language) {
            Activity::event('user:account.language-changed')
                ->property(['old' => $original, 'new' => $user->language])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    public function updateEditor(Request $request): JsonResponse
    {
        $request->validate([
            'fileEditor' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $original = $user->editor;
        $user->editor = $request->input('fileEditor');
        $user->save();

        if ($original !== $user->editor) {
            Activity::event('user:account.file-editor-changed')
                ->property(['old' => $original, 'new' => $user->editor])
                ->log();
        }

        return new JsonResponse([$user], Response::HTTP_OK);
    }
}
