<?php

namespace App\Http\Controllers\Api\Client\Servers;

use App\Http\Controllers\Api\Client\ClientApiController;
use App\Models\Permission;
use App\Models\Server;
use App\Services\Subusers\SubuserPreviewContext;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

#[Group('Backup notifications')]
class BackupNotificationController extends ClientApiController
{
    #[Endpoint(title: 'View backup notification preferences')]
    public function index(Request $request, Server $server): JsonResponse
    {
        abort_if($request->attributes->get(SubuserPreviewContext::class), 403);
        abort_unless($request->user()->can(Permission::ACTION_BACKUP_READ, $server), 403);
        $row = DB::table('backup_notification_preferences')->where('user_id', $request->user()->id)->where('server_id', $server->id)->first();

        return new JsonResponse(['manual' => (bool) ($row->manual ?? false), 'scheduled' => (bool) ($row->scheduled ?? false), 'email' => (bool) ($row->email ?? false)]);
    }

    #[Endpoint(title: 'Update backup notification preferences')]
    public function update(Request $request, Server $server): JsonResponse
    {
        abort_if($request->attributes->get(SubuserPreviewContext::class), 403);
        abort_unless($request->user()->can(Permission::ACTION_BACKUP_READ, $server), 403);
        $data = $request->validate(['manual' => 'required|boolean', 'scheduled' => 'required|boolean', 'email' => 'required|boolean']);
        DB::table('backup_notification_preferences')->updateOrInsert(['user_id' => $request->user()->id, 'server_id' => $server->id], $data);

        return new JsonResponse($data);
    }
}
