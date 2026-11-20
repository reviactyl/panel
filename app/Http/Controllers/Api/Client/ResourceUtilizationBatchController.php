<?php

namespace App\Http\Controllers\Api\Client;

use App\Exceptions\Http\Server\ServerStateConflictException;
use App\Http\Middleware\Api\Client\Server\AuthenticateServerAccess;
use App\Http\Requests\Api\Client\GetResourceUtilizationBatchRequest;
use App\Models\Server;
use App\Services\Helpers\ServerResourceUsageService;
use App\Services\Subusers\SubuserPreviewContext;
use App\Transformers\Api\Client\StatsTransformer;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ResourceUtilizationBatchController extends ClientApiController
{
    public function __construct(private ServerResourceUsageService $usage)
    {
        parent::__construct();
    }

    public function __invoke(GetResourceUtilizationBatchRequest $request): array
    {
        $user = $request->user();
        $preview = $request->attributes->get(SubuserPreviewContext::class);
        $preview = $preview instanceof SubuserPreviewContext ? $preview : null;

        $data = [];
        $available = [];
        $servers = Server::query()
            ->whereIn('uuid', $request->uuids())
            ->with(['node', 'subusers', 'transfer'])
            ->get();

        foreach ($servers as $server) {
            if (! AuthenticateServerAccess::canAccess($user, $server, $preview)) {
                continue;
            }

            try {
                $server->validateCurrentState();
                $available[] = $server;
            } catch (ServerStateConflictException $exception) {
                $data[$server->uuid] = $this->error($exception);
            }
        }

        $transformer = $this->getTransformer(StatsTransformer::class);
        foreach ($this->usage->getMany($available) as $uuid => $stats) {
            $data[$uuid] = $stats instanceof \Throwable
                ? $this->error($stats)
                : $this->fractal->item($stats)->transformWith($transformer)->toArray();
        }

        return ['object' => 'list', 'data' => (object) $data];
    }

    private function error(\Throwable $exception): array
    {
        $status = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : (method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500);

        if ($status >= 500) {
            Log::warning('Could not load resource usage for a server in a batch request.', ['exception' => $exception]);
        }

        return [
            'errors' => [[
                'code' => class_basename($exception),
                'status' => (string) $status,
                'detail' => $status < 500 ? $exception->getMessage() : 'The server could not report its resource usage.',
            ]],
        ];
    }
}
