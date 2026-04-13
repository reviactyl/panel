<?php

namespace Tests\Integration\Api\Client\Server\Backup;

use App\Events\ActivityLogged;
use App\Models\Backup;
use App\Models\Permission;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class RenameBackupTest extends ClientApiIntegrationTestCase
{
    public function test_user_without_permission_cannot_rename_backup()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_READ]);

        $backup = Backup::factory()->create(['server_id' => $server->id]);

        $this->actingAs($user)
            ->postJson($this->link($backup, '/rename'), ['name' => 'New Backup Name'])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_backup_alias_can_be_renamed()
    {
        Event::fake([ActivityLogged::class]);

        [$user, $server] = $this->generateTestAccount([Permission::ACTION_BACKUP_CREATE]);

        /** @var Backup $backup */
        $backup = Backup::factory()->create([
            'server_id' => $server->id,
            'name' => 'Old Backup Name',
        ]);

        $this->actingAs($user)
            ->postJson($this->link($backup, '/rename'), ['name' => 'New Backup Name'])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('attributes.name', 'New Backup Name');

        $backup->refresh();

        $this->assertSame('New Backup Name', $backup->name);
        $this->assertActivityFor('server:backup.rename', $user, [$backup, $backup->server]);
    }
}
