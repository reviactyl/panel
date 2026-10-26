<?php

namespace Tests\Unit\Services\Eggs;

use App\Models\Egg;
use App\Models\Server;
use App\Services\Eggs\EggConfigurationService;
use App\Services\Servers\ServerConfigurationStructureService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EggConfigurationServiceTest extends TestCase
{
    #[DataProvider('placeholders')]
    public function test_it_resolves_server_placeholders(string $placeholder, string $expected): void
    {
        $result = $this->render(['value' => 'prefix {{'.$placeholder.'}} suffix']);

        $this->assertSame('prefix '.$expected.' suffix', $result[0]['replace'][0]['replace_with']);
    }

    public static function placeholders(): array
    {
        return [
            'legacy env port' => ['env.SERVER_PORT', '25565'],
            'legacy build env port' => ['server.build.env.SERVER_PORT', '25565'],
            'legacy env memory' => ['env.SERVER_MEMORY', '2048'],
            'legacy build env memory' => ['server.build.env.SERVER_MEMORY', '2048'],
            'legacy env ip' => ['env.SERVER_IP', '192.0.2.10'],
            'legacy build env ip' => ['server.build.env.SERVER_IP', '192.0.2.10'],
            'current port' => ['server.build.default.port', '25565'],
            'current memory' => ['server.build.memory', '2048'],
            'current ip' => ['server.build.default.ip', '192.0.2.10'],
            'environment variable' => ['env.CUSTOM_VALUE', 'custom'],
            'server environment variable' => ['server.build.env.CUSTOM_VALUE', 'custom'],
        ];
    }

    public function test_it_resolves_mixed_and_nested_placeholders_and_preserves_daemon_keys(): void
    {
        $result = $this->render([
            'address' => '{{env.SERVER_IP}}:{{server.build.env.SERVER_PORT}}/{{server.build.default.port}}',
            'memory' => (object) ['old' => '{{env.SERVER_MEMORY}}/{{server.build.env.SERVER_MEMORY}}'],
            'interfaces' => '{{config.docker.interface}}/{{config.docker.network.interface}}',
        ]);

        $this->assertSame([
            ['match' => 'address', 'replace_with' => '192.0.2.10:25565/25565'],
            ['match' => 'memory', 'if_value' => 'old', 'replace_with' => '2048/2048'],
            ['match' => 'interfaces', 'replace_with' => '{{config.docker.network.interface}}/{{config.docker.network.interface}}'],
        ], $result[0]['replace']);
    }

    private function render(array $find): array
    {
        $server = new Server();
        $server->setRelation('egg', new Egg([
            'config_files' => json_encode(['server.properties' => ['parser' => 'properties', 'find' => $find]], JSON_THROW_ON_ERROR),
            'config_startup' => '{"done":"Ready"}',
            'config_stop' => 'stop',
        ]));

        $structure = $this->createMock(ServerConfigurationStructureService::class);
        $structure->expects($this->once())->method('handle')->with($server, [], true)->willReturn([
            'build' => [
                'memory' => 2048,
                'default' => ['ip' => '192.0.2.10', 'port' => 25565],
                'env' => ['CUSTOM_VALUE' => 'custom'],
            ],
        ]);

        return (new EggConfigurationService($structure))->handle($server)['configs'];
    }
}
