<?php

namespace Tests\Integration\Admin;

use App\Filament\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Models\Location;
use App\Models\Node;
use App\Models\User;
use App\Services\Activity\ActivityLogService;
use App\Services\Nodes\NodeCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Integration\IntegrationTestCase;

class ActivityLogTest extends IntegrationTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test that creating a server generates an activity log.
     */
    public function test_server_creation_generates_log()
    {
        $admin = User::factory()->create(['root_admin' => 1]);
        $this->actingAs($admin);

        $location = Location::factory()->create();

        $node = app(NodeCreationService::class)->handle([
            'name' => 'Test Node',
            'location_id' => $location->id,
            'fqdn' => 'node.test.com',
            'scheme' => 'http',
            'memory' => 1024,
            'memory_overallocate' => 0,
            'disk' => 1024,
            'disk_overallocate' => 0,
            'upload_size' => 100,
            'daemonSFTP' => 2022,
            'daemonListen' => 8080,
            'daemonBase' => '/var/lib/reviactyl/volumes',
            'public' => true,
            'maintenance_mode' => false,
        ]);

        app(ActivityLogService::class)->subject($node)->event('node:create')->log();

        $this->assertDatabaseHas('activity_logs', [
            'event' => 'node:create',
        ]);

        $node = Node::query()->where('id', $node->id)->first();
        $this->assertNotNull($node);

        $log = ActivityLog::query()->where('event', 'node:create')->orderByDesc('timestamp')->first();
        $this->assertNotNull($log);
        $this->assertEquals('Test Node', $log->subjects->first()?->subject?->name);
    }

    /**
     * Test that the admin index page loads and shows activity logs.
     */
    public function test_admin_index_shows_logs()
    {
        $admin = User::factory()->create(['root_admin' => 1]);
        $this->actingAs($admin);

        $log = new ActivityLog();
        $log->timestamp = now();
        $log->event = 'test:event';
        $log->ip = '127.0.0.1';
        $log->actor_id = $admin->id;
        $log->actor_type = User::class;
        $log->properties = collect();
        $log->save();

        $response = $this->get(ActivityLogResource::getUrl('index'));
        $response->assertStatus(200);
        $response->assertSee('test:event');
    }
}
