<?php

namespace App\Http\Controllers\Api\Remote;

use App\Exceptions\Http\HttpForbiddenException;
use App\Facades\Activity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Remote\SftpAuthenticationFormRequest;
use App\Models\Permission;
use App\Models\Server;
use App\Models\User;
use App\Services\Servers\GetUserPermissionsService;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\NoKeyLoadedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class SftpAuthenticationController extends Controller
{
    use ThrottlesLogins;

    public function __construct(protected GetUserPermissionsService $permissions) {}

    /**
     * Authenticate a set of credentials and return the associated server details
     * for a SFTP connection on the daemon. This supports both public key and password
     * based credentials.
     */
    public function __invoke(SftpAuthenticationFormRequest $request): JsonResponse
    {
        $connection = $this->parseUsername($request->input('username'));
        if (empty($connection['server'])) {
            throw new BadRequestHttpException('No valid server identifier was included in the request.');
        }

        if ($this->hasTooManyLoginAttempts($request)) {
            $seconds = $this->limiter()->availableIn($this->throttleKey($request));

            throw new TooManyRequestsHttpException($seconds, "Too many login attempts for this account, please try again in $seconds seconds.");
        }

        $user = $this->getUser($request, $connection['username']);
        $server = $this->getServer($request, $connection['server']);

        if ($request->input('type') !== 'public_key') {
            if (! password_verify($request->input('password'), $user->password)) {
                $this->reject($request, true, $user, 'password');
            }
        } else {
            $key = null;
            try {
                $key = PublicKeyLoader::loadPublicKey(trim($request->input('password')));
            } catch (NoKeyLoadedException) {
                // do nothing
            }

            if (! $key || ! $user->sshKeys()->where('fingerprint', $key->getFingerprint('sha256'))->exists()) {
                // We don't log here because of the way the SFTP system works. This endpoint
                // will get hit for every key the user provides, which could be 4 or 5. That is
                // a lot of unnecessary log noise.
                //
                // For now, we'll only log failures due to a bad password as those are not likely
                // to occur more than once in a session for the user, and are more likely to be of
                // value to the end user.
                $this->reject($request, is_null($key), $user, 'public_key');
            }
        }

        $this->validateSftpAccess($request, $user, $server);
        $this->clearLoginAttempts($request);

        return new JsonResponse([
            'user' => $user->uuid,
            'server' => $server->uuid,
            'permissions' => $this->permissions->handle($server, $user),
        ]);
    }

    /**
     * Finds the server being requested and ensures that it belongs to the node this
     * request stems from.
     */
    protected function getServer(Request $request, string $uuid): Server
    {
        return Server::query()
            ->where(fn ($builder) => $builder->where('uuid', $uuid)->orWhere('uuidShort', $uuid))
            ->where('node_id', $request->attributes->get('node')->id)
            ->firstOr(function () use ($request) {
                $this->reject($request);
            });
    }

    /**
     * Finds a user with the given username or increments the login attempts.
     */
    protected function getUser(Request $request, string $username): User
    {
        return User::query()->where('username', $username)->firstOr(function () use ($request) {
            $this->reject($request);
        });
    }

    /**
     * Parses the username provided to the request.
     *
     * @return array{"username": string, "server": string}
     */
    protected function parseUsername(string $value): array
    {
        // Reverse the string to avoid issues with usernames that contain periods.
        $parts = explode('.', strrev($value), 2);

        // Unreverse the strings after parsing them apart.
        return [
            'username' => strrev(array_get($parts, 1)),
            'server' => strrev(array_get($parts, 0)),
        ];
    }

    /**
     * Rejects the request and increments the login attempts.
     */
    protected function reject(Request $request, bool $increment = true, ?User $user = null, string $method = 'unknown'): void
    {
        $activity = Activity::event('auth:sftp.fail')->property('method', $method);

        if ($user !== null) {
            $activity->subject($user);
        }

        $activity->log();

        if ($increment) {
            $this->incrementLoginAttempts($request);
        }

        throw new HttpForbiddenException('Authorization credentials were not correct, please try again.');
    }

    /**
     * Validates that a user should have permission to use SFTP for the given server.
     */
    protected function validateSftpAccess(Request $request, User $user, Server $server): void
    {
        if (! $user->root_admin && $server->owner_id !== $user->id) {
            $permissions = $this->permissions->handle($server, $user);

            if (! in_array(Permission::ACTION_FILE_SFTP, $permissions)) {
                $this->reject($request, false, $user, 'permission');
            }
        }

        $server->validateCurrentState();
    }

    /**
     * Get the throttle key for the given request.
     */
    protected function throttleKey(Request $request): string
    {
        $connection = $this->parseUsername($request->input('username', ''));
        $username = $connection['username'] !== ''
            ? $connection['username']
            : $request->input('username', '');

        return strtolower($username.'|'.$request->ip());
    }
}
