<?php

namespace App\Http\Controllers\Api\Client\Servers;

use App\Exceptions\Model\DataValidationException;
use App\Exceptions\Repository\RecordNotFoundException;
use App\Facades\Activity;
use App\Http\Controllers\Api\Client\ClientApiController;
use App\Http\Requests\Api\Client\Servers\Settings\ReinstallServerRequest;
use App\Http\Requests\Api\Client\Servers\Settings\RenameServerRequest;
use App\Http\Requests\Api\Client\Servers\Settings\SetCategoryRequest;
use App\Http\Requests\Api\Client\Servers\Settings\SetDockerImageRequest;
use App\Models\Server;
use App\Models\ServerCategoryAssignment;
use App\Repositories\Eloquent\ServerRepository;
use App\Services\Servers\ReinstallServerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SettingsController extends ClientApiController
{
    /**
     * SettingsController constructor.
     */
    public function __construct(
        private ServerRepository $repository,
        private ReinstallServerService $reinstallServerService,
    ) {
        parent::__construct();
    }

    /**
     * Renames a server.
     *
     * @throws DataValidationException
     * @throws RecordNotFoundException
     */
    public function rename(RenameServerRequest $request, Server $server): JsonResponse
    {
        $name = $request->input('name');
        $description = $request->has('description') ? (string) $request->input('description') : $server->description;
        $this->repository->update($server->id, [
            'name' => $name,
            'description' => $description,
        ]);

        if ($server->name !== $name) {
            Activity::event('server:settings.rename')
                ->property(['old' => $server->name, 'new' => $name])
                ->log();
        }

        if ($server->description !== $description) {
            Activity::event('server:settings.description')
                ->property(['old' => $server->description, 'new' => $description])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Reinstalls the server on the daemon.
     *
     * @throws \Throwable
     */
    public function reinstall(ReinstallServerRequest $request, Server $server): JsonResponse
    {
        $this->reinstallServerService->handle($server);

        Activity::event('server:reinstall')->log();

        return new JsonResponse([], Response::HTTP_ACCEPTED);
    }

    /**
     * Changes the Docker image in use by the server.
     *
     * @throws \Throwable
     */
    public function dockerImage(SetDockerImageRequest $request, Server $server): JsonResponse
    {
        if (! in_array($server->image, array_values($server->egg->docker_images))) {
            throw new BadRequestHttpException('This server\'s Docker image has been manually set by an administrator and cannot be updated.');
        }

        $original = $server->image;
        $server->forceFill(['image' => $request->input('docker_image')])->saveOrFail();

        if ($original !== $server->image) {
            Activity::event('server:startup.image')
                ->property(['old' => $original, 'new' => $request->input('docker_image')])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Updates the category for the server.
     *
     * @throws \Throwable
     */
    public function setCategory(SetCategoryRequest $request, Server $server): JsonResponse
    {
        $categoryUuid = $request->input('category');
        $user = $request->user();
        $assignment = ServerCategoryAssignment::query()->firstOrNew([
            'server_id' => $server->id,
            'user_id' => $user->id,
        ]);
        $original = $assignment->exists ? $assignment->category_id : null;

        if (empty($categoryUuid)) {
            if ($assignment->exists) {
                $assignment->deleteOrFail();
            }

            $categoryId = null;
        } else {
            $category = $user->categories()->where('uuid', $categoryUuid)->firstOrFail();
            $categoryId = $category->id;
            $assignment->category_id = $categoryId;
            $assignment->saveOrFail();
        }

        if ($original !== $categoryId) {
            Activity::event('server:settings.category')
                ->property(['old' => $original, 'new' => $categoryId])
                ->log();
        }

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
