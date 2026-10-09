<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Servers\Pages\EditServer;
use App\Models\ServerVariable;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class EditServerTest extends IntegrationTestCase
{
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
