<?php

namespace Tests\Integration\Api\Client;

use App\Models\Permission;
use App\Models\Subuser;
use App\Models\User;
use Illuminate\Support\Str;

class DashboardEggsTest extends ClientApiIntegrationTestCase
{
    public function test_egg_filter_preserves_owned_shared_private_and_admin_scopes(): void
    {
        [$user, $owned] = $this->generateTestAccount();
        $sharedEgg = $owned->egg->replicate();
        $sharedEgg->uuid = (string) Str::uuid();
        $sharedEgg->save();
        $privateEgg = $owned->egg->replicate();
        $privateEgg->uuid = (string) Str::uuid();
        $privateEgg->save();
        $owner = User::factory()->create();
        $shared = $this->createServerModel(['owner_id' => $owner->id, 'egg_id' => $sharedEgg->id]);
        $this->createServerModel(['owner_id' => $owner->id, 'egg_id' => $privateEgg->id]);
        Subuser::create(['user_id' => $user->id, 'server_id' => $shared->id, 'permissions' => [Permission::ACTION_WEBSOCKET_CONNECT]]);

        $response = $this->actingAs($user)->getJson('/api/client/eggs')->assertOk();
        $this->assertEqualsCanonicalizing([$owned->egg_id, $sharedEgg->id], array_column($response->json('data'), 'id'));
        $this->actingAs($user)->getJson('/api/client')->assertOk()->assertJsonCount(2, 'data');
        $user->update(['root_admin' => true]);
        $this->actingAs($user)->getJson('/api/client/eggs?type=admin')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $privateEgg->id);
        $this->actingAs($user)->getJson('/api/client?type=admin')->assertOk()->assertJsonCount(1, 'data');
    }
}
