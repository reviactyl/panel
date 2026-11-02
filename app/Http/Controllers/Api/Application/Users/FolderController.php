<?php

namespace App\Http\Controllers\Api\Application\Users;

use App\Http\Controllers\Api\Application\ApplicationApiController;
use App\Http\Requests\Api\Application\Users\Folders\AssignServerFolderRequest;
use App\Http\Requests\Api\Application\Users\Folders\ReadFolderRequest;
use App\Http\Requests\Api\Application\Users\Folders\WriteFolderRequest;
use App\Models\Server;
use App\Models\ServerCategory;
use App\Models\User;
use App\Services\ServerCategories\ServerCategoryService;
use App\Transformers\Api\Application\ServerCategoryTransformer;
use Illuminate\Http\JsonResponse;

class FolderController extends ApplicationApiController
{
    public function __construct(private ServerCategoryService $folders)
    {
        parent::__construct();
    }

    public function index(ReadFolderRequest $request, User $user): array
    {
        return $this->fractal->collection($this->folders->all($user->id))
            ->transformWith($this->getTransformer(ServerCategoryTransformer::class))->toArray();
    }

    public function show(ReadFolderRequest $request, User $user, string $uuid): array
    {
        $folder = ServerCategory::query()->with('parent')->where('user_id', $user->id)->where('uuid', $uuid)->firstOrFail();

        return $this->fractal->item($folder)->transformWith($this->getTransformer(ServerCategoryTransformer::class))->toArray();
    }

    public function store(WriteFolderRequest $request, User $user): JsonResponse
    {
        $folder = $this->folders->save($user->id, $request->validated());

        return new JsonResponse($this->fractal->item($folder)->transformWith($this->getTransformer(ServerCategoryTransformer::class))->toArray(), 201);
    }

    public function update(WriteFolderRequest $request, User $user, string $uuid): array
    {
        $folder = $this->folders->save($user->id, $request->validated(), $uuid);

        return $this->fractal->item($folder)->transformWith($this->getTransformer(ServerCategoryTransformer::class))->toArray();
    }

    public function delete(WriteFolderRequest $request, User $user, string $uuid): JsonResponse
    {
        $this->folders->delete($user->id, $uuid);

        return new JsonResponse([], 204);
    }

    public function server(ReadFolderRequest $request, User $user, Server $server): JsonResponse
    {
        abort_unless($user->accessibleServers()->where('servers.id', $server->id)->exists(), 404);
        $folder = $user->categories()->whereHas('assignments', fn ($query) => $query->where('server_id', $server->id))->first();

        return new JsonResponse($folder
            ? $this->fractal->item($folder)->transformWith($this->getTransformer(ServerCategoryTransformer::class))->toArray()
            : ['object' => 'null_resource', 'attributes' => null]);
    }

    public function assign(AssignServerFolderRequest $request, User $user, Server $server): JsonResponse
    {
        $this->folders->assign($user, $server, $request->input('category_uuid'));

        return new JsonResponse([], 204);
    }
}
