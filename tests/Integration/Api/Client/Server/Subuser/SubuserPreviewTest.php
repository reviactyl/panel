<?php

namespace Tests\Integration\Api\Client\Server\Subuser;

use App\Models\Allocation;
use App\Models\DatabaseHost;
use App\Models\Permission;
use App\Models\Server;
use App\Models\Subuser;
use App\Models\SubuserPreviewSession;
use App\Models\User;
use App\Repositories\Agent\DaemonFileRepository;
use App\Repositories\Agent\DaemonPowerRepository;
use App\Repositories\Agent\DaemonRevocationRepository;
use Illuminate\Support\Carbon;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Mockery\MockInterface;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class SubuserPreviewTest extends ClientApiIntegrationTestCase
{
    public function test_owner_can_start_and_end_a_privacy_safe_preview(): void
    {
        [$owner, $server, $target] = $this->models();
        $response = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target));

        $response->assertCreated()
            ->assertJsonPath('active', true)
            ->assertJsonPath('owned_by_tab', true)
            ->assertJsonPath('session.server_identifier', $server->uuidShort)
            ->assertJsonPath('session.subuser_email', $target->email)
            ->assertJsonMissingPath('session.name_first')
            ->assertJsonMissingPath('session.username');

        $token = $response->json('token');
        $session = SubuserPreviewSession::query()->firstOrFail();

        $this->assertNotSame($token, $session->token_hash);
        $this->withPreviewToken($token)
            ->deleteJson('/api/client/subuser-preview')
            ->assertNoContent();
        $this->assertDatabaseCount('subuser_preview_sessions', 0);
    }

    public function test_only_the_server_owner_can_start_a_preview(): void
    {
        [$owner, $server, $target] = $this->models();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->postJson($this->previewEndpoint($server, $target))
            ->assertNotFound();

        $this->actingAs($owner)
            ->postJson($this->previewEndpoint($server, $target))
            ->assertCreated();
    }

    public function test_only_one_preview_can_exist_and_an_owner_can_replace_it(): void
    {
        [$owner, $server, $target] = $this->models();
        $secondTarget = User::factory()->create();
        Subuser::query()->create([
            'server_id' => $server->id,
            'user_id' => $secondTarget->id,
            'permissions' => [Permission::ACTION_FILE_READ],
        ]);

        $first = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target));
        $first->assertCreated();

        $this->postJson($this->previewEndpoint($server, $secondTarget))
            ->assertConflict()
            ->assertJsonPath('active', true)
            ->assertJsonPath('owned_by_tab', false);

        $replacement = $this->postJson($this->previewEndpoint($server, $secondTarget), ['replace' => true]);
        $replacement->assertCreated()->assertJsonPath('session.subuser_email', $secondTarget->email);

        $this->assertDatabaseCount('subuser_preview_sessions', 1);
        $this->withPreviewToken($first->json('token'))
            ->postJson('/api/client/subuser-preview/heartbeat')
            ->assertForbidden();
    }

    public function test_owner_can_preview_a_subuser_after_updating_permissions(): void
    {
        [$owner, $server, $target] = $this->models();
        $this->mock(DaemonRevocationRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('setNode')->once()->andReturnSelf();
            $mock->shouldReceive('deauthorize')->once();
        });
        $permissions = [
            Permission::ACTION_DATABASE_READ,
            Permission::ACTION_SCHEDULE_READ,
            Permission::ACTION_STARTUP_READ,
        ];

        $this->actingAs($owner)
            ->postJson($this->link($server, "users/$target->uuid"), ['permissions' => $permissions])
            ->assertOk();

        $response = $this->postJson($this->previewEndpoint($server, $target));

        $response->assertCreated()
            ->assertJsonPath('session.permission_count', count($permissions));
        $this->withPreviewToken($response->json('token'))
            ->getJson($this->link($server))
            ->assertOk()
            ->assertJsonPath('meta.user_permissions', [...$permissions, Permission::ACTION_WEBSOCKET_CONNECT]);
    }

    public function test_preview_is_scoped_to_one_server_and_blocks_account_information(): void
    {
        [$owner, $server, $target] = $this->models();
        $otherServer = $this->createServerModel(['owner_id' => $owner->id]);
        $token = $this->actingAs($owner)
            ->postJson($this->previewEndpoint($server, $target))
            ->json('token');

        $this->withPreviewToken($token)
            ->getJson('/api/client')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.uuid', $server->uuid);

        $this->withPreviewToken($token)
            ->getJson($this->link($server))
            ->assertOk()
            ->assertJsonPath('meta.is_server_owner', false)
            ->assertJsonPath('meta.user_permissions', [
                Permission::ACTION_CONTROL_START,
                Permission::ACTION_FILE_CREATE,
                Permission::ACTION_FILE_READ,
                Permission::ACTION_FILE_READ_CONTENT,
                Permission::ACTION_FILE_UPDATE,
                Permission::ACTION_FILE_DELETE,
                Permission::ACTION_WEBSOCKET_CONNECT,
            ]);

        $this->withPreviewToken($token)->getJson($this->link($otherServer))->assertNotFound();
        $this->withPreviewToken($token)->getJson('/api/client/account')->assertForbidden();
    }

    public function test_power_and_file_changes_are_isolated_and_persist_in_the_preview(): void
    {
        [$owner, $server, $target] = $this->models();
        $owner->forceFill(['language' => 'de'])->save();
        $token = $this->actingAs($owner)
            ->postJson($this->previewEndpoint($server, $target))
            ->json('token');

        $this->mock(DaemonPowerRepository::class, fn (MockInterface $mock) => $mock->shouldNotReceive('send'));
        $this->mock(DaemonFileRepository::class, function (MockInterface $mock) use ($server) {
            $mock->expects('setServer')->twice()->withArgs(fn (Server $value) => $value->id === $server->id)->andReturnSelf();
            $mock->expects('getDirectory')->with('/')->andReturn([]);
            $mock->expects('getContent')->with('/live.txt', config('panel.files.max_edit_size'))->andReturn('live data');
            $mock->shouldNotReceive('putContent');
        });

        $this->withPreviewToken($token)
            ->postJson($this->link($server, 'power'), ['signal' => 'start'])
            ->assertOk()
            ->assertJsonPath('status', 'running');

        $this->call(
            'POST',
            $this->link($server, 'files/write').'?file=/preview.txt',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'text/plain',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_SUBUSER_PREVIEW' => $token,
            ],
            'session data'
        )
            ->assertNoContent();

        $this->assertSame(
            'session data',
            SubuserPreviewSession::query()->firstOrFail()->state['files']['/preview.txt']['content'] ?? null
        );

        $this->withPreviewToken($token)
            ->getJson($this->link($server, 'files/list').'?directory=/')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.name', 'preview.txt');

        $this->withPreviewToken($token)
            ->get($this->link($server, 'files/contents').'?file=/preview.txt')
            ->assertOk()
            ->assertSeeText('session data');

        $this->withPreviewToken($token)
            ->postJson('/api/client/subuser-preview/heartbeat')
            ->assertOk()
            ->assertJsonPath('session.power_status', 'running');

        $this->withPreviewToken($token)
            ->putJson($this->link($server, 'files/rename'), [
                'root' => '/',
                'files' => [['from' => 'live.txt', 'to' => 'renamed.txt']],
            ])
            ->assertNoContent();
        $this->withPreviewToken($token)
            ->get($this->link($server, 'files/contents').'?file=/renamed.txt')
            ->assertOk()
            ->assertSeeText('live data');
        $this->withPreviewToken($token)
            ->postJson($this->link($server, 'files/delete'), ['root' => '/', 'files' => ['renamed.txt']])
            ->assertNoContent();
        $this->withPreviewToken($token)
            ->getJson($this->link($server, 'files/download').'?file=/renamed.txt')
            ->assertNotFound()
            ->assertJsonPath('errors.0.detail', 'The requested file does not exist in this preview.');
    }

    public function test_preview_websocket_token_is_read_only(): void
    {
        [$owner, $server, $target] = $this->models();
        $token = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target))->json('token');
        $response = $this->withPreviewToken($token)
            ->getJson($this->link($server, 'websocket'))
            ->assertOk();

        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($server->node->getDecryptedKey())
        );
        $websocketToken = $config->parser()->parse($response->json('data.token'));

        $this->assertSame(
            [Permission::ACTION_WEBSOCKET_CONNECT],
            $websocketToken->claims()->get('permissions')
        );
    }

    public function test_expired_preview_is_deleted(): void
    {
        [$owner, $server, $target] = $this->models();
        $response = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target));
        SubuserPreviewSession::query()->update(['expires_at' => Carbon::now()->subMinute()]);

        $this->withPreviewToken($response->json('token'))
            ->postJson('/api/client/subuser-preview/heartbeat')
            ->assertConflict();
        $this->assertDatabaseCount('subuser_preview_sessions', 0);
    }

    public function test_resource_changes_persist_only_in_the_preview_session(): void
    {
        [$owner, $server, $target] = $this->models();
        $originalName = $server->name;
        $server->forceFill([
            'database_limit' => 10,
            'allocation_limit' => 10,
            'backup_limit' => 10,
        ])->save();
        $server->subusers()->where('user_id', $target->id)->update(['permissions' => [
            Permission::ACTION_CONTROL_CONSOLE,
            Permission::ACTION_DATABASE_CREATE,
            Permission::ACTION_DATABASE_READ,
            Permission::ACTION_DATABASE_UPDATE,
            Permission::ACTION_DATABASE_DELETE,
            Permission::ACTION_DATABASE_VIEW_PASSWORD,
            Permission::ACTION_SCHEDULE_CREATE,
            Permission::ACTION_SCHEDULE_READ,
            Permission::ACTION_SCHEDULE_UPDATE,
            Permission::ACTION_SCHEDULE_DELETE,
            Permission::ACTION_BACKUP_CREATE,
            Permission::ACTION_BACKUP_READ,
            Permission::ACTION_BACKUP_DELETE,
            Permission::ACTION_BACKUP_RESTORE,
            Permission::ACTION_ALLOCATION_CREATE,
            Permission::ACTION_ALLOCATION_READ,
            Permission::ACTION_ALLOCATION_UPDATE,
            Permission::ACTION_ALLOCATION_DELETE,
            Permission::ACTION_SETTINGS_RENAME,
            Permission::ACTION_STARTUP_READ,
            Permission::ACTION_STARTUP_UPDATE,
            Permission::ACTION_STARTUP_DOCKER_IMAGE,
            Permission::ACTION_WEBSOCKET_CONNECT,
        ]]);
        DatabaseHost::factory()->create(['node_id' => $server->node_id]);
        $availableAllocation = Allocation::factory()->create(['node_id' => $server->node_id, 'server_id' => null]);
        $variable = $server->variables()->where('user_editable', true)->firstOrFail();
        $originalVariableValue = $variable->server_value;
        $token = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target))->json('token');

        $database = $this->withPreviewToken($token)->postJson($this->link($server, 'databases'), [
            'database' => 'preview_database',
            'remote' => '127.0.0.1',
        ])->assertCreated();
        $databaseId = $database->json('attributes.id');
        $this->withPreviewToken($token)
            ->postJson($this->link($server, "databases/$databaseId/rotate-password"))
            ->assertOk()
            ->assertJsonPath('attributes.name', 'preview_database');

        $schedule = $this->withPreviewToken($token)->postJson($this->link($server, 'schedules'), [
            'name' => 'Preview schedule',
            'minute' => '*/5',
            'hour' => '*',
            'day_of_month' => '*',
            'month' => '*',
            'day_of_week' => '*',
            'is_active' => true,
            'only_when_online' => false,
        ])->assertCreated();
        $scheduleId = $schedule->json('attributes.id');
        $this->withPreviewToken($token)
            ->postJson($this->link($server, "schedules/$scheduleId/tasks"), [
                'action' => 'command',
                'payload' => 'say preview',
                'time_offset' => 0,
                'continue_on_failure' => false,
            ])->assertCreated();
        $this->withPreviewToken($token)
            ->getJson($this->link($server, "schedules/$scheduleId"))
            ->assertOk()
            ->assertJsonCount(1, 'attributes.relationships.tasks.data');

        $this->withPreviewToken($token)
            ->postJson($this->link($server, 'backups'), ['name' => 'Preview backup', 'is_locked' => true])
            ->assertCreated();
        $this->withPreviewToken($token)
            ->postJson($this->link($server, 'network/allocations'))
            ->assertCreated()
            ->assertJsonPath('attributes.id', $availableAllocation->id);
        $this->withPreviewToken($token)
            ->postJson($this->link($server, 'settings/rename'), ['name' => 'Session-only name'])
            ->assertNoContent();
        $this->withPreviewToken($token)
            ->putJson($this->link($server, 'startup/variable'), [
                'key' => $variable->env_variable,
                'value' => $variable->default_value,
            ])
            ->assertOk()
            ->assertJsonPath('attributes.server_value', $variable->default_value);
        $this->assertSame(
            $variable->default_value,
            SubuserPreviewSession::query()->firstOrFail()->state['startup']['variables'][$variable->env_variable]
        );

        $this->withPreviewToken($token)
            ->getJson($this->link($server, 'databases'))
            ->assertOk()
            ->assertJsonPath('data.0.attributes.name', 'preview_database');
        $this->withPreviewToken($token)
            ->getJson($this->link($server, 'schedules'))
            ->assertOk()
            ->assertJsonPath('data.0.attributes.name', 'Preview schedule');
        $this->withPreviewToken($token)
            ->getJson($this->link($server, 'backups'))
            ->assertOk()
            ->assertJsonPath('data.0.attributes.name', 'Preview backup');
        $this->withPreviewToken($token)
            ->getJson($this->link($server))
            ->assertOk()
            ->assertJsonPath('attributes.name', 'Session-only name');
        $this->withPreviewToken($token)
            ->getJson($this->link($server, 'startup'))
            ->assertOk()
            ->assertJsonFragment([
                'env_variable' => $variable->env_variable,
                'server_value' => $variable->default_value,
            ]);

        $this->assertSame($originalName, $server->fresh()->name);
        $this->assertDatabaseCount('databases', 0);
        $this->assertDatabaseCount('schedules', 0);
        $this->assertDatabaseCount('backups', 0);
        $this->assertNull($availableAllocation->fresh()->server_id);
        $this->assertSame($originalVariableValue, $server->fresh()->variables()->findOrFail($variable->id)->server_value);
    }

    public function test_preview_mutations_reject_malformed_requests_without_changing_session_state(): void
    {
        [$owner, $server, $target] = $this->models();
        $server->subusers()->where('user_id', $target->id)->update(['permissions' => [
            Permission::ACTION_CONTROL_START,
            Permission::ACTION_DATABASE_CREATE,
            Permission::ACTION_SCHEDULE_CREATE,
            Permission::ACTION_SETTINGS_RENAME,
            Permission::ACTION_USER_CREATE,
            Permission::ACTION_FILE_CREATE,
            Permission::ACTION_WEBSOCKET_CONNECT,
        ]]);
        $token = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target))->json('token');

        $this->withPreviewToken($token)->postJson($this->link($server, 'power'), ['signal' => 'explode'])->assertUnprocessable();
        $this->withPreviewToken($token)->postJson($this->link($server, 'databases'), [
            'database' => 'x',
            'remote' => 'not allowed!',
        ])->assertUnprocessable();
        $this->withPreviewToken($token)->postJson($this->link($server, 'schedules'), [])->assertUnprocessable();
        $this->withPreviewToken($token)->postJson($this->link($server, 'settings/rename'), ['name' => ''])->assertUnprocessable();
        $this->withPreviewToken($token)->postJson($this->link($server, 'users'), ['email' => 'invalid'])->assertUnprocessable();
        $this->withPreviewToken($token)->postJson($this->link($server, 'files/create-folder'), ['root' => '/'])->assertUnprocessable();

        $state = SubuserPreviewSession::query()->firstOrFail()->state;
        $this->assertCount(2, $state);
        $this->assertNull($state['power_status']);
        $this->assertSame([], $state['files']);
    }

    public function test_schedule_tasks_require_the_underlying_action_permission(): void
    {
        [$owner, $server, $target] = $this->models();
        $server->subusers()->where('user_id', $target->id)->update(['permissions' => [
            Permission::ACTION_SCHEDULE_CREATE,
            Permission::ACTION_SCHEDULE_READ,
            Permission::ACTION_SCHEDULE_UPDATE,
            Permission::ACTION_WEBSOCKET_CONNECT,
        ]]);
        $token = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target))->json('token');
        $schedule = $this->withPreviewToken($token)->postJson($this->link($server, 'schedules'), [
            'name' => 'Permission check',
            'minute' => '*',
            'hour' => '*',
            'day_of_month' => '*',
            'month' => '*',
            'day_of_week' => '*',
            'is_active' => true,
            'only_when_online' => false,
        ])->assertCreated();

        $this->withPreviewToken($token)
            ->postJson($this->link($server, 'schedules/'.$schedule->json('attributes.id').'/tasks'), [
                'action' => 'command',
                'payload' => 'say should-not-run',
                'time_offset' => 0,
                'continue_on_failure' => false,
            ])
            ->assertForbidden();
        $this->withPreviewToken($token)
            ->getJson($this->link($server, 'schedules/'.$schedule->json('attributes.id')))
            ->assertOk()
            ->assertJsonCount(0, 'attributes.relationships.tasks.data');
    }

    public function test_locked_primary_and_preview_identity_safeguards_are_enforced(): void
    {
        [$owner, $server, $target] = $this->models();
        $server->forceFill(['backup_limit' => 10])->save();
        $server->subusers()->where('user_id', $target->id)->update(['permissions' => [
            Permission::ACTION_ALLOCATION_DELETE,
            Permission::ACTION_BACKUP_CREATE,
            Permission::ACTION_BACKUP_DELETE,
            Permission::ACTION_USER_UPDATE,
            Permission::ACTION_USER_DELETE,
            Permission::ACTION_WEBSOCKET_CONNECT,
        ]]);
        $token = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target))->json('token');
        $backup = $this->withPreviewToken($token)->postJson($this->link($server, 'backups'), [
            'name' => 'Locked preview backup',
            'is_locked' => true,
        ])->assertCreated();

        $this->withPreviewToken($token)
            ->deleteJson($this->link($server, 'backups/'.$backup->json('attributes.uuid')))
            ->assertConflict();
        $this->withPreviewToken($token)
            ->deleteJson($this->link($server, 'network/allocations/'.$server->allocation_id))
            ->assertConflict();
        $this->withPreviewToken($token)
            ->postJson($this->link($server, 'users/'.$target->uuid), [
                'permissions' => [Permission::ACTION_FILE_READ],
            ])
            ->assertForbidden();
        $this->withPreviewToken($token)
            ->deleteJson($this->link($server, 'users/'.$target->uuid))
            ->assertForbidden();
    }

    public function test_dashboard_overlays_are_removed_when_the_preview_ends(): void
    {
        [$owner, $server, $target] = $this->models();
        $originalName = $server->name;
        $server->subusers()->where('user_id', $target->id)->update(['permissions' => [
            Permission::ACTION_SETTINGS_RENAME,
            Permission::ACTION_WEBSOCKET_CONNECT,
        ]]);
        $token = $this->actingAs($owner)->postJson($this->previewEndpoint($server, $target))->json('token');

        $this->withPreviewToken($token)
            ->postJson($this->link($server, 'settings/rename'), ['name' => 'Dashboard-only preview name'])
            ->assertNoContent();
        $this->withPreviewToken($token)
            ->getJson('/api/client')
            ->assertOk()
            ->assertJsonPath('data.0.attributes.name', 'Dashboard-only preview name');
        $this->withPreviewToken($token)->deleteJson('/api/client/subuser-preview')->assertNoContent();

        $this->assertDatabaseCount('subuser_preview_sessions', 0);
        $this->assertSame($originalName, $server->fresh()->name);
    }

    private function models(): array
    {
        $owner = User::factory()->create();
        $server = $this->createServerModel(['owner_id' => $owner->id]);
        $target = User::factory()->create([
            'name_first' => 'Private',
            'name_last' => 'Person',
            'username' => 'private-user',
        ]);

        Subuser::query()->create([
            'server_id' => $server->id,
            'user_id' => $target->id,
            'permissions' => [
                Permission::ACTION_CONTROL_START,
                Permission::ACTION_FILE_CREATE,
                Permission::ACTION_FILE_READ,
                Permission::ACTION_FILE_READ_CONTENT,
                Permission::ACTION_FILE_UPDATE,
                Permission::ACTION_FILE_DELETE,
                Permission::ACTION_WEBSOCKET_CONNECT,
            ],
        ]);

        return [$owner, $server, $target];
    }

    private function previewEndpoint(Server $server, User $target): string
    {
        return $this->link($server, "users/$target->uuid/preview");
    }

    private function withPreviewToken(string $token): static
    {
        return $this->withHeader('X-Subuser-Preview', $token);
    }
}
