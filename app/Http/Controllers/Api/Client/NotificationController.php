<?php

namespace App\Http\Controllers\Api\Client;

use App\Services\Notifications\NotificationService;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

#[Group('Notifications')]
class NotificationController extends ClientApiController
{
    public function __construct(private NotificationService $notifications)
    {
        parent::__construct();
    }

    #[Endpoint(title: 'List your notifications')]
    public function index(Request $request): JsonResponse
    {
        $request->validate(['page' => 'sometimes|integer|min:1', 'unread' => 'sometimes|boolean']);
        $query = $this->notifications->query($request->user());
        $unread = (clone $query)->whereNull('read_at')->count();
        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }
        $page = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(20);

        return new JsonResponse([
            'data' => $page->map(fn ($notification) => $this->notifications->serialize($notification)),
            'unread' => $unread,
            'last_page' => $page->lastPage(),
        ]);
    }

    #[Endpoint(title: 'Mark all notifications as read')]
    public function readAll(Request $request): JsonResponse
    {
        $this->notifications->query($request->user())->whereNull('read_at')->update(['read_at' => now()]);

        return new JsonResponse([], 204);
    }

    #[Endpoint(title: 'Mark a notification as read or unread')]
    public function update(Request $request, string $notification): JsonResponse
    {
        $data = $request->validate(['read' => 'required|boolean']);
        $model = $this->notifications->query($request->user())->findOrFail($notification);
        $model->update(['read_at' => $data['read'] ? now() : null]);

        return new JsonResponse([], 204);
    }

    #[Endpoint(title: 'Dismiss a notification')]
    public function destroy(Request $request, string $notification): JsonResponse
    {
        $this->notifications->query($request->user())->findOrFail($notification)->delete();

        return new JsonResponse([], 204);
    }
}
