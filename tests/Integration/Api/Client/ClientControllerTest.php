<?php

namespace Tests\Integration\Api\Client;

use App\Filament\Resources\Nests\Eggs\Pages\EditEgg;
use App\Models\Allocation;
use App\Models\Permission;
use App\Models\Server;
use App\Models\Subuser;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;

class ClientControllerTest extends ClientApiIntegrationTestCase
{
    public function test_dashboard_loads_shared_egg_images_once_without_loading_image_bytes(): void
    {
        $user = User::factory()->create(['root_admin' => true]);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $server = $this->createServerModel(['user_id' => $user->id]);
        $egg = $server->egg;
        $contents = str_pad(file_get_contents(public_path('reviactyl/icon.png')), 10 * 1024 * 1024, "\0");
        Livewire::test(EditEgg::class, ['record' => $egg->id])
            ->set('eggImageUpload', UploadedFile::fake()->createWithContent('large.png', $contents))
            ->call('save')->assertHasNoFormErrors();
        for ($i = 1; $i < 50; $i++) {
            $this->createServerModel(['user_id' => $user->id, 'egg_id' => $egg->id]);
        }

        DB::enableQueryLog();
        try {
            $response = $this->getJson('/api/client?include=category')->assertOk()->assertJsonCount(50, 'data');
            $queries = DB::getQueryLog();
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
        $eggQueries = array_filter($queries, fn ($query) => preg_match('/from ["`]?eggs["`]?\s/i', $query['query']));
        $this->assertCount(1, $eggQueries);
        $this->assertStringNotContainsString('egg_images', implode(' ', array_column($queries, 'query')));
        $this->assertLessThan(10000, strlen($egg->fresh()->toJson()));
        $url = $egg->fresh()->image_url;
        foreach ($response->json('data') as $entry) {
            $this->assertSame($url, $entry['attributes']['egg_image']);
        }
    }

    /**
     * Test that only the servers a logged-in user is assigned to are returned by the
     * API endpoint. Obviously there are cases such as being an administrator or being
     * a subuser, but for this test we just want to test a basic scenario and pretend
     * subusers do not exist at all.
     */
    public function test_only_logged_in_users_servers_are_returned()
    {
        /** @var User[] $users */
        $users = User::factory()->times(3)->create();

        /** @var Server[] $servers */
        $servers = [
            $this->createServerModel(['user_id' => $users[0]->id]),
            $this->createServerModel(['user_id' => $users[1]->id]),
            $this->createServerModel(['user_id' => $users[2]->id]),
        ];

        $response = $this->actingAs($users[0])->getJson('/api/client');

        $response->assertOk();
        $response->assertJsonPath('object', 'list');
        $response->assertJsonPath('data.0.object', Server::RESOURCE_NAME);
        $response->assertJsonPath('data.0.attributes.identifier', $servers[0]->uuidShort);
        $response->assertJsonPath('data.0.attributes.server_owner', true);
        $response->assertJsonPath('meta.pagination.total', 1);
        $response->assertJsonPath('meta.pagination.per_page', 50);
    }

    /**
     * Test that using ?filter[*]=name|uuid returns any server matching that name or UUID
     * with the search filters.
     */
    public function test_servers_are_filtered_using_name_and_uuid_information()
    {
        /** @var User[] $users */
        $users = User::factory()->times(2)->create();
        $users[0]->update(['root_admin' => true]);

        /** @var Server[] $servers */
        $servers = [
            $this->createServerModel(['user_id' => $users[0]->id, 'name' => 'Julia']),
            $this->createServerModel(['user_id' => $users[1]->id, 'uuidShort' => '12121212', 'name' => 'Janice']),
            $this->createServerModel(['user_id' => $users[1]->id, 'uuid' => Uuid::uuid4()->toString(), 'external_id' => 'ext123', 'name' => 'Julia']),
            $this->createServerModel(['user_id' => $users[1]->id, 'uuid' => Uuid::uuid4()->toString(), 'name' => 'Jennifer']),
        ];

        $this->actingAs($users[1])->getJson('/api/client?filter[*]=Julia')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $servers[2]->uuidShort);

        $this->actingAs($users[1])->getJson('/api/client?filter[*]=ext123')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $servers[2]->uuidShort);

        $this->actingAs($users[1])->getJson('/api/client?filter[*]=ext123')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $servers[2]->uuidShort);

        $this->actingAs($users[1])->getJson('/api/client?filter[*]=12121212')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $servers[1]->uuidShort);

        $this->actingAs($users[1])->getJson("/api/client?filter[*]={$servers[2]->uuidShort}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $servers[2]->uuidShort);

        $this->actingAs($users[1])->getJson('/api/client?filter[*]=88788878-abcd')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($users[0])->getJson('/api/client?filter[*]=Julia&type=admin-all')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $servers[0]->uuidShort)
            ->assertJsonPath('data.1.attributes.identifier', $servers[2]->uuidShort);
    }

    /**
     * Test that using ?filter[*]=:25565 or ?filter[*]=192.168.1.1:25565 returns only those servers
     * with the same allocation for the given user.
     */
    public function test_servers_are_filtered_using_allocation_information()
    {
        /** @var User $user */
        /** @var Server $server */
        [$user, $server] = $this->generateTestAccount();
        $server2 = $this->createServerModel(['user_id' => $user->id, 'node_id' => $server->node_id]);

        $allocation = Allocation::factory()->create(['node_id' => $server->node_id, 'server_id' => $server->id, 'ip' => '192.168.1.1', 'port' => 25565]);
        $allocation2 = Allocation::factory()->create(['node_id' => $server->node_id, 'server_id' => $server2->id, 'ip' => '192.168.1.1', 'port' => 25570]);

        $server->update(['allocation_id' => $allocation->id]);
        $server2->update(['allocation_id' => $allocation2->id]);

        $server->refresh();
        $server2->refresh();

        $this->actingAs($user)->getJson('/api/client?filter[*]=192.168.1.1')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $server->uuidShort)
            ->assertJsonPath('data.1.attributes.identifier', $server2->uuidShort);

        $this->actingAs($user)->getJson('/api/client?filter[*]=192.168.1.1:25565')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $server->uuidShort);

        $this->actingAs($user)->getJson('/api/client?filter[*]=:25570')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $server2->uuidShort);

        $this->actingAs($user)->getJson('/api/client?filter[*]=:255')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.attributes.identifier', $server->uuidShort)
            ->assertJsonPath('data.1.attributes.identifier', $server2->uuidShort);
    }

    /**
     * Test that servers where the user is a subuser are returned by default in the API call.
     */
    public function test_servers_user_is_a_subuser_of_are_returned()
    {
        /** @var User[] $users */
        $users = User::factory()->times(3)->create();
        $servers = [
            $this->createServerModel(['user_id' => $users[0]->id]),
            $this->createServerModel(['user_id' => $users[1]->id]),
            $this->createServerModel(['user_id' => $users[2]->id]),
        ];

        // Set user 0 as a subuser of server 1. Thus, we should get two servers
        // back in the response when making the API call as user 0.
        Subuser::query()->create([
            'user_id' => $users[0]->id,
            'server_id' => $servers[1]->id,
            'permissions' => [Permission::ACTION_WEBSOCKET_CONNECT],
        ]);

        $response = $this->actingAs($users[0])->getJson('/api/client');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('data.0.attributes.server_owner', true);
        $response->assertJsonPath('data.0.attributes.identifier', $servers[0]->uuidShort);
        $response->assertJsonPath('data.1.attributes.server_owner', false);
        $response->assertJsonPath('data.1.attributes.identifier', $servers[1]->uuidShort);
    }

    /**
     * Returns only servers that the user owns, not servers they are a subuser of.
     */
    public function test_filter_only_owner_servers()
    {
        /** @var User[] $users */
        $users = User::factory()->times(3)->create();
        $servers = [
            $this->createServerModel(['user_id' => $users[0]->id]),
            $this->createServerModel(['user_id' => $users[1]->id]),
            $this->createServerModel(['user_id' => $users[2]->id]),
        ];

        // Set user 0 as a subuser of server 1. Thus, we should get two servers
        // back in the response when making the API call as user 0.
        Subuser::query()->create([
            'user_id' => $users[0]->id,
            'server_id' => $servers[1]->id,
            'permissions' => [Permission::ACTION_WEBSOCKET_CONNECT],
        ]);

        $response = $this->actingAs($users[0])->getJson('/api/client?type=owner');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.attributes.server_owner', true);
        $response->assertJsonPath('data.0.attributes.identifier', $servers[0]->uuidShort);
    }

    /**
     * Tests that the permissions from the Panel are returned correctly.
     */
    public function test_permissions_are_returned()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/client/permissions')
            ->assertOk()
            ->assertJson([
                'object' => 'system_permissions',
                'attributes' => [
                    'permissions' => Permission::permissions()->toArray(),
                ],
            ]);
    }

    /**
     * Test that only servers a user can access because they are an administrator are returned. This
     * will always exclude any servers they can see because they're the owner or a subuser of the server.
     */
    public function test_only_admin_level_servers_are_returned()
    {
        /** @var User[] $users */
        $users = User::factory()->times(4)->create();
        $users[0]->update(['root_admin' => true]);

        $servers = [
            $this->createServerModel(['user_id' => $users[0]->id]),
            $this->createServerModel(['user_id' => $users[1]->id]),
            $this->createServerModel(['user_id' => $users[2]->id]),
            $this->createServerModel(['user_id' => $users[3]->id]),
        ];

        Subuser::query()->create([
            'user_id' => $users[0]->id,
            'server_id' => $servers[1]->id,
            'permissions' => [Permission::ACTION_WEBSOCKET_CONNECT],
        ]);

        // Only servers 2 & 3 (0 indexed) should be returned by the API at this point. The user making
        // the request is the owner of server 0, and a subuser of server 1, so they should be excluded.
        $response = $this->actingAs($users[0])->getJson('/api/client?type=admin');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.attributes.server_owner', false);
        $response->assertJsonPath('data.0.attributes.identifier', $servers[2]->uuidShort);
        $response->assertJsonPath('data.1.attributes.server_owner', false);
        $response->assertJsonPath('data.1.attributes.identifier', $servers[3]->uuidShort);
    }

    /**
     * Test that all servers a user can access as an admin are returned if using ?filter=admin-all.
     */
    public function test_all_servers_are_returned_to_admin()
    {
        /** @var User[] $users */
        $users = User::factory()->times(4)->create();
        $users[0]->update(['root_admin' => true]);

        $servers = [
            $this->createServerModel(['user_id' => $users[0]->id]),
            $this->createServerModel(['user_id' => $users[1]->id]),
            $this->createServerModel(['user_id' => $users[2]->id]),
            $this->createServerModel(['user_id' => $users[3]->id]),
        ];

        Subuser::query()->create([
            'user_id' => $users[0]->id,
            'server_id' => $servers[1]->id,
            'permissions' => [Permission::ACTION_WEBSOCKET_CONNECT],
        ]);

        // All servers should be returned.
        $response = $this->actingAs($users[0])->getJson('/api/client?type=admin-all');

        $response->assertOk();
        $response->assertJsonCount(4, 'data');
    }

    /**
     * Test that no servers get returned if the user requests all admin level servers by using
     * ?type=admin or ?type=admin-all in the request.
     */
    #[DataProvider('filterTypeDataProvider')]
    public function test_no_servers_are_returned_if_admin_filter_is_passed_by_regular_user(string $type)
    {
        /** @var User[] $users */
        $users = User::factory()->times(3)->create();

        $this->createServerModel(['user_id' => $users[0]->id]);
        $this->createServerModel(['user_id' => $users[1]->id]);
        $this->createServerModel(['user_id' => $users[2]->id]);

        $response = $this->actingAs($users[0])->getJson('/api/client?type='.$type);

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    /**
     * Test that a subuser without the allocation.read permission is only able to see the primary
     * allocation for the server.
     */
    public function test_only_primary_allocation_is_returned_to_subuser()
    {
        /** @var Server $server */
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_WEBSOCKET_CONNECT]);
        $server->allocation->notes = 'Test notes';
        $server->allocation->save();

        Allocation::factory()->times(2)->create([
            'node_id' => $server->node_id,
            'server_id' => $server->id,
        ]);

        $server->refresh();
        $response = $this->actingAs($user)->getJson('/api/client');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.attributes.server_owner', false);
        $response->assertJsonPath('data.0.attributes.uuid', $server->uuid);
        $response->assertJsonCount(1, 'data.0.attributes.relationships.allocations.data');
        $response->assertJsonPath('data.0.attributes.relationships.allocations.data.0.attributes.id', $server->allocation->id);
        $response->assertJsonPath('data.0.attributes.relationships.allocations.data.0.attributes.notes', null);
    }

    public static function filterTypeDataProvider(): array
    {
        return [['admin'], ['admin-all']];
    }
}
