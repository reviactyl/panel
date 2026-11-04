<?php

namespace App\Http\Controllers\Api\Application\Users;

use App\Http\Controllers\Api\Application\ApplicationApiController;
use App\Http\Requests\Api\Application\Users\Notifications\CreateNotificationRequest;
use App\Http\Requests\Api\Application\Users\Notifications\ReadNotificationRequest;
use App\Http\Requests\Api\Application\Users\Notifications\UpdateNotificationRequest;
use App\Http\Requests\Api\Application\Users\Notifications\WriteNotificationRequest;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use App\Transformers\Api\Application\NotificationTransformer;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('User notifications')]
class NotificationController extends ApplicationApiController
{
    public function __construct(private NotificationService $notifications)
    {
        parent::__construct();
    }

    #[Endpoint(title: 'List user notifications')]
    public function index(ReadNotificationRequest $request, User $user): array
    {
        $query = $this->notifications->query($user);
        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }
        if ($request->filled('extension')) {
            $query->where('data->extension', $request->input('extension'));
        }

        return $this->fractal->collection($query->orderByDesc('created_at')->orderByDesc('id')->paginate(20))
            ->transformWith($this->getTransformer(NotificationTransformer::class))->toArray();
    }

    #[Endpoint(title: 'View a user notification')]
    public function show(ReadNotificationRequest $request, User $user, string $notification): array
    {
        return $this->fractal->item($this->notifications->find($user, $notification))
            ->transformWith($this->getTransformer(NotificationTransformer::class))->toArray();
    }

    #[Endpoint(title: 'Create a user notification')]
    public function store(CreateNotificationRequest $request, User $user): JsonResponse
    {
        return new JsonResponse($this->fractal->item($this->notifications->create($user, $request->validated()))
            ->transformWith($this->getTransformer(NotificationTransformer::class))->toArray(), 201);
    }

    #[Endpoint(title: 'Update a user notification')]
    public function update(UpdateNotificationRequest $request, User $user, string $notification): array
    {
        return $this->fractal->item($this->notifications->update($user, $notification, $request->validated()))
            ->transformWith($this->getTransformer(NotificationTransformer::class))->toArray();
    }

    #[Endpoint(title: 'Delete a user notification')]
    public function delete(WriteNotificationRequest $request, User $user, string $notification): JsonResponse
    {
        $this->notifications->delete($user, $notification);

        return new JsonResponse([], 204);
    }
}
