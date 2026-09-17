<?php

namespace Tests\Integration\Api\Client;

use App\Models\Extension;
use App\Models\User;
use App\Services\Extensions\ExtensionManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ExtensionsControllerTest extends ClientApiIntegrationTestCase
{
    /** @var string[] */
    private array $extensionIds = [];

    protected function tearDown(): void
    {
        foreach ($this->extensionIds as $identifier) {
            File::deleteDirectory(base_path('extensions/'.$identifier));
        }

        parent::tearDown();
    }

    public function test_extensions_registry_returns_only_enabled_extensions(): void
    {
        $enabledId = 'client-enabled-'.Str::lower(Str::random(6));
        $disabledId = 'client-disabled-'.Str::lower(Str::random(6));

        $this->extensionIds[] = $enabledId;
        $this->extensionIds[] = $disabledId;

        Extension::query()->create([
            'identifier' => $enabledId,
            'name' => 'Enabled Extension',
            'version' => '2.1.0',
            'enabled' => true,
            'api_version' => ExtensionManager::API_VERSION,
            'manifest' => [
                'id' => $enabledId,
                'name' => 'Enabled Extension',
                'version' => '2.1.0',
                'api_version' => ExtensionManager::API_VERSION,
                'permissions' => ['extensions.use'],
                'feature_flags' => ['feature-a'],
                'frontend' => [
                    'entry_points' => ['frontend/dist/entry.js'],
                    'slots' => [
                        ['name' => 'dashboard.after-cards', 'module' => 'frontend/dist/slot.js'],
                    ],
                    'routes' => [
                        'dashboardRouter' => [
                            ['path' => '/ext', 'module' => 'frontend/dist/dashboard.js'],
                        ],
                        'serverRouter' => [
                            ['path' => '/ext/server', 'module' => 'frontend/dist/server.js'],
                        ],
                    ],
                ],
            ],
        ]);

        Extension::query()->create([
            'identifier' => $disabledId,
            'name' => 'Disabled Extension',
            'version' => '1.0.0',
            'enabled' => false,
            'api_version' => ExtensionManager::API_VERSION,
            'manifest' => [
                'id' => $disabledId,
                'name' => 'Disabled Extension',
                'version' => '1.0.0',
                'api_version' => ExtensionManager::API_VERSION,
            ],
        ]);

        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/client/extensions');

        $response->assertOk();
        $response->assertJsonPath('object', 'list');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $enabledId);
        $response->assertJsonPath('data.0.name', 'Enabled Extension');
        $response->assertJsonPath('data.0.version', '2.1.0');
        $response->assertJsonPath('data.0.permissions.0', 'extensions.use');
        $response->assertJsonPath('data.0.feature_flags.0', 'feature-a');
        $response->assertJsonPath('data.0.frontend.entry_points.0', 'frontend/dist/entry.js');
        $response->assertJsonPath('data.0.frontend.slots.0.module', 'frontend/dist/slot.js');
        $response->assertJsonPath('data.0.frontend.routes.dashboardRouter.0.module', 'frontend/dist/dashboard.js');
        $response->assertJsonPath('data.0.frontend.routes.serverRouter.0.module', 'frontend/dist/server.js');
    }

    public function test_extensions_registry_requires_authenticated_user(): void
    {
        $this->getJson('/api/client/extensions')->assertUnauthorized();
    }
}
