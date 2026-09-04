<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\Activity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Laravel\Passkeys\Actions\DeletePasskey;
use Laravel\Passkeys\Actions\GenerateRegistrationOptions;
use Laravel\Passkeys\Actions\StorePasskey;
use Laravel\Passkeys\Http\Requests\PasskeyRegistrationRequest;
use Laravel\Passkeys\Support\WebAuthn;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PasskeyController extends ClientApiController
{
    /**
     * Return passkeys owned by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $credentials = $request->user()
            ->passkeys()
            ->latest('created_at')
            ->get()
            ->map(fn ($credential) => [
                'id' => (string) $credential->id,
                'name' => $credential->name,
                'authenticator' => $credential->authenticator,
                'created_at' => $credential->created_at,
                'updated_at' => $credential->updated_at,
            ]);

        return new JsonResponse([
            'data' => $credentials,
        ]);
    }

    /**
     * Build registration options for a new passkey.
     */
    public function options(Request $request, GenerateRegistrationOptions $generate): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user instanceof User) {
            throw new BadRequestHttpException('Unable to validate the authenticated account.');
        }

        if (! Hash::check($data['password'], $user->password)) {
            throw new BadRequestHttpException('The password provided was not valid.');
        }

        $options = $generate($user);

        $request->session()->put('passkey.registration_options', WebAuthn::toJson($options));

        return new JsonResponse(WebAuthn::toBrowserArray($options));
    }

    /**
     * Save a newly registered passkey.
     */
    public function store(PasskeyRegistrationRequest $request, StorePasskey $storePasskey): JsonResponse
    {
        $passkey = $storePasskey(
            $request->user(),
            $request->string('name')->toString(),
            $request->credential(),
            $request->registrationOptions()
        );

        Activity::event('user:passkey.create')->property('id', $passkey->id)->log();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a passkey from the current account.
     */
    public function delete(Request $request, DeletePasskey $deletePasskey, ?string $id = null): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $credentialId = $id ?? $request->input('id');

        if (! is_string($credentialId) || $credentialId === '') {
            throw new BadRequestHttpException('A passkey id must be provided.');
        }

        $user = $request->user();

        if (! $user instanceof User) {
            throw new BadRequestHttpException('Unable to validate the authenticated account.');
        }

        if (! Hash::check($data['password'], $user->password)) {
            throw new BadRequestHttpException('The password provided was not valid.');
        }

        $credential = $user->passkeys()->whereKey($credentialId)->firstOrFail();

        $deletePasskey($user, $credential);

        Activity::event('user:passkey.delete')->property('id', $credentialId)->log();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
