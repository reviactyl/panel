<?php

namespace Tests\Integration\Api\Client\Server\Backup;

use App\Models\Backup;
use App\Repositories\Agent\DaemonBackupRepository;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class CreateBackupTest extends ClientApiIntegrationTestCase
{
    public function test_pending_backups_count_toward_the_limit(): void
    {
        $server = $this->createServerModel(['backup_limit' => 2]);
        config()->set('backups.throttles.period', 0);
        $this->mock(DaemonBackupRepository::class)->expects('setServer->setBackupAdapter->backup')
            ->twice()->andReturn(new Response());

        $this->actingAs($server->user);
        $url = $this->link($server, 'backups');
        $this->postJson($url)->assertOk();
        $this->postJson($url)->assertOk();
        $this->postJson($url)->assertStatus(400)->assertJsonPath('errors.0.code', 'TooManyBackupsException');
        $this->assertSame(2, $server->backups()->whereNull('completed_at')->count());
    }

    public function test_concurrent_manual_requests_cannot_claim_the_same_remaining_slot(): void
    {
        if (! function_exists('pcntl_fork')) {
            $this->markTestSkipped('Requires pcntl for independent concurrent requests.');
        }
        if (DB::getDatabaseName() === ':memory:') {
            $this->markTestSkipped('Requires a shared test database.');
        }

        $server = $this->createServerModel(['backup_limit' => 2]);
        Backup::factory()->create(['server_id' => $server->id, 'is_successful' => true, 'completed_at' => now()]);
        config()->set('backups.throttles.limit', 3);
        config()->set('backups.throttles.period', 60);
        $this->actingAs($server->user);
        $url = $this->link($server, 'backups');
        [$parent, $child] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
        stream_set_timeout($parent, 10);
        stream_set_timeout($child, 10);
        DB::disconnect();
        $pid = pcntl_fork();
        $this->assertNotSame(-1, $pid);

        if ($pid === 0) {
            fclose($parent);
            $this->mock(DaemonBackupRepository::class)->shouldReceive('setServer->setBackupAdapter->backup')
                ->once()->andReturnUsing(function () use ($child) {
                    fwrite($child, "pending\n");
                    fgets($child);
                    usleep(500000);

                    return new Response();
                });
            try {
                $response = $this->postJson($url);
                fwrite($child, $response->status()."\n");
            } catch (\Throwable $exception) {
                fwrite($child, get_class($exception).': '.$exception->getMessage()."\n");
            }
            fclose($child);
            exit(0);
        }

        fclose($child);
        try {
            $this->assertSame("pending\n", fgets($parent));
            $this->mock(DaemonBackupRepository::class)->shouldNotReceive('setServer');
            fwrite($parent, "competing\n");
            $response = $this->postJson($url);
            $firstStatus = fgets($parent);
            $response->assertStatus(400)->assertJsonPath('errors.0.code', 'TooManyBackupsException');
            $this->assertSame("200\n", $firstStatus);
            $this->assertSame(2, $server->backups()->count());
            $this->assertSame(1, $server->backups()->whereNull('completed_at')->count());
        } finally {
            fclose($parent);
            pcntl_waitpid($pid, $status);
        }
    }
}
