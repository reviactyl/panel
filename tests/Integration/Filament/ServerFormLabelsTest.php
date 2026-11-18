<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Servers\Pages\CreateServer;
use App\Filament\Resources\Servers\Pages\EditServer;
use App\Models\Allocation;
use App\Models\EggVariable;
use App\Models\Server;
use App\Models\ServerVariable;
use App\Models\User;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Servers\ServerConfigurationStructureService;
use DOMDocument;
use DOMXPath;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class ServerFormLabelsTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['root_admin' => true]));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_startup_labels_render_one_required_marker_and_preserve_values_in_both_forms(): void
    {
        $server = $this->createServerModel();
        $variable = $server->egg->variables->first();
        $variable->update(['name' => 'Required startup value', 'rules' => 'required|string', 'default_value' => 'default-value']);
        EggVariable::factory()->create([
            'egg_id' => $server->egg_id,
            'name' => 'Optional startup value',
            'env_variable' => 'OPTIONAL_VALUE',
            'rules' => 'nullable|string',
            'default_value' => '',
        ]);
        ServerVariable::query()->create([
            'server_id' => $server->id,
            'variable_id' => $variable->id,
            'variable_value' => 'saved-value',
        ]);

        $create = Livewire::test(CreateServer::class)
            ->fillForm(['nest_id' => $server->nest_id, 'egg_id' => $server->egg_id])
            ->assertFormSet(['environment.'.$variable->env_variable => 'default-value']);
        $edit = Livewire::test(EditServer::class, ['record' => $server->id])
            ->assertFormSet(['environment.'.$variable->env_variable => 'saved-value']);

        foreach ([$create, $edit] as $page) {
            $document = new DOMDocument();
            @$document->loadHTML($page->html());
            $xpath = new DOMXPath($document);
            foreach (['Required startup value' => 1, 'Optional startup value' => 0] as $label => $markers) {
                $labels = $xpath->query('//label[contains(@class, "fi-fo-field-label")][contains(., "'.$label.'")]');
                $this->assertCount(1, $labels);
                $this->assertSame($markers, substr_count($labels->item(0)->textContent, '*'));
            }

            $page->fillForm(['environment.'.$variable->env_variable => ''])
                ->call($page === $create ? 'create' : 'save')
                ->assertHasFormErrors(['environment.'.$variable->env_variable => 'required']);
        }
        $this->assertSame('saved-value', $server->variables()->findOrFail($variable->id)->server_value);
    }

    #[DataProvider('createOomStates')]
    public function test_create_oom_control_persists_the_displayed_state(?bool $state): void
    {
        $template = $this->createServerModel();
        $allocation = Allocation::factory()->create(['node_id' => $template->node_id]);
        $repository = $this->mock(DaemonServerRepository::class);
        $repository->allows('setServer')->andReturnSelf();
        $repository->expects('create')->with(false)->andReturnUndefined();

        $page = Livewire::test(CreateServer::class)
            ->assertFormSet(['oom_disabled' => true])
            ->assertSee('Disable OOM Killer')
            ->assertSee(trans('admin/server.fields.oom_disabled.helper'))
            ->fillForm([
                'name' => 'OOM create regression',
                'owner_id' => $template->owner_id,
                'node_id' => $template->node_id,
                'allocation_id' => $allocation->id,
                'nest_id' => $template->nest_id,
                'egg_id' => $template->egg_id,
                'memory' => 128,
                'disk' => 256,
            ]);
        if ($state !== null) {
            $page->fillForm(['oom_disabled' => $state]);
        }
        $page->call('create')->assertHasNoFormErrors();

        $server = Server::query()->where('name', 'OOM create regression')->sole();
        $this->assertSame($state ?? true, $server->oom_disabled);
        $this->assertSame($state ?? true, app(ServerConfigurationStructureService::class)->handle($server)['build']['oom_disabled']);
        Livewire::test(EditServer::class, ['record' => $server->id])
            ->assertFormSet(['oom_disabled' => $state ?? true]);
    }

    public static function createOomStates(): array
    {
        return ['existing default' => [null], 'disabled' => [true], 'enabled' => [false]];
    }

    #[DataProvider('editOomStates')]
    public function test_edit_oom_control_preserves_existing_state_and_saves_changes(bool $state): void
    {
        $server = $this->createServerModel(['oom_disabled' => $state]);
        $repository = $this->mock(DaemonServerRepository::class);
        $repository->allows('setServer')->andReturnSelf();
        $repository->expects('sync')->twice()->andReturnUndefined();

        $page = Livewire::test(EditServer::class, ['record' => $server->id])
            ->assertSee('Disable OOM Killer')
            ->assertSee(trans('admin/server.fields.oom_disabled.helper'))
            ->assertFormSet(['oom_disabled' => $state])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame($state, $server->refresh()->oom_disabled);
        $page->fillForm(['oom_disabled' => ! $state])->call('save')->assertHasNoFormErrors();
        $this->assertSame(! $state, $server->refresh()->oom_disabled);
        $this->assertSame(! $state, app(ServerConfigurationStructureService::class)->handle($server)['build']['oom_disabled']);
        Livewire::test(EditServer::class, ['record' => $server->id])
            ->assertFormSet(['oom_disabled' => ! $state]);
    }

    public static function editOomStates(): array
    {
        return ['disabled' => [true], 'enabled' => [false]];
    }
}
