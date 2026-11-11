<?php

namespace App\Http\Controllers\Api\Client;

use App\Services\Notifications\NotificationPreferenceService;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

#[Group('Notification settings')]
class NotificationSettingsController extends ClientApiController
{
    public function __construct(private NotificationPreferenceService $preferences)
    {
        parent::__construct();
    }

    #[Endpoint(title: 'View account notification preferences')]
    public function index(Request $request): JsonResponse
    {
        return new JsonResponse([
            'categories' => $this->preferences->definitions($request->user()),
        ]);
    }

    #[Endpoint(title: 'Update account notification preferences')]
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category' => ['required', Rule::in($this->preferences->categories())],
            'database' => ['required', 'boolean'],
            'email' => ['required', 'boolean'],
        ]);

        return new JsonResponse($this->preferences->update(
            $request->user(),
            $data['category'],
            (bool) $data['database'],
            (bool) $data['email'],
        ));
    }
}
