<?php

namespace Tests\Integration\Filament\Pages;

use App\Filament\Resources\Servers\Pages\EditServer;
use App\Models\Allocation;
use App\Models\EggVariable;
use App\Models\Server;
use App\Models\User;
use App\Repositories\Agent\DaemonRevocationRepository;
use App\Repositories\Agent\DaemonServerRepository;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionMethod;
use Tests\Integration\IntegrationTestCase;

class EditServerTest extends IntegrationTestCase
{
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

    public function test_valid_save_syncs_updated_startup_and_limits_to_agent(): void
    {
        $server = $this->createServerModel();
        $allocation = Allocation::factory()->create(['node_id' => $server->node_id]);
        $repository = $this->mock(DaemonServerRepository::class);
        $repository->expects('setServer')->with(Mockery::on(function (Server $updated): bool {
            return $updated->name === 'Updated server'
                && $updated->memory === 256
                && $updated->startup === 'updated startup'
                && $updated->variables()->where('env_variable', 'BUNGEE_VERSION')->first()->server_value === '1234';
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
