<?php

namespace Tests\Integration\Api\Client\Server\Startup;

use App\Events\ActivityLogged;
use App\Models\EggVariable;
use App\Models\Permission;
use App\Models\Server;
use App\Models\User;
use App\Services\Servers\EnvironmentService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class UpdateStartupVariableTest extends ClientApiIntegrationTestCase
{
    /**
     * Test that a startup variable can be edited successfully for a server.
     */
    #[DataProvider('permissionsDataProvider')]
    public function test_startup_variable_can_be_updated(array $permissions)
    {
        /** @var Server $server */
        [$user, $server] = $this->generateTestAccount($permissions);
        $server->fill([
            'startup' => 'java {{SERVER_JARFILE}} --version {{BUNGEE_VERSION}}',
        ])->save();

        $response = $this->actingAs($user)->putJson($this->link($server).'/startup/variable', [
            'key' => 'BUNGEE_VERSION',
            'value' => '1.2.3',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonPath('errors.0.code', 'ValidationException');
        $response->assertJsonPath('errors.0.detail', 'The value may only contain letters and numbers.');

        $response = $this->actingAs($user)->putJson($this->link($server).'/startup/variable', [
            'key' => 'BUNGEE_VERSION',
            'value' => '123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('object', EggVariable::RESOURCE_NAME);
        $this->assertJsonTransformedWith($response->json('attributes'), $server->variables[0]);
        $response->assertJsonPath('meta.startup_command', 'java bungeecord.jar --version 123');
        $response->assertJsonPath('meta.raw_startup_command', $server->startup);
    }

    /**
     * Test that variables that are either not user_viewable, or not user_editable, cannot be
     * updated via this endpoint.
     */
    #[DataProvider('permissionsDataProvider')]
    public function test_startup_variable_cannot_be_updated_if_not_user_viewable_or_editable(array $permissions)
    {
        /** @var Server $server */
        [$user, $server] = $this->generateTestAccount($permissions);

        $egg = $this->cloneEggAndVariables($server->egg);
        $egg->variables()->where('env_variable', 'BUNGEE_VERSION')->update(['user_viewable' => false]);
        $egg->variables()->where('env_variable', 'SERVER_JARFILE')->update(['user_editable' => false]);

        $server->fill(['egg_id' => $egg->id])->save();
        $server->refresh();

        $response = $this->actingAs($user)->putJson($this->link($server).'/startup/variable', [
            'key' => 'BUNGEE_VERSION',
            'value' => '123',
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonPath('errors.0.code', 'BadRequestHttpException');
        $response->assertJsonPath('errors.0.detail', 'The environment variable you are trying to edit does not exist.');

        $response = $this->actingAs($user)->putJson($this->link($server).'/startup/variable', [
            'key' => 'SERVER_JARFILE',
            'value' => 'server2.jar',
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonPath('errors.0.code', 'BadRequestHttpException');
        $response->assertJsonPath('errors.0.detail', 'The environment variable you are trying to edit is read-only.');
    }

    /**
     * Test that a hidden variable is not included in the startup_command output for the server if
     * a different variable is updated.
     */
    public function test_hidden_variables_are_not_returned_in_startup_command_when_updating_variable()
    {
        /** @var Server $server */
        [$user, $server] = $this->generateTestAccount();

        $egg = $this->cloneEggAndVariables($server->egg);
        $egg->variables()->first()->update(['user_viewable' => false]);

        $server->fill([
            'egg_id' => $egg->id,
            'startup' => 'java {{SERVER_JARFILE}} --version {{BUNGEE_VERSION}}',
        ])->save();

        $server->refresh();

        $response = $this->actingAs($user)->putJson($this->link($server).'/startup/variable', [
            'key' => 'SERVER_JARFILE',
            'value' => 'server2.jar',
        ]);

        $response->assertOk();
        $response->assertJsonPath('meta.startup_command', 'java server2.jar --version [hidden]');
        $response->assertJsonPath('meta.raw_startup_command', $server->startup);
    }

    /**
     * Test that an egg variable with a validation rule of 'nullable|string' works if no value
     * is passed through in the request.
     *
     * @see https://github.com/pterodactyl/panel/issues/2433
     */
    public function test_egg_variable_with_nullable_string_is_not_required()
    {
        /** @var Server $server */
        [$user, $server] = $this->generateTestAccount();

        $egg = $this->cloneEggAndVariables($server->egg);
        $egg->variables()->first()->update(['rules' => 'nullable|string']);

        $server->fill(['egg_id' => $egg->id])->save();
        $server->refresh();

        $response = $this->actingAs($user)->putJson($this->link($server).'/startup/variable', [
            'key' => 'BUNGEE_VERSION',
            'value' => '',
        ]);

        $response->assertOk();
        $response->assertJsonPath('attributes.server_value', null);
    }

    /**
     * Test that a variable cannot be updated if the user does not have permission to perform
     * that action, or they aren't assigned at all to the server.
     */
    public function test_startup_variable_cannot_be_updated_if_not_user_viewable()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_WEBSOCKET_CONNECT]);
        $this->actingAs($user)->putJson($this->link($server).'/startup/variable')->assertForbidden();

        $user2 = User::factory()->create();
        $this->actingAs($user2)->putJson($this->link($server).'/startup/variable')->assertNotFound();
    }

    public function test_duplicate_name_updates_the_effective_variable(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $egg = $this->cloneEggAndVariables($server->egg);
        $first = $egg->variables()->where('env_variable', 'BUNGEE_VERSION')->firstOrFail();
        $last = $first->replicate();
        $last->default_value = 'last';
        $last->save();
        $server->update(['egg_id' => $egg->id, 'startup' => '{{BUNGEE_VERSION}}']);

        $this->actingAs($user)->getJson($this->link($server).'/startup')
            ->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.startup_command', 'last');

        $this->actingAs($user)->putJson($this->link($server).'/startup/variable', [
            'key' => 'BUNGEE_VERSION', 'value' => 'updated',
        ])->assertOk()->assertJsonPath('attributes.server_value', 'updated')
            ->assertJsonPath('meta.startup_command', 'updated');

        $this->assertDatabaseHas('server_variables', [
            'server_id' => $server->id, 'variable_id' => $last->id, 'variable_value' => 'updated',
        ]);
        $this->assertDatabaseMissing('server_variables', [
            'server_id' => $server->id, 'variable_id' => $first->id,
        ]);
        $this->assertSame('updated', app(EnvironmentService::class)->handle($server->fresh())['BUNGEE_VERSION']);
    }

    #[DataProvider('effectiveVariableRestrictions')]
    public function test_duplicate_name_respects_effective_variable_restrictions(array $restrictions, int $status): void
    {
        [$user, $server] = $this->generateTestAccount();
        $egg = $this->cloneEggAndVariables($server->egg);
        $last = $egg->variables()->where('env_variable', 'BUNGEE_VERSION')->firstOrFail()->replicate();
        $last->fill($restrictions)->save();
        $server->update(['egg_id' => $egg->id, 'startup' => '{{BUNGEE_VERSION}}']);

        $response = $this->actingAs($user)->getJson($this->link($server).'/startup')->assertOk();
        if (! $last->user_viewable) {
            $response->assertJsonCount(1, 'data')->assertJsonPath('meta.startup_command', '[hidden]');
        }

        $this->actingAs($user)->putJson($this->link($server).'/startup/variable', [
            'key' => 'BUNGEE_VERSION', 'value' => 'updated',
        ])->assertStatus($status);
        $this->assertDatabaseMissing('server_variables', ['server_id' => $server->id]);
    }

    public function test_secret_values_are_validated_redacted_and_can_be_cleared(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $egg = $this->cloneEggAndVariables($server->egg);
        $variable = $egg->variables()->where('env_variable', 'BUNGEE_VERSION')->firstOrFail();
        $variable->update(['is_secret' => true, 'default_value' => 'default-secret', 'rules' => 'nullable|string|min:8']);
        $server->update(['egg_id' => $egg->id, 'startup' => '{{BUNGEE_VERSION}}']);
        $url = $this->link($server).'/startup';

        $response = $this->actingAs($user)->getJson($url)->assertOk();
        $response->assertJsonPath('meta.startup_command', '[hidden]');
        $this->assertStringNotContainsString('default-secret', $response->getContent());
        $secret = collect($response->json('data'))->firstWhere('attributes.env_variable', 'BUNGEE_VERSION')['attributes'];
        $this->assertTrue($secret['is_secret']);
        $this->assertTrue($secret['has_value']);
        $this->assertNull($secret['server_value']);
        $this->assertSame('', $secret['default_value']);

        $this->putJson($url.'/variable', ['key' => 'BUNGEE_VERSION', 'value' => 'short'])->assertUnprocessable();
        $response = $this->putJson($url.'/variable', ['key' => 'BUNGEE_VERSION', 'value' => 'replacement-secret'])
            ->assertOk()->assertJsonPath('attributes.server_value', null)
            ->assertJsonPath('attributes.has_value', true)
            ->assertJsonPath('meta.startup_command', '[hidden]');
        $this->assertStringNotContainsString('replacement-secret', $response->getContent());
        $this->assertSame('replacement-secret', app(EnvironmentService::class)->handle($server->fresh())['BUNGEE_VERSION']);
        Event::assertDispatched(ActivityLogged::class, function ($event) {
            return $event->model->event === 'server:startup.edit'
                && $event->model->properties['old'] === '[hidden]'
                && $event->model->properties['new'] === '[hidden]';
        });

        $this->putJson($url.'/variable', ['key' => 'BUNGEE_VERSION', 'value' => ''])
            ->assertOk()->assertJsonPath('attributes.has_value', false);
        $this->assertSame('', app(EnvironmentService::class)->handle($server->fresh())['BUNGEE_VERSION']);
        $cleared = collect($this->getJson($url)->assertOk()->json('data'))
            ->firstWhere('attributes.env_variable', 'BUNGEE_VERSION')['attributes'];
        $this->assertFalse($cleared['has_value']);
        $this->assertSame('', $server->fresh()->variables->firstWhere('env_variable', 'BUNGEE_VERSION')->server_value);
        $variable->update(['rules' => 'required|string']);
        $this->putJson($url.'/variable', ['key' => 'BUNGEE_VERSION', 'value' => ''])->assertUnprocessable();
    }

    public static function effectiveVariableRestrictions(): array
    {
        return [
            [['user_viewable' => false], 400],
            [['user_editable' => false], 400],
            [['rules' => 'required|integer'], 422],
        ];
    }

    public static function permissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_STARTUP_UPDATE]]];
    }
}
