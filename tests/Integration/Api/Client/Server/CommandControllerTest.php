<?php

namespace Tests\Integration\Api\Client\Server;

use App\Exceptions\Http\Connection\DaemonConnectionException;
use App\Models\Permission;
use App\Models\Server;
use App\Repositories\Agent\DaemonCommandRepository;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Response;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class CommandControllerTest extends ClientApiIntegrationTestCase
{
    /**
     * Test that a validation error is returned if there is no command present in the
     * request.
     */
    public function test_validation_error_is_returned_if_no_command_is_present()
    {
        [$user, $server] = $this->generateTestAccount();

        $response = $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/command", [
            'command' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonPath('errors.0.meta.rule', 'required');
    }

    /**
     * Test that a subuser without the required permission receives an error when trying to
     * execute the command.
     */
    public function test_subuser_without_permission_receives_error()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_WEBSOCKET_CONNECT]);

        $response = $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/command", [
            'command' => 'say Test',
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * Test that a command can be sent to the server.
     */
    public function test_command_can_send_to_server()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_CONTROL_CONSOLE]);

        $mock = $this->mock(DaemonCommandRepository::class);
        $mock->expects('setServer')
            ->with(\Mockery::on(fn (Server $value) => $value->is($server)))
            ->andReturnSelf();

        $mock->expects('send')->with('say Test')->andReturn(new GuzzleResponse());

        $response = $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/command", [
            'command' => 'say Test',
        ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /**
     * Test that an error is returned when the server is offline that is more specific than the
     * regular daemon connection error.
     */
    public function test_error_is_returned_when_server_is_offline()
    {
        [$user, $server] = $this->generateTestAccount();

        $mock = $this->mock(DaemonCommandRepository::class);
        $mock->expects('setServer->send')->andThrows(
            new DaemonConnectionException(
                new BadResponseException('', new Request('GET', 'test'), new GuzzleResponse(Response::HTTP_BAD_GATEWAY))
            )
        );

        $response = $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/command", [
            'command' => 'say Test',
        ]);

        $response->assertStatus(Response::HTTP_BAD_GATEWAY);
        $response->assertJsonPath('errors.0.code', 'HttpException');
        $response->assertJsonPath('errors.0.detail', 'Server must be online in order to send commands.');
    }
}
