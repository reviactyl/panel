<?php

namespace Tests\Integration\Api\Client\Server;

use App\Models\Permission;
use App\Models\Server;
use App\Models\ServerCategory;
use App\Models\ServerCategoryAssignment;
use App\Models\Subuser;
use App\Models\User;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class ClientServerCategoryTest extends ClientApiIntegrationTestCase
{
    /** @var Server */
    private $server;

    /** @var User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->server = $this->createServerModel(['user_id' => $this->user->id]);
    }

    public function test_user_can_list_categories()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);
        $otherCategory = ServerCategory::factory()->create(); // different user (default factory)

        $response = $this->actingAs($this->user)->getJson('/api/client/account/categories');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.uuid', $category->uuid);
    }

    public function test_user_can_create_category()
    {
        $response = $this->actingAs($this->user)->postJson('/api/client/account/categories', [
            'name' => 'Test Category',
            'description' => 'A test category',
            'color' => '#ff0000',
        ]);

        $response->assertOk()
            ->assertJsonPath('attributes.name', 'Test Category')
            ->assertJsonPath('attributes.color', '#ff0000');

        $this->assertDatabaseHas('server_categories', [
            'user_id' => $this->user->id,
            'name' => 'Test Category',
            'color' => '#ff0000',
        ]);
    }

    public function test_user_can_update_category()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson("/api/client/account/categories/{$category->uuid}", [
            'name' => 'Updated Name',
        ]);

        $response->assertOk()
            ->assertJsonPath('attributes.name', 'Updated Name');

        $this->assertDatabaseHas('server_categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_can_delete_category()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/client/account/categories/{$category->uuid}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('server_categories', ['id' => $category->id]);
    }

    public function test_server_response_includes_category()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);
        ServerCategoryAssignment::query()->create([
            'server_id' => $this->server->id,
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/client/servers/{$this->server->uuid}?include=category");

        $response->assertOk()
            ->assertJsonPath('attributes.relationships.category.attributes.uuid', $category->uuid);
    }

    public function test_filter_servers_by_category()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);
        $server1 = $this->createServerModel(['user_id' => $this->user->id]);
        $server2 = $this->createServerModel(['user_id' => $this->user->id]);
        ServerCategoryAssignment::query()->create([
            'server_id' => $server1->id,
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        // Filter by category
        $response = $this->actingAs($this->user)->getJson("/api/client?filter[category_uuid]={$category->uuid}");
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.uuid', $server1->uuid);

        // Filter by null (uncategorized)
        $response = $this->actingAs($this->user)->getJson('/api/client?filter[category_uuid]=null');
        // Note: ClientController might need to handle 'null' string?
        // My implementation handles `is_null($value) || $value === 'null'`.
        $response->assertOk()
            ->assertJsonCount(2, 'data'); // Wait, $this->server (from setup) + server2? $this->server has no category by default?

        // $this->server created in setUp has null category.
        // So server2 + this->server = 2.
    }

    public function test_owner_and_subuser_category_assignments_are_isolated(): void
    {
        $subuser = User::factory()->create();
        Subuser::query()->create([
            'user_id' => $subuser->id,
            'server_id' => $this->server->id,
            'permissions' => [Permission::ACTION_SETTINGS_RENAME],
        ]);

        $ownerCategory = ServerCategory::factory()->create(['user_id' => $this->user->id]);
        $subuserCategory = ServerCategory::factory()->create(['user_id' => $subuser->id]);

        $this->actingAs($this->user)
            ->putJson("/api/client/servers/{$this->server->uuid}/settings/category", [
                'category' => $ownerCategory->uuid,
            ])
            ->assertNoContent();

        $this->actingAs($subuser)
            ->putJson("/api/client/servers/{$this->server->uuid}/settings/category", [
                'category' => $subuserCategory->uuid,
            ])
            ->assertNoContent();

        $this->actingAs($this->user)
            ->getJson("/api/client/servers/{$this->server->uuid}?include=category")
            ->assertOk()
            ->assertJsonPath('attributes.relationships.category.attributes.uuid', $ownerCategory->uuid);

        $this->actingAs($subuser)
            ->getJson("/api/client/servers/{$this->server->uuid}?include=category")
            ->assertOk()
            ->assertJsonPath('attributes.relationships.category.attributes.uuid', $subuserCategory->uuid);
    }

    public function test_subuser_cannot_assign_another_users_category(): void
    {
        $subuser = User::factory()->create();
        Subuser::query()->create([
            'user_id' => $subuser->id,
            'server_id' => $this->server->id,
            'permissions' => [Permission::ACTION_SETTINGS_RENAME],
        ]);

        $ownerCategory = ServerCategory::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($subuser)
            ->putJson("/api/client/servers/{$this->server->uuid}/settings/category", [
                'category' => $ownerCategory->uuid,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('server_category_assignments', [
            'server_id' => $this->server->id,
            'user_id' => $subuser->id,
        ]);
    }

    public function test_subuser_without_permission_cannot_assign_category_or_view_owner_category(): void
    {
        $subuser = User::factory()->create();
        Subuser::query()->create([
            'user_id' => $subuser->id,
            'server_id' => $this->server->id,
            'permissions' => [Permission::ACTION_WEBSOCKET_CONNECT],
        ]);

        $ownerCategory = ServerCategory::factory()->create(['user_id' => $this->user->id]);
        $subuserCategory = ServerCategory::factory()->create(['user_id' => $subuser->id]);

        $this->actingAs($this->user)
            ->putJson("/api/client/servers/{$this->server->uuid}/settings/category", [
                'category' => $ownerCategory->uuid,
            ])
            ->assertNoContent();

        $this->actingAs($subuser)
            ->putJson("/api/client/servers/{$this->server->uuid}/settings/category", [
                'category' => $subuserCategory->uuid,
            ])
            ->assertForbidden();

        $this->actingAs($subuser)
            ->getJson("/api/client/servers/{$this->server->uuid}?include=category")
            ->assertOk()
            ->assertJsonPath('attributes.relationships.category.object', 'null_resource')
            ->assertJsonPath('attributes.relationships.category.attributes', null);
    }
}
