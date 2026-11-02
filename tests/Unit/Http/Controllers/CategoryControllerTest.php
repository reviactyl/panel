<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Middleware\Activity\TrackAPIKey;
use App\Http\Middleware\Api\AuthenticateIPAccess;
use App\Http\Middleware\EnsureInstallationState;
use App\Http\Middleware\EnsureStatefulRequests;
use App\Http\Middleware\RequireTwoFactorAuthentication;
use App\Models\ApiKey;
use App\Models\ServerCategory;
use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\TransientToken;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', fn (Blueprint $table) => $table->id());
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        Schema::create('allocations', fn (Blueprint $table) => $table->id());
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id');
        });
        Schema::create('subusers', function (Blueprint $table) {
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('user_id');
        });
        Schema::create('server_category_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('user_id');
            $table->foreignId('category_id')->constrained('server_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['server_id', 'user_id']);
        });
        Schema::create('server_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });
        (require database_path('migrations/2026_11_01_000000_add_parent_to_server_categories.php'))->up();
        (require database_path('migrations/2026_11_01_000001_add_priority_to_server_categories.php'))->up();
        $this->withoutMiddleware();
        $user = new User();
        $user->id = 1;
        $user->root_admin = true;
        $user->withAccessToken(new TransientToken());
        $this->actingAs($user);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        parent::tearDown();
    }

    private function createFolder(string $name, ?string $parent = null): string
    {
        return $this->postJson('/api/client/account/categories', ['name' => $name, 'parent_uuid' => $parent])
            ->assertOk()->json('attributes.uuid');
    }

    public function test_nested_folders_reject_cycles_and_foreign_parents(): void
    {
        $this->postJson('/api/client/account/categories', ['name' => 'Home Folder', 'parent_uuid' => ''])->assertOk()->assertJsonPath('attributes.parent_uuid', null);
        $root = $this->createFolder('Root');
        $child = $this->createFolder('Child', $root);
        $leaf = $this->createFolder('Leaf', $child);
        $this->getJson('/api/client/account/categories/'.$leaf)->assertOk()->assertJsonPath('attributes.parent_uuid', $child);
        $this->putJson('/api/client/account/categories/'.$root, ['parent_uuid' => $root])->assertUnprocessable();
        $this->putJson('/api/client/account/categories/'.$root, ['parent_uuid' => $leaf])->assertUnprocessable();
        $foreign = ServerCategory::create(['uuid' => fake()->uuid(), 'user_id' => 2, 'name' => 'Private']);
        $this->putJson('/api/client/account/categories/'.$child, ['parent_uuid' => $foreign->uuid])->assertUnprocessable();
        $this->postJson('/api/client/account/categories', ['name' => 'Invalid', 'parent_uuid' => $foreign->uuid])->assertUnprocessable();
        $this->getJson('/api/client/account/categories/'.$root)->assertOk()->assertJsonPath('attributes.parent_uuid', null);
    }

    public function test_moving_and_deleting_a_parent_preserves_its_children(): void
    {
        $root = $this->createFolder('Root');
        $child = $this->createFolder('Child', $root);
        $other = $this->createFolder('Other');
        $this->putJson('/api/client/account/categories/'.$child, ['parent_uuid' => $other])->assertOk()->assertJsonPath('attributes.parent_uuid', $other);
        $this->putJson('/api/client/account/categories/'.$child, ['name' => 'Renamed'])->assertOk()->assertJsonPath('attributes.parent_uuid', $other);
        $this->deleteJson('/api/client/account/categories/'.$other)->assertNoContent();
        $this->getJson('/api/client/account/categories/'.$child)->assertOk()->assertJsonPath('attributes.parent_uuid', null);
        (require database_path('migrations/2026_11_01_000000_add_parent_to_server_categories.php'))->down();
        $this->assertFalse(Schema::hasColumn('server_categories', 'parent_id'));
    }

    public function test_priority_name_and_creation_order_and_leftmost_move(): void
    {
        $root = $this->createFolder('Root');
        $a = $this->createFolder('Alpha', $root);
        $z = $this->createFolder('Zulu', $root);
        $this->putJson('/api/client/account/categories/'.$a, ['priority' => 10])->assertOk();
        $this->putJson('/api/client/account/categories/'.$z, ['priority' => -2])->assertOk();
        $this->assertSame([$z, $root, $a], array_values(array_intersect($this->getJson('/api/client/account/categories')->json('data.*.attributes.uuid'), [$root, $a, $z])));
        $moved = $this->createFolder('Moved');
        $this->putJson('/api/client/account/categories/'.$moved, ['parent_uuid' => $root])->assertOk()->assertJsonPath('attributes.priority', -3);
        $this->putJson('/api/client/account/categories/'.$moved, ['priority' => 2147483648])->assertUnprocessable();
        $this->postJson('/api/client/account/categories/reorder', ['ids' => [$a, $z]])->assertNoContent();
        $this->getJson('/api/client/account/categories/'.$a)->assertJsonPath('attributes.priority', 0);
        $this->getJson('/api/client/account/categories/'.$z)->assertJsonPath('attributes.priority', 1);
        $this->postJson('/api/client/account/categories/reorder', ['ids' => [$a, $a]])->assertUnprocessable();
    }

    public function test_priority_migration_preserves_existing_order(): void
    {
        $root = $this->createFolder('Root');
        ServerCategory::where('uuid', $root)->update(['position' => 8]);
        $migration = require database_path('migrations/2026_11_01_000001_add_priority_to_server_categories.php');
        $migration->down();
        $migration->up();
        $this->assertSame(8, ServerCategory::where('uuid', $root)->firstOrFail()->priority);
    }

    private function applicationKey(int $permission): void
    {
        $this->withMiddleware();
        $this->withoutMiddleware([
            EnsureInstallationState::class,
            EnsureStatefulRequests::class,
            TrackAPIKey::class,
            RequireTwoFactorAuthentication::class,
            AuthenticateIPAccess::class,
            Authenticate::class,
            ThrottleRequests::class,
        ]);
        $user = new User();
        $user->id = 1;
        $user->root_admin = true;
        $key = new ApiKey();
        $key->forceFill(['key_type' => ApiKey::TYPE_APPLICATION, 'r_users' => $permission]);
        $user->withAccessToken($key);
        $this->actingAs($user);
    }

    public function test_application_api_acl_and_scoping_and_client_parity(): void
    {
        $this->applicationKey(0);
        $this->getJson('/api/application/users/1/folders')->assertForbidden();
        $this->applicationKey(1);
        $this->getJson('/api/application/users/1/folders')->assertOk();
        $this->postJson('/api/application/users/1/folders', ['name' => 'Denied'])->assertForbidden();
        $this->applicationKey(3);
        $uuid = $this->postJson('/api/application/users/1/folders', ['name' => 'API Folder', 'priority' => 8])
            ->assertCreated()->assertJsonPath('attributes.priority', 8)->json('attributes.uuid');
        $this->patchJson('/api/application/users/1/folders/'.$uuid, ['color' => '#abcdef', 'priority' => 4])->assertOk()->assertJsonPath('attributes.priority', 4);
        $this->getJson('/api/application/users/2/folders/'.$uuid)->assertNotFound();
        $this->patchJson('/api/application/users/2/folders/'.$uuid, ['name' => 'Invalid'])->assertNotFound();
        DB::table('servers')->insert([['id' => 1, 'owner_id' => 1], ['id' => 2, 'owner_id' => 2]]);
        $this->putJson('/api/application/users/1/servers/1/folder', ['category_uuid' => $uuid])->assertNoContent();
        $this->getJson('/api/application/users/1/servers/1/folder')->assertOk()->assertJsonPath('attributes.uuid', $uuid);
        $this->putJson('/api/application/users/1/servers/2/folder', ['category_uuid' => $uuid])->assertNotFound();
        $this->putJson('/api/application/users/2/servers/2/folder', ['category_uuid' => $uuid])->assertUnprocessable();
        $this->putJson('/api/application/users/1/servers/1/folder', ['category_uuid' => null])->assertNoContent();
        $this->getJson('/api/application/users/1/servers/1/folder')->assertOk()->assertJsonPath('object', 'null_resource');
        $other = $this->postJson('/api/application/users/2/folders', ['name' => 'Shared'])->assertCreated()->json('attributes.uuid');
        DB::table('subusers')->insert(['user_id' => 2, 'server_id' => 1]);
        $this->putJson('/api/application/users/2/servers/1/folder', ['category_uuid' => $other])->assertNoContent();
        $this->getJson('/api/application/users/2/servers/1/folder')->assertJsonPath('attributes.uuid', $other);
        $this->getJson('/api/application/users/1/servers/1/folder')->assertJsonPath('object', 'null_resource');
        $this->deleteJson('/api/application/users/2/folders/'.$other)->assertNoContent();
        $this->deleteJson('/api/application/users/1/folders/'.$uuid)->assertNoContent();
    }
}
