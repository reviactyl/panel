<?php

namespace Tests\Integration\Filament;

use App\Filament\Resources\Nodes\Pages\EditNode;
use App\Models\ApiKey;
use App\Models\Location;
use App\Models\Node;
use App\Models\User;
use App\Services\Acl\Api\AdminAcl;
use App\Services\Api\KeyCreationService;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class EditNodeTest extends IntegrationTestCase
{
    public static function existingNodePermissions(): array
    {
        return [
            'no key' => [null],
            'no access' => [AdminAcl::NONE],
            'legacy read-only key' => [AdminAcl::READ],
            'write-only key' => [AdminAcl::WRITE],
            'read-write key' => [AdminAcl::READ | AdminAcl::WRITE],
        ];
    }

    #[DataProvider('existingNodePermissions')]
    public function test_deployment_command_uses_a_key_authorized_to_fetch_configuration(?int $permission): void
    {
        $user = User::factory()->create(['root_admin' => true]);
        $node = Node::factory()->for(Location::factory())->create();
        $existingKey = $permission === null ? null : app(KeyCreationService::class)
            ->setKeyType(ApiKey::TYPE_APPLICATION)
            ->handle(['user_id' => $user->id, 'memo' => 'Existing key'], ['r_nodes' => $permission]);
        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $page = Livewire::test(EditNode::class, ['record' => $node->id])
            ->mountAction(TestAction::make('generateToken')->schemaComponent())
            ->assertActionMounted(TestAction::make('generateToken')->schemaComponent());
        $command = $page->get('mountedActions')[0]['data']['command'];
        $this->assertMatchesRegularExpression('/--token (\S+) --node '.$node->id.'(?:\s|$)/', $command);
        preg_match('/--token (\S+)/', $command, $matches);
        $token = $matches[1];
        $key = ApiKey::query()->where('identifier', substr($token, 0, ApiKey::IDENTIFIER_LENGTH))->sole();
        $this->assertTrue(AdminAcl::check($key, AdminAcl::RESOURCE_NODES, AdminAcl::WRITE));
        $canReuse = $permission !== null && AdminAcl::can($permission, AdminAcl::WRITE);
        $this->assertDatabaseCount('api_keys', $existingKey && ! $canReuse ? 2 : 1);
        if ($canReuse) {
            $this->assertTrue($existingKey->is($key));
        } else {
            $this->assertSame(AdminAcl::READ | AdminAcl::WRITE, $key->r_nodes);
            foreach (AdminAcl::getResourceList() as $resource) {
                if ($resource !== AdminAcl::RESOURCE_NODES) {
                    $this->assertSame(AdminAcl::NONE, $key->getAttribute('r_'.$resource));
                }
            }
        }
        if ($existingKey) {
            $this->assertSame($permission, $existingKey->fresh()->r_nodes);
        }

        auth()->forgetGuards();
        $this->withToken($token)->getJson('/api/application/nodes/'.$node->id.'/configuration')
            ->assertOk()
            ->assertJsonPath('uuid', $node->uuid);
    }

    public function test_read_only_node_key_cannot_fetch_agent_credentials(): void
    {
        $user = User::factory()->create(['root_admin' => true]);
        $node = Node::factory()->for(Location::factory())->create();
        $key = app(KeyCreationService::class)->setKeyType(ApiKey::TYPE_APPLICATION)
            ->handle(['user_id' => $user->id, 'memo' => 'Read only'], ['r_nodes' => AdminAcl::READ]);

        $this->withToken($key->identifier.decrypt($key->token))
            ->getJson('/api/application/nodes/'.$node->id.'/configuration')->assertForbidden();
    }
}
