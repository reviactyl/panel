<?php

namespace Tests\Integration\Api\Client\Server;

use App\Models\Permission;
use App\Models\Schedule;
use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Servers\EnvironmentService;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class SettingsControllerTest extends ClientApiIntegrationTestCase
{
    /**
     * Test that the server's name can be changed.
     */
    #[DataProvider('renamePermissionsDataProvider')]
    public function test_server_name_can_be_changed(array $permissions)
    {
        /** @var Server $server */
        [$user, $server] = $this->generateTestAccount($permissions);
        $originalName = $server->name;
        $originalDescription = $server->description;

        $response = $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/settings/rename", [
            'name' => '',
            'description' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonPath('errors.0.meta.rule', 'required');

        $server = $server->refresh();
        $this->assertSame($originalName, $server->name);
        $this->assertSame($originalDescription, $server->description);

        $this->actingAs($user)
            ->postJson("/api/client/servers/$server->uuid/settings/rename", [
                'name' => 'Test Server Name',
                'description' => 'This is a test server.',
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $server = $server->refresh();
        $this->assertSame('Test Server Name', $server->name);
        $this->assertSame('This is a test server.', $server->description);
    }

    /**
     * Test that a subuser receives a permissions error if they do not have the required permission
     * and attempt to change the name.
     */
    public function test_subuser_cannot_change_server_name_without_permission()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_WEBSOCKET_CONNECT]);
        $originalName = $server->name;

        $this->actingAs($user)
            ->postJson("/api/client/servers/$server->uuid/settings/rename", [
                'name' => 'Test Server Name',
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $server = $server->refresh();
        $this->assertSame($originalName, $server->name);
    }

    /**
     * Test that a server can be reinstalled. Honestly this test doesn't do much of anything other
     * than make sure the endpoint works since.
     */
    #[DataProvider('reinstallPermissionsDataProvider')]
    public function test_server_can_be_reinstalled(array $permissions)
    {
        /** @var Server $server */
        [$user, $server] = $this->generateTestAccount($permissions);
        $this->assertTrue($server->isInstalled());

        $service = \Mockery::mock(DaemonServerRepository::class);
        $this->app->instance(DaemonServerRepository::class, $service);

        $service->expects('setServer')
            ->with(\Mockery::on(function ($value) use ($server) {
                return $value->uuid === $server->uuid;
            }))
            ->andReturnSelf()
            ->getMock()
            ->expects('reinstall')
            ->andReturnUndefined();

        $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/settings/reinstall")
            ->assertStatus(Response::HTTP_ACCEPTED);

        $server = $server->refresh();
        $this->assertSame(Server::STATUS_INSTALLING, $server->status);
    }

    /**
     * Test that a subuser receives a permissions error if they do not have the required permission
     * and attempt to reinstall a server.
     */
    public function test_subuser_cannot_reinstall_server_without_permission()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_WEBSOCKET_CONNECT]);

        $this->actingAs($user)
            ->postJson("/api/client/servers/$server->uuid/settings/reinstall")
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $server = $server->refresh();
        $this->assertTrue($server->isInstalled());
    }

    /**
     * Test that a server configured to skip its egg's install script cannot be reinstalled.
     */
    #[DataProvider('reinstallPermissionsDataProvider')]
    public function test_server_cannot_be_reinstalled_if_configured_to_skip_scripts(array $permissions)
    {
        [$user, $server] = $this->generateTestAccount($permissions);
        $server->update(['skip_scripts' => true]);

        $service = \Mockery::mock(DaemonServerRepository::class);
        $this->app->instance(DaemonServerRepository::class, $service);

        $service->expects('setServer')->never();

        $this->actingAs($user)
            ->postJson("/api/client/servers/$server->uuid/settings/reinstall")
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.0.detail', trans('admin/server.exceptions.skipping_install_script'));

        $this->assertNull($server->refresh()->status);
    }

    /**
     * Test that the "skip scripts" state is exposed to the client API.
     */
    public function test_skip_scripts_state_is_exposed_to_client()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SETTINGS_REINSTALL]);

        $this->actingAs($user)
            ->getJson("/api/client/servers/$server->uuid")
            ->assertOk()
            ->assertJsonPath('attributes.skip_scripts', false);

        $server->update(['skip_scripts' => true]);

        $this->actingAs($user)
            ->getJson("/api/client/servers/$server->uuid")
            ->assertOk()
            ->assertJsonPath('attributes.skip_scripts', true);
    }

    #[DataProvider('timezonePermissionsDataProvider')]
    public function test_server_timezone_can_be_changed(array $permissions)
    {
        [$user, $server] = $this->generateTestAccount($permissions);
        $environment = $this->app->make(EnvironmentService::class);

        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'cron_minute' => '0',
            'cron_hour' => '3',
        ]);

        $this->assertNull($server->timezone);
        $this->assertArrayNotHasKey('TZ', $environment->handle($server));

        $this->actingAs($user)
            ->putJson("/api/client/servers/$server->uuid/settings/timezone", ['timezone' => 'Not/AZone'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.meta.rule', 'timezone');

        $this->actingAs($user)
            ->putJson("/api/client/servers/$server->uuid/settings/timezone")
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.meta.rule', 'present');

        $this->assertNull($server->refresh()->timezone);

        $this->actingAs($user)
            ->putJson("/api/client/servers/$server->uuid/settings/timezone", ['timezone' => 'Asia/Tokyo'])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $server = $server->refresh();
        $next = $schedule->refresh()->next_run_at;
        $this->assertSame('Asia/Tokyo', $server->timezone);
        $this->assertSame('Asia/Tokyo', $environment->handle($server)['TZ']);
        $this->assertSame(config('app.timezone'), $next->getTimezone()->getName());
        $this->assertSame('03:00', $next->clone()->setTimezone('Asia/Tokyo')->format('H:i'));

        $this->actingAs($user)
            ->getJson("/api/client/servers/$server->uuid")
            ->assertOk()
            ->assertJsonPath('attributes.timezone', 'Asia/Tokyo')
            ->assertJsonPath('attributes.default_timezone', config('app.timezone'));

        $this->actingAs($user)
            ->putJson("/api/client/servers/$server->uuid/settings/timezone", ['timezone' => null])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $server = $server->refresh();
        $this->assertNull($server->timezone);
        $this->assertArrayNotHasKey('TZ', $environment->handle($server));
        $this->assertSame('03:00', $schedule->refresh()->next_run_at->format('H:i'));
    }

    public function test_subuser_cannot_change_server_timezone_without_permission()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SETTINGS_RENAME]);

        $this->actingAs($user)
            ->putJson("/api/client/servers/$server->uuid/settings/timezone", ['timezone' => 'Asia/Tokyo'])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $this->assertNull($server->refresh()->timezone);
    }

    public static function renamePermissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SETTINGS_RENAME]]];
    }

    public static function timezonePermissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SETTINGS_TIMEZONE]]];
    }

    public static function reinstallPermissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SETTINGS_REINSTALL]]];
    }
}
