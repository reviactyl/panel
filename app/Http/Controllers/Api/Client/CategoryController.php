<?php

namespace App\Http\Controllers\Api\Client;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ServerCategory;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Transformers\Api\Client\ServerCategoryTransformer;
use App\Http\Requests\Api\Client\Account\StoreServerCategoryRequest;
use App\Http\Requests\Api\Client\Account\UpdateServerCategoryRequest;

class CategoryController extends ClientApiController
{
    /**
     * List all categories for the authenticated user.
     */
    public function index(ClientApiRequest $request): array
    {
        $categories = ServerCategory::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return $this->fractal->collection($categories)
            ->transformWith($this->getTransformer(ServerCategoryTransformer::class))
            ->toArray();
    }

    /**
     * Create a new category.
     */
    public function store(StoreServerCategoryRequest $request): array
    {
        $category = ServerCategory::create([
            'uuid' => Str::uuid()->toString(),
            'user_id' => $request->user()->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'color' => $request->input('color'),
        ]);

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
        $category = ServerCategory::query()
            ->where('user_id', $request->user()->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $category->update($request->validated());

        return $this->fractal->item($category)
            ->transformWith($this->getTransformer(ServerCategoryTransformer::class))
            ->toArray();
    }

    /**
     * Reorder categories for the authenticated user.
     */
    public function reorder(ClientApiRequest $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        
        foreach ($ids as $index => $uuid) {
            ServerCategory::query()
                ->where('user_id', $request->user()->id)
                ->where('uuid', $uuid)
                ->update(['position' => $index]);
        }

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Delete a category.
     */
    public function delete(ClientApiRequest $request, string $uuid): JsonResponse
    {
        $category = ServerCategory::query()
            ->where('user_id', $request->user()->id)
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Optional: decoupling servers from this category is redundant if foreign key is set null on delete?
        // Yes, migration used nullOnDelete().
        
        $category->delete();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
