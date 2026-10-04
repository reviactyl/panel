<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Requests\Api\Client\ClientApiRequest;
use App\Models\Alert;
use App\Models\Server;
use App\Models\User;
use App\Services\Alerts\AlertVisibilityService;
use App\Transformers\Api\Client\AlertTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AlertController extends ClientApiController
{
    public function __construct(private readonly AlertVisibilityService $alerts)
    {
        parent::__construct();
    }

    public function index(ClientApiRequest $request): array
    {
        $this->validate($request, [
            'placement' => ['sometimes', 'string', Rule::in([Alert::PLACEMENT_DASHBOARD, Alert::PLACEMENT_SERVER, Alert::PLACEMENT_ACCOUNT])],
            'server' => ['required_if:placement,'.Alert::PLACEMENT_SERVER, 'nullable', 'string', 'max:36'],
        ]);

        $placement = $request->input('placement', Alert::PLACEMENT_DASHBOARD);
        $server = $placement === Alert::PLACEMENT_SERVER
            ? $this->findServer($request->user(), (string) $request->input('server'))
            : null;

        return $this->fractal->collection($this->alerts->forUser($request->user(), $placement, $server))
            ->transformWith($this->getTransformer(AlertTransformer::class))
            ->toArray();
    }

    public function dismiss(ClientApiRequest $request, Alert $alert): JsonResponse
    {
        $this->assertAvailable($request->user(), $alert);

        if (! $alert->dismissible) {
            throw new BadRequestHttpException(trans('exceptions.alerts.not_dismissible'));
        }

        $this->alerts->dismiss($alert, $request->user());

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    public function click(ClientApiRequest $request, Alert $alert): JsonResponse
    {
        $this->assertAvailable($request->user(), $alert);

        if (empty($alert->publicButtons())) {
            throw new BadRequestHttpException(trans('exceptions.alerts.no_action'));
        }

        $this->alerts->click($alert, $request->user());

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    private function assertAvailable(User $user, Alert $alert): void
    {
        if (! $this->alerts->isAvailableTo($alert, $user)) {
            throw new NotFoundHttpException(trans('exceptions.api.resource_not_found'));
        }
    }

    private function findServer(User $user, string $identifier): Server
    {
        $query = $user->root_admin ? Server::query() : $user->accessibleServers();

        $server = $query
            ->where(fn ($builder) => $builder->where('servers.uuid', $identifier)->orWhere('servers.uuidShort', $identifier))
            ->first();

        if ($server === null) {
            throw new NotFoundHttpException(trans('exceptions.api.resource_not_found'));
        }

        return $server;
    }
}
