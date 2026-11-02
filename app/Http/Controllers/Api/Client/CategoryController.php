<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Requests\Api\Client\Account\StoreServerCategoryRequest;
use App\Http\Requests\Api\Client\Account\UpdateServerCategoryRequest;
use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\ServerCategory;
use App\Services\ServerCategories\ServerCategoryService;
use App\Transformers\Api\Client\ServerCategoryTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CategoryController extends ClientApiController
{
    public function __construct(private ServerCategoryService $categories)
    {
        parent::__construct();
    }

    /**
     * List all categories for the authenticated user.
     */
    public function index(ClientApiRequest $request): array
    {
        $categories = $this->categories->all($request->user()->id);

        return $this->fractal->collection($categories)
            ->transformWith($this->getTransformer(ServerCategoryTransformer::class))
            ->toArray();
    }

    /**
     * Create a new category.
     */
    public function store(StoreServerCategoryRequest $request): array
    {
        $category = $this->categories->save($request->user()->id, $request->validated());

        return $this->fractal->item($category)
            ->transformWith($this->getTransformer(ServerCategoryTransformer::class))
            ->toArray();
    }

    /**
     * Get a specific category.
     */
    public function show(ClientApiRequest $request, string $uuid): array
    {
        $category = ServerCategory::query()
            ->where('user_id', $request->user()->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return $this->fractal->item($category)
            ->transformWith($this->getTransformer(ServerCategoryTransformer::class))
            ->toArray();
    }

    /**
     * Update a category.
     */
    public function update(UpdateServerCategoryRequest $request, string $uuid): array
    {
        $category = $this->categories->save($request->user()->id, $request->validated(), $uuid);

        return $this->fractal->item($category)
            ->transformWith($this->getTransformer(ServerCategoryTransformer::class))
            ->toArray();
    }

    /**
     * Reorder categories for the authenticated user.
     */
    public function reorder(ClientApiRequest $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'uuid', 'distinct', Rule::exists('server_categories', 'uuid')->where('user_id', $request->user()->id)],
        ]);
        $this->categories->reorder($request->user()->id, $validated['ids']);

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Delete a category.
     */
    public function delete(ClientApiRequest $request, string $uuid): JsonResponse
    {
        $this->categories->delete($request->user()->id, $uuid);

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
