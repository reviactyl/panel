<?php

namespace App\Http\Controllers\Api\Client;

use App\Facades\Activity;
use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PasskeyController extends ClientApiController
{
    /**
     * Return passkeys owned by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $credentials = $request->user()
            ->webAuthnCredentials()
            ->latest('created_at')
            ->get()
            ->map(fn ($credential) => [
                'id' => $credential->id,
                'name' => $credential->alias,
                'origin' => $credential->origin,
                'created_at' => $credential->created_at,
                'updated_at' => $credential->updated_at,
                'disabled_at' => $credential->disabled_at,
            ]);

        return new JsonResponse([
            'data' => $credentials,
        ]);
    }

    /**
     * Build registration options for a new passkey.
     */
    public function options(AttestationRequest $request): Responsable
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

        // Secure + userless allows account selection directly from the authenticator.
        return $request->secureRegistration()->userless()->toCreate();
    }

    /**
     * Save a newly registered passkey.
     */
    public function store(AttestedRequest $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:191'],
        ]);

        $id = $request->save([
            'alias' => $data['name'] ?? null,
        ]);

        Activity::event('user:passkey.create')->property('id', $id)->log();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a passkey from the current account.
     */
    public function delete(Request $request, ?string $id = null): JsonResponse
    {
        $credentialId = $id ?? $request->input('id');

        if (! is_string($credentialId) || $credentialId === '') {
            throw new BadRequestHttpException('A passkey id must be provided.');
        }

        $credential = $request->user()->webAuthnCredentials()->whereKey($credentialId)->firstOrFail();

        $credential->delete();

        Activity::event('user:passkey.delete')->property('id', $credentialId)->log();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
