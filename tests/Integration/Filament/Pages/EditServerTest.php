<?php

namespace Tests\Integration\Filament\Pages;

use App\Exceptions\DisplayException;
use App\Exceptions\Model\DataValidationException;
use App\Filament\Resources\Servers\Pages\EditServer;
use App\Models\Allocation;
use App\Models\EggVariable;
use App\Models\Server;
use App\Models\ServerVariable;
use App\Models\User;
use App\Repositories\Agent\DaemonRevocationRepository;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Servers\ServerConfigurationStructureService;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Mockery;
use ReflectionMethod;
use Tests\Integration\IntegrationTestCase;

class EditServerTest extends IntegrationTestCase
{
    public function test_duplicate_external_identifier_does_not_save_startup_changes(): void
    {
        $server = $this->createServerModel();
        $other = $this->createServerModel(['external_id' => 'already-used']);
        foreach ($server->variables as $variable) {
            ServerVariable::query()->create([
                'server_id' => $server->id,
                'variable_id' => $variable->id,
                'variable_value' => $variable->default_value,
            ]);
        }

        $original = $server->fresh()->getAttributes();
        $originalVariables = ServerVariable::query()->where('server_id', $server->id)->pluck('variable_value', 'variable_id')->all();
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        $this->withoutExceptionHandling();
        $this->mock(DaemonServerRepository::class)->shouldNotReceive('setServer');
        $this->mock(DaemonRevocationRepository::class)->shouldNotReceive('setNode');

        $page = Livewire::test(EditServer::class, ['record' => $server->id])
            ->fillForm([
                'external_id' => $other->external_id,
                'startup' => 'rejected startup',
                'environment' => ['BUNGEE_VERSION' => '1234', 'SERVER_JARFILE' => 'rejected.jar'],
            ]);

        try {
            $page->call('save');
            $this->fail('The duplicate external identifier should reject the save.');
        } catch (DataValidationException $exception) {
            $this->assertArrayHasKey('external_id', $exception->getMessageBag()->toArray());
        }

        $this->assertSame($original, $server->fresh()->getAttributes());
        $this->assertSame($originalVariables, ServerVariable::query()->where('server_id', $server->id)->pluck('variable_value', 'variable_id')->all());
    }

    public function test_decimal_storage_unit_only_converts_limits_at_the_form(): void
    {
        config()->set('panel.use_binary_prefix', false);
        $server = $this->createServerModel(['memory' => 2048, 'swap' => -1, 'disk' => 0]);
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        $repository = $this->mock(DaemonServerRepository::class);
        $repository->allows('setServer')->andReturnSelf();
        $repository->allows('sync')->andReturnUndefined();

        $page = Livewire::test(EditServer::class, ['record' => $server->id])
            ->assertFormSet(['memory' => 2147, 'swap' => -1, 'disk' => 0]);

        // Saving without touching the limits must not change what is stored or sent to Agent.
        $page->call('save')->assertHasNoFormErrors();
        $this->assertSame([2048, -1, 0], [$server->refresh()->memory, $server->swap, $server->disk]);
        $this->assertSame(2048, app(ServerConfigurationStructureService::class)->handle($server)['build']['memory_limit']);

        $page->fillForm(['memory' => 1000, 'disk' => 2000])->call('save')->assertHasNoFormErrors();
        $this->assertSame([954, 1907], [$server->refresh()->memory, $server->disk]);
    }

    public function test_invalid_startup_values_do_not_save_details_limits_or_allocations(): void
    {
        $server = $this->createServerModel();
        $original = $server->getAttributes();
        $allocation = Allocation::factory()->create(['node_id' => $server->node_id]);
        $owner = User::factory()->create();

        $this->mock(DaemonServerRepository::class)->shouldNotReceive('setServer');
        $this->mock(DaemonRevocationRepository::class)->shouldNotReceive('setNode');

        try {
            $this->updateServer($server, [
                'owner_id' => $owner->id,
                'name' => 'Rejected name',
                'memory' => $server->memory + 128,
                'allocation_additional' => [$allocation->id],
                'environment' => ['BUNGEE_VERSION' => '$$', 'SERVER_JARFILE' => 'server.jar'],
            ]);
            $this->fail('Invalid startup values should reject the save.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('environment.BUNGEE_VERSION', $exception->errors());
        }

        $this->assertSame($original, $server->fresh()->getAttributes());
        $this->assertNull($allocation->fresh()->server_id);
        $this->assertDatabaseCount('server_variables', 0);
    }

    public function test_startup_values_are_validated_against_the_selected_egg_as_admin(): void
    {
        $server = $this->createServerModel();
        $original = $server->getAttributes();
        $egg = $this->cloneEggAndVariables($server->egg);
        EggVariable::query()->where('egg_id', $egg->id)->where('env_variable', 'BUNGEE_VERSION')->update([
            'rules' => 'required|integer',
            'user_editable' => false,
            'user_viewable' => false,
        ]);

        $this->mock(DaemonServerRepository::class)->shouldNotReceive('setServer');

        try {
            $this->updateServer($server, [
                'egg_id' => $egg->id,
                'name' => 'Rejected egg change',
                'environment' => ['BUNGEE_VERSION' => 'latest', 'SERVER_JARFILE' => 'server.jar'],
            ]);
            $this->fail('The selected egg rules should reject the save.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('environment.BUNGEE_VERSION', $exception->errors());
        }

        $this->assertSame($original, $server->fresh()->getAttributes());
    }

    public function test_invalid_allocation_does_not_save_startup_changes(): void
    {
        $server = $this->createServerModel();
        $allocation = Allocation::factory()->create(['node_id' => $server->node_id]);
        $startup = $server->startup;
        $this->mock(DaemonServerRepository::class)->shouldNotReceive('setServer');

        try {
            $this->updateServer($server, [
                'allocation_id' => $allocation->id,
                'startup' => 'rejected startup',
                'environment' => ['BUNGEE_VERSION' => '1234', 'SERVER_JARFILE' => 'rejected.jar'],
            ]);
            $this->fail('An unassigned default allocation should reject the save.');
        } catch (DisplayException $exception) {
            $this->assertSame('The requested default allocation is not currently assigned to this server.', $exception->getMessage());
        }

        $this->assertSame($startup, $server->fresh()->startup);
        $this->assertDatabaseCount('server_variables', 0);
    }

    public function test_valid_save_updates_startup_and_syncs_limits_to_agent(): void
    {
        $server = $this->createServerModel();
        $allocation = Allocation::factory()->create(['node_id' => $server->node_id]);
        $repository = $this->mock(DaemonServerRepository::class);
        $repository->expects('setServer')->with(Mockery::on(function (Server $updated): bool {
            return $updated->name === 'Updated server'
                && $updated->memory === 256;
        }))->andReturnSelf();
        $repository->expects('sync')->andReturnUndefined();

        $result = $this->updateServer($server, [
            'name' => 'Updated server',
            'memory' => 256,
            'startup' => 'updated startup',
            'allocation_additional' => [$allocation->id],
            'environment' => ['BUNGEE_VERSION' => '1234', 'SERVER_JARFILE' => 'updated.jar'],
        ]);

        $this->assertSame('Updated server', $result->name);
        $this->assertSame(256, $result->memory);
        $this->assertSame('updated startup', $result->startup);
        $this->assertSame($server->id, $allocation->fresh()->server_id);
        $this->assertDatabaseHas('server_variables', [
            'server_id' => $server->id,
            'variable_id' => $server->variables()->where('env_variable', 'SERVER_JARFILE')->first()->id,
            'variable_value' => 'updated.jar',
        ]);
    }

    private function updateServer(Server $server, array $data): Server
    {
        return (new ReflectionMethod(EditServer::class, 'handleRecordUpdate'))->invoke(new EditServer(), $server, array_replace([
            'external_id' => $server->external_id,
            'owner_id' => $server->owner_id,
            'name' => $server->name,
            'description' => $server->description,
            'database_limit' => $server->database_limit,
            'allocation_limit' => $server->allocation_limit,
            'backup_limit' => $server->backup_limit,
        ], $data));
    }
}
