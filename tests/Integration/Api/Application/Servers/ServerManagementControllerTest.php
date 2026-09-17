<?php

namespace Tests\Integration\Api\Application\Servers;

use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Application\ApplicationApiIntegrationTestCase;

class ServerManagementControllerTest extends ApplicationApiIntegrationTestCase
{
    /**
     * Test that a server can be reinstalled.
     */
    public function test_server_can_be_reinstalled()
    {
        $server = $this->createServerModel();

        $service = \Mockery::mock(DaemonServerRepository::class);
        $this->app->instance(DaemonServerRepository::class, $service);

        $service->expects('setServer')
            ->with(\Mockery::on(fn ($value) => $value->uuid === $server->uuid))
            ->andReturnSelf()
            ->getMock()
            ->expects('reinstall')
            ->andReturnUndefined();

        $this->postJson('/api/application/servers/'.$server->id.'/reinstall')
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSame(Server::STATUS_INSTALLING, $server->refresh()->status);
    }

    /**
     * Test that a server configured to skip its egg's install script cannot be reinstalled.
     */
    public function test_server_configured_to_skip_scripts_cannot_be_reinstalled()
    {
        $server = $this->createServerModel(['skip_scripts' => true]);

        $service = \Mockery::mock(DaemonServerRepository::class);
        $this->app->instance(DaemonServerRepository::class, $service);

        $service->expects('setServer')->never();

        $this->postJson('/api/application/servers/'.$server->id.'/reinstall')
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.0.detail', trans('admin/server.exceptions.skipping_install_script'));
    }

    /**
     * Test that a client which does not explicitly ask for JSON still receives the error status
     * rather than being redirected away from the API.
     */
    public function test_blocked_reinstall_returns_an_error_to_clients_not_requesting_json()
    {
        $server = $this->createServerModel(['skip_scripts' => true]);

        $service = \Mockery::mock(DaemonServerRepository::class);
        $this->app->instance(DaemonServerRepository::class, $service);

        $service->expects('setServer')->never();

        $this->post('/api/application/servers/'.$server->id.'/reinstall', [], ['Accept' => '*/*'])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.0.detail', trans('admin/server.exceptions.skipping_install_script'));
    }

    /**
     * Test that a server configured to skip its egg's install script can still be reinstalled
     * while it is in a failed state, since that is the only way to pull it back out of one.
     */
    #[DataProvider('recoverableStatusesDataProvider')]
    public function test_server_configured_to_skip_scripts_can_be_reinstalled_from_a_failed_state(string $status)
    {
        $server = $this->createServerModel(['skip_scripts' => true, 'status' => $status]);

        $service = \Mockery::mock(DaemonServerRepository::class);
        $this->app->instance(DaemonServerRepository::class, $service);

        $service->expects('setServer')
            ->with(\Mockery::on(fn ($value) => $value->uuid === $server->uuid))
            ->andReturnSelf()
            ->getMock()
            ->expects('reinstall')
            ->andReturnUndefined();

        $this->postJson('/api/application/servers/'.$server->id.'/reinstall')
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSame(Server::STATUS_INSTALLING, $server->refresh()->status);
    }

    public static function recoverableStatusesDataProvider(): array
    {
        return [
            [Server::STATUS_INSTALLING],
            [Server::STATUS_INSTALL_FAILED],
            [Server::STATUS_REINSTALL_FAILED],
        ];
    }
}
