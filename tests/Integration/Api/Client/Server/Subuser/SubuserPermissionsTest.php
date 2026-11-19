<?php

namespace Tests\Integration\Api\Client\Server\Subuser;

use App\Models\Subuser;
use App\Models\User;
use App\Repositories\Agent\DaemonRevocationRepository;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class SubuserPermissionsTest extends ClientApiIntegrationTestCase
{
    public function test_unchanged_permissions_repair_existing_json_objects_without_revoking_access(): void
    {
        [$owner, $server] = $this->generateTestAccount();
        $user = User::factory()->create();
        $subuser = Subuser::factory()->for($user)->for($server)->create([
            'permissions' => [0 => 'control.console', 2 => 'file.read', 3 => 'websocket.connect'],
        ]);

        $this->mock(DaemonRevocationRepository::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('setNode');
        });

        $response = $this->actingAs($owner)->postJson($this->link($server).'/users/'.$user->uuid, [
            'permissions' => ['control.console', 'control.console', 'file.read'],
        ])->assertOk();

        $expected = ['control.console', 'file.read', 'websocket.connect'];
        $this->assertSame($expected, json_decode($response->getContent())->attributes->permissions);
        $this->assertSame($expected, $subuser->refresh()->permissions);
        $this->assertSame($expected, json_decode($subuser->getRawOriginal('permissions')));
    }

    #[DataProvider('permissionLists')]
    public function test_permissions_are_stored_and_returned_as_json_lists(bool $update, array $permissions, array $expected): void
    {
        [$owner, $server] = $this->generateTestAccount();
        $user = User::factory()->create();
        $endpoint = $this->link($server).'/users';

        if ($update) {
            Subuser::factory()->for($user)->for($server)->create([
                'permissions' => ['control.start', 'websocket.connect'],
            ]);
            $endpoint .= '/'.$user->uuid;

            $this->mock(DaemonRevocationRepository::class, function (MockInterface $mock) {
                $mock->expects('setNode->deauthorize')->once()->andReturnUndefined();
            });
        }

        $response = $this->actingAs($owner)->postJson($endpoint, [
            'email' => $user->email,
            'permissions' => $permissions,
        ])->assertOk();

        $this->assertSame($expected, json_decode($response->getContent())->attributes->permissions);

        $subuser = Subuser::query()->where('server_id', $server->id)->where('user_id', $user->id)->firstOrFail();
        $this->assertSame($expected, $subuser->permissions);
        $this->assertSame($expected, json_decode($subuser->getRawOriginal('permissions')));
    }

    public static function permissionLists(): array
    {
        $cases = [
            'duplicates' => [
                ['control.console', 'control.console', 'file.read'],
                ['control.console', 'file.read', 'websocket.connect'],
            ],
            'filtered and duplicate permissions' => [
                ['unknown.permission', 'control.console', 'control.console', 'file.read', 'invalid.permission'],
                ['control.console', 'file.read', 'websocket.connect'],
            ],
            'existing websocket permission' => [
                ['websocket.connect', 'file.read', 'websocket.connect', 'control.console'],
                ['websocket.connect', 'file.read', 'control.console'],
            ],
            'only filtered permissions' => [
                ['unknown.permission', 'invalid.permission'],
                ['websocket.connect'],
            ],
        ];

        $data = [];
        foreach ([false, true] as $update) {
            foreach ($cases as $name => [$permissions, $expected]) {
                $data[($update ? 'update ' : 'create ').$name] = [$update, $permissions, $expected];
            }
        }

        return $data;
    }
}
