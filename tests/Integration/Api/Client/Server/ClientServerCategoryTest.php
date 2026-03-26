<?php

namespace App\Tests\Integration\Api\Client\Server;

use App\Models\User;
use App\Models\Server;
use App\Models\ServerCategory;
use App\Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class ClientServerCategoryTest extends ClientApiIntegrationTestCase
{
    /** @var Server */
    private $server;

    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->server = Server::factory()->create(['owner_id' => $this->user->id]);
    }

    public function testUserCanListCategories()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);
        $otherCategory = ServerCategory::factory()->create(); // different user (default factory)

        $response = $this->actingAs($this->user)->getJson('/api/client/account/categories');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.uuid', $category->uuid);
    }

    public function testUserCanCreateCategory()
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

    public function testUserCanUpdateCategory()
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

    public function testUserCanDeleteCategory()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/client/account/categories/{$category->uuid}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('server_categories', ['id' => $category->id]);
    }

    public function testServerResponseIncludesCategory()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);
        $this->server->update(['category_id' => $category->id]);

        $response = $this->actingAs($this->user)->getJson("/api/client/servers/{$this->server->uuid}?include=category");

        $response->assertOk()
            ->assertJsonPath('attributes.relationships.category.attributes.uuid', $category->uuid);
    }

    public function testFilterServersByCategory()
    {
        $category = ServerCategory::factory()->create(['user_id' => $this->user->id]);
        $server1 = Server::factory()->create(['owner_id' => $this->user->id, 'category_id' => $category->id]);
        $server2 = Server::factory()->create(['owner_id' => $this->user->id, 'category_id' => null]);

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
}
