<?php

namespace App\Http\Controllers\Api\Client\Servers;

use App\Facades\Activity;
use App\Http\Controllers\Api\Client\ClientApiController;
use App\Http\Requests\Api\Client\Servers\Files\RecycleBinRequest;
use App\Models\Server;
use App\Services\Servers\RecycleBinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class RecycleBinController extends ClientApiController
{
    public function index(RecycleBinRequest $request, Server $server, RecycleBinService $bin): JsonResponse
    {
        return new JsonResponse($bin->listing($server));
    }

    public function restore(RecycleBinRequest $request, Server $server, RecycleBinService $bin): JsonResponse
    {
        return Cache::lock('recycle-bin:'.$server->id, 300)->block(5, function () use ($request, $server, $bin) {
            $result = $bin->restore($server, $request->string('id')->toString(), $request->boolean('replace'), $request->boolean('preview'), $request->string('path')->toString());
            if ($result) {
                return new JsonResponse($result, $request->boolean('preview') ? 200 : 409);
            }
            Activity::event('server:file.restore')->property('id', $request->input('id'))->property('path', $request->input('path'))->log();

            return new JsonResponse([], 204);
        });
    }

    public function empty(RecycleBinRequest $request, Server $server, RecycleBinService $bin): JsonResponse
    {
        return Cache::lock('recycle-bin:'.$server->id, 300)->block(5, function () use ($server, $bin) {
            $bin->empty($server);
            Activity::event('server:file.delete')->property('files', ['/.recycle-bin'])->log();

            return new JsonResponse([], 204);
        });
    }

    public function destroy(RecycleBinRequest $request, Server $server, RecycleBinService $bin): JsonResponse
    {
        return Cache::lock('recycle-bin:'.$server->id, 300)->block(5, function () use ($request, $server, $bin) {
            $bin->purge($server, $request->string('id')->toString(), $request->string('path')->toString());
            Activity::event('server:file.delete')->property('files', [$request->input('id')])->log();

            return new JsonResponse([], 204);
        });
    }
}
