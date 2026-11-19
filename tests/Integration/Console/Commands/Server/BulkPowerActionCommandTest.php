<?php

namespace Tests\Integration\Console\Commands\Server;

use App\Models\Server;
use App\Repositories\Agent\DaemonPowerRepository;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class BulkPowerActionCommandTest extends IntegrationTestCase
{
    #[DataProvider('selectorAndStatusProvider')]
    public function test_only_eligible_servers_receive_power_actions(string $selector, string $status): void
    {
        $serverMatch = $this->createServerModel(['status' => null]);
        $nodeMatch = $this->createServerModel(['status' => null]);
        $unmatched = $this->createServerModel(['status' => null]);
        $blockedServer = $this->createServerModel(['node_id' => $serverMatch->node_id, 'status' => $status]);
        $blockedNode = $this->createServerModel(['node_id' => $nodeMatch->node_id, 'status' => $status]);
        $blockedBoth = $this->createServerModel(['node_id' => $nodeMatch->node_id, 'status' => $status]);

        $arguments = ['action' => 'restart'];
        if (in_array($selector, ['servers', 'combined'])) {
            $arguments['--servers'] = implode(',', [$serverMatch->id, $blockedServer->id, $blockedBoth->id]);
        }
        if (in_array($selector, ['nodes', 'combined'])) {
            $arguments['--nodes'] = (string) $nodeMatch->node_id;
        }

        $expectedIds = match ($selector) {
            'servers' => [$serverMatch->id],
            'nodes' => [$nodeMatch->id],
            'combined' => [$serverMatch->id, $nodeMatch->id],
            'all' => [$serverMatch->id, $nodeMatch->id, $unmatched->id],
        };
        $receivedIds = [];
        $repository = $this->mock(DaemonPowerRepository::class);
        $repository->expects('setServer')->times(count($expectedIds))
            ->with(Mockery::on(function (Server $server) use (&$receivedIds): bool {
                $receivedIds[] = $server->id;

                return true;
            }))->andReturnSelf();
        $repository->expects('send')->with('restart')->times(count($expectedIds))->andReturn(new Response());

        $this->artisan('p:server:bulk-power', $arguments)
            ->expectsConfirmation(trans('command/messages.server.power.confirm', [
                'action' => 'restart',
                'count' => count($expectedIds),
            ]), 'yes')
            ->assertSuccessful();

        $this->assertSame($expectedIds, $receivedIds);
    }

    public static function selectorAndStatusProvider(): array
    {
        $cases = [];
        foreach (['combined', 'servers', 'nodes', 'all'] as $selector) {
            foreach ([Server::STATUS_SUSPENDED, Server::STATUS_INSTALLING, Server::STATUS_RESTORING_BACKUP, Server::STATUS_INSTALL_FAILED, Server::STATUS_REINSTALL_FAILED] as $status) {
                $cases["$selector / $status"] = [$selector, $status];
            }
        }

        return $cases;
    }
}
