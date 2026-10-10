<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Servers\Pages\EditServer;
use App\Filament\Resources\Servers\RelationManagers\MountsRelationManager;
use App\Filament\Resources\Servers\ServerResource;
use App\Models\Mount;
use App\Models\ServerVariable;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class EditServerTest extends IntegrationTestCase
{
    public function test_admin_can_attach_and_detach_eligible_server_mounts(): void
    {
        $server = $this->createServerModel();
        $otherServer = $this->createServerModel();
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $mounts = [];
        foreach (['eligible', 'wrong-node', 'wrong-egg', 'unassigned'] as $name) {
            $mount = new Mount([
                'name' => $name,
                'source' => '/tmp/'.$name,
                'target' => '/mnt/'.$name,
                'read_only' => true,
                'user_mountable' => false,
            ]);
            $mount->forceFill(['uuid' => Str::uuid()->toString()])->saveOrFail();
            if ($name !== 'unassigned') {
                $mount->nodes()->attach($name === 'wrong-node' ? $otherServer->node_id : $server->node_id);
                $mount->eggs()->attach($name === 'wrong-egg' ? $this->cloneEggAndVariables($server->egg)->id : $server->egg_id);
            }
            $mounts[$name] = $mount;
        }

        $this->assertContains(MountsRelationManager::class, ServerResource::getRelations());
        $component = Livewire::test(MountsRelationManager::class, [
            'ownerRecord' => $server,
            'pageClass' => EditServer::class,
        ])->assertSuccessful();

        foreach (['wrong-node', 'wrong-egg', 'unassigned'] as $name) {
            $component->callAction(TestAction::make('attach')->table(), ['recordId' => $mounts[$name]->id]);
            $this->assertDatabaseMissing('mount_server', ['server_id' => $server->id, 'mount_id' => $mounts[$name]->id]);
            $component->unmountAction();
        }

        $component->callAction(TestAction::make('attach')->table(), ['recordId' => $mounts['eligible']->id])
            ->assertHasNoActionErrors()
            ->assertCanSeeTableRecords([$mounts['eligible']]);
        $this->assertDatabaseHas('mount_server', ['server_id' => $server->id, 'mount_id' => $mounts['eligible']->id]);
        $this->assertCount(0, $otherServer->mounts);

        $mounts['eligible']->nodes()->detach();
        $component->callAction(TestAction::make('detach')->table($mounts['eligible']))
            ->assertHasNoActionErrors();
        $this->assertDatabaseMissing('mount_server', ['server_id' => $server->id, 'mount_id' => $mounts['eligible']->id]);
    }

    #[DataProvider('startupValues')]
    public function test_form_loads_saved_startup_values_for_the_current_server(?string $value): void
    {
        $server = $this->createServerModel();
        $otherServer = $this->createServerModel(['egg_id' => $server->egg_id]);
        $variable = $server->egg->variables->first();

        ServerVariable::query()->create([
            'server_id' => $otherServer->id,
            'variable_id' => $variable->id,
            'variable_value' => 'other-server-value',
        ]);

        if ($value !== null) {
            ServerVariable::query()->create([
                'server_id' => $server->id,
                'variable_id' => $variable->id,
                'variable_value' => $value,
            ]);
        }

        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(EditServer::class, ['record' => $server->id])
            ->assertSuccessful()
            ->assertSet('data.environment.'.$variable->env_variable, $value ?? $variable->default_value);
    }

    public static function startupValues(): array
    {
        return [
            'custom value' => ['saved-custom-value'],
            'empty value' => [''],
            'zero value' => ['0'],
            'missing value' => [null],
        ];
    }
}
