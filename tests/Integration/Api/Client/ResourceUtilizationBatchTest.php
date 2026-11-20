<?php

namespace Tests\Integration\Api\Client;

use App\Exceptions\Http\Connection\DaemonConnectionException;
use App\Models\Permission;
use App\Models\Server;
use App\Models\Subuser;
use App\Models\User;
use App\Repositories\Agent\DaemonServerRepository;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Psr7\Request;
use Mockery\MockInterface;

class ResourceUtilizationBatchTest extends ClientApiIntegrationTestCase
{
    /** @var string[] UUIDs the daemon was asked about, in order. */
    private array $asked = [];

    /** @var array<string, \Throwable> */
    private array $failures = [];

    protected function setUp(): void
    {
        parent::setUp();

        $current = null;
        $this->app->instance(DaemonServerRepository::class, \Mockery::mock(DaemonServerRepository::class, function (MockInterface $mock) use (&$current) {
            $mock->allows('setServer')->andReturnUsing(function (Server $server) use (&$current, $mock) {
                $current = $server;

                return $mock;
            });
            $mock->allows('getDetails')->andReturnUsing(function () use (&$current) {
                $this->asked[] = $current->uuid;

                return ['state' => 'running', 'utilization' => ['memory_bytes' => strlen($current->name)]];
            });
            $mock->allows('getDetailsAsync')->andReturnUsing(function () use (&$current) {
                $this->asked[] = $current->uuid;
                if (isset($this->failures[$current->uuid])) {
                    return Create::rejectionFor($this->failures[$current->uuid]);
                }

                return Create::promiseFor(['state' => 'running', 'utilization' => ['memory_bytes' => strlen($current->name)]]);
            });
        }));
    }

    private function batch(array $servers)
    {
        return $this->getJson('/api/client/resources?servers='.implode(',', array_map(fn (Server $server) => $server->uuid, $servers)));
    }

    public function test_returns_usage_for_every_accessible_server_keyed_by_uuid(): void
    {
        $user = User::factory()->create();
        $owned = $this->createServerModel(['owner_id' => $user->id, 'name' => 'owned']);
        $shared = $this->createServerModel(['name' => 'shared!']);
        Subuser::query()->create(['user_id' => $user->id, 'server_id' => $shared->id, 'permissions' => [Permission::ACTION_WEBSOCKET_CONNECT]]);
        $foreign = $this->createServerModel(['name' => 'foreign']);

        $response = $this->actingAs($user)->batch([$owned, $shared, $foreign])->assertOk();

        $response->assertJsonPath('object', 'list');
        $response->assertJsonPath("data.{$owned->uuid}.object", 'stats');
        $response->assertJsonPath("data.{$owned->uuid}.attributes.current_state", 'running');
        $response->assertJsonPath("data.{$owned->uuid}.attributes.resources.memory_bytes", 5);
        $response->assertJsonPath("data.{$shared->uuid}.attributes.resources.memory_bytes", 7);
        $response->assertJsonMissingPath("data.{$foreign->uuid}");
        $this->assertEqualsCanonicalizing([$owned->uuid, $shared->uuid], $this->asked);
    }

    public function test_matches_the_single_server_endpoint(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $this->actingAs($user);

        $single = $this->getJson("/api/client/servers/{$server->uuid}/resources")->assertOk()->json();
        cache()->flush();

        $this->assertSame($single, $this->batch([$server])->assertOk()->json("data.{$server->uuid}"));
    }

    public function test_reuses_cached_usage_between_requests(): void
    {
        [$user, $server] = $this->generateTestAccount();

        $this->actingAs($user)->batch([$server])->assertOk();
        $this->actingAs($user)->getJson("/api/client/servers/{$server->uuid}/resources")->assertOk();
        $this->actingAs($user)->batch([$server])->assertOk();

        $this->assertSame([$server->uuid], $this->asked);
    }

    public function test_reports_servers_that_cannot_give_usage_without_failing_the_rest(): void
    {
        $user = User::factory()->create();
        $healthy = $this->createServerModel(['owner_id' => $user->id]);
        $offline = $this->createServerModel(['owner_id' => $user->id]);
        $suspended = $this->createServerModel(['owner_id' => $user->id, 'status' => Server::STATUS_SUSPENDED]);
        $this->failures[$offline->uuid] = new DaemonConnectionException(new ConnectException('down', new Request('GET', '/')));

        $response = $this->actingAs($user)->batch([$healthy, $offline, $suspended])->assertOk();

        $response->assertJsonPath("data.{$healthy->uuid}.object", 'stats');
        $response->assertJsonPath("data.{$offline->uuid}.errors.0.status", '504');
        $response->assertJsonPath("data.{$suspended->uuid}.errors.0.status", '409');
        $this->assertNotContains($suspended->uuid, $this->asked);
    }

    public function test_root_admins_can_read_any_server(): void
    {
        $server = $this->createServerModel();

        $this->actingAs(User::factory()->create(['root_admin' => true]))
            ->batch([$server])
            ->assertOk()
            ->assertJsonPath("data.{$server->uuid}.object", 'stats');
    }

    public function test_validates_and_limits_the_requested_servers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->getJson('/api/client/resources')->assertStatus(422);
        $this->actingAs($user)->getJson('/api/client/resources?servers=not-a-uuid,../../etc')
            ->assertOk()
            ->assertExactJson(['object' => 'list', 'data' => []]);

        $servers = collect(range(1, 52))->map(fn () => $this->createServerModel(['owner_id' => $user->id]));
        $this->actingAs($user)->batch($servers->all())->assertOk();
        $this->assertCount(50, $this->asked);
    }
}
