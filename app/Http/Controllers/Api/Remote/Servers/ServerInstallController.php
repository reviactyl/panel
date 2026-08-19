<?php

namespace App\Http\Controllers\Api\Remote\Servers;

use App\Events\Server\Installed as ServerInstalled;
use App\Exceptions\Http\HttpForbiddenException;
use App\Exceptions\Model\DataValidationException;
use App\Exceptions\Repository\RecordNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Remote\InstallationDataRequest;
use App\Models\Server;
use App\Repositories\Eloquent\ServerRepository;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ServerInstallController extends Controller
{
    /**
     * ServerInstallController constructor.
     */
    public function __construct(private ServerRepository $repository, private EventDispatcher $eventDispatcher) {}

    /**
     * Returns installation information for a server.
     *
     * @throws RecordNotFoundException
     */
    public function index(Request $request, string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);
        $egg = $server->egg;

        if (! $server->node->is($request->attributes->get('node'))) {
            throw new HttpForbiddenException('Requesting node does not have permission to access this server.');
        }

        return new JsonResponse([
            'container_image' => $egg->copy_script_container,
            'entrypoint' => $egg->copy_script_entry,
            'script' => $egg->copy_script_install,
        ]);
    }

    /**
     * Updates the installation state of a server.
     *
     * @throws RecordNotFoundException
     * @throws DataValidationException
     */
    public function store(InstallationDataRequest $request, string $uuid): JsonResponse
    {
        $server = $this->repository->getByUuid($uuid);
        $successful = $request->boolean('successful');
        $status = null;

        if (! $server->node->is($request->attributes->get('node'))) {
            throw new HttpForbiddenException('Requesting node does not have permission to access this server.');
        }

        // Make sure the type of failure is accurate
        if (! $successful) {
            $status = Server::STATUS_INSTALL_FAILED;

            if ($request->boolean('reinstall')) {
                $status = Server::STATUS_REINSTALL_FAILED;
            }
        }

        // Keep the server suspended if it's already suspended
        if ($server->status === Server::STATUS_SUSPENDED) {
            $status = Server::STATUS_SUSPENDED;
        }

        $fields = ['status' => $status];
        if ($successful) {
            $fields['installed_at'] = CarbonImmutable::now();
        }

        $this->repository->update($server->id, $fields, true, true);

        // If the server successfully installed, fire installed event.
        // This logic allows individually disabling install and reinstall notifications separately.
        $isInitialInstall = is_null($server->installed_at);
        if ($successful && $isInitialInstall && config()->get('panel.email.send_install_notification', true)) {
            $this->eventDispatcher->dispatch(new ServerInstalled($server));
        } elseif ($successful && ! $isInitialInstall && config()->get('panel.email.send_reinstall_notification', true)) {
            $this->eventDispatcher->dispatch(new ServerInstalled($server));
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
