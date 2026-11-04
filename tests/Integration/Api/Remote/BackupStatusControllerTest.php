<?php

namespace Tests\Integration\Api\Remote;

use App\Events\ActivityLogged;
use App\Events\Server\Updated as ServerUpdated;
use App\Jobs\Backups\RotateBackupsJob;
use App\Models\Backup;
use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Servers\SuspensionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class BackupStatusControllerTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    public function test_failed_replacement_preserves_existing_backup(): void
    {
        $server = $this->createServerModel(['backup_limit' => 1]);
        $existing = Backup::factory()->for($server)->create(['created_at' => CarbonImmutable::now()->subHour()]);
        $replacement = Backup::factory()->for($server)->create([
            'is_successful' => false,
            'completed_at' => null,
        ]);

        $this->sendBackupStatus($server, $replacement, false)->assertNoContent();

        $this->assertNotSoftDeleted($existing);
        $this->assertFalse($replacement->refresh()->is_successful);
        $this->assertNotNull($replacement->completed_at);
        Queue::assertNotPushed(RotateBackupsJob::class);
    }

    public function test_successful_replacement_queues_backup_rotation(): void
    {
        $server = $this->createServerModel(['backup_limit' => 1]);
        $existing = Backup::factory()->for($server)->create(['created_at' => CarbonImmutable::now()->subHour()]);
        $replacement = Backup::factory()->for($server)->create([
            'is_successful' => false,
            'completed_at' => null,
        ]);

        $this->sendBackupStatus($server, $replacement, true)->assertNoContent();

        $this->assertNotSoftDeleted($existing);
        $this->assertTrue($replacement->refresh()->is_successful);
        Queue::assertPushed(RotateBackupsJob::class, function (RotateBackupsJob $job) use ($server, $replacement) {
            return $job->serverId === $server->id
                && $job->backupId === $replacement->id
                && $job->tries === 3;
        });
    }

    public function test_successful_status_retry_queues_rotation_again(): void
    {
        $server = $this->createServerModel(['backup_limit' => 1]);
        $replacement = Backup::factory()->for($server)->create();

        $this->sendBackupStatus($server, $replacement, true)->assertNoContent();

        Queue::assertPushed(RotateBackupsJob::class, function (RotateBackupsJob $job) use ($replacement) {
            return $job->backupId === $replacement->id;
        });
    }

    public static function restorationOutcomes(): array
    {
        return [
            'successful' => [true],
            'failed' => [false],
        ];
    }

    #[DataProvider('restorationOutcomes')]
    public function test_restore_completion_preserves_intervening_suspension(bool $successful): void
    {
        $server = $this->createServerModel(['status' => Server::STATUS_RESTORING_BACKUP]);
        $backup = Backup::factory()->for($server)->create();
        $repository = \Mockery::mock(DaemonServerRepository::class);
        $repository->expects('setServer')->with($server)->andReturnSelf();
        $repository->expects('sync')->andReturnSelf();
        $this->app->instance(DaemonServerRepository::class, $repository);

        $this->app->make(SuspensionService::class)->toggle($server);
        $this->assertTrue($server->refresh()->isSuspended());
        Event::fake([ActivityLogged::class, ServerUpdated::class]);

        $this->sendRestoreStatus($server, $backup, $successful)->assertNoContent();

        $this->assertTrue($server->refresh()->isSuspended());
        $this->assertActivityLogged($successful ? 'server:backup.restore-complete' : 'server:backup.restore-failed');
        Event::assertNotDispatched(ServerUpdated::class);
    }

    #[DataProvider('restorationOutcomes')]
    public function test_restore_completion_preserves_suspension_after_server_is_loaded(bool $successful): void
    {
        $server = $this->createServerModel(['status' => Server::STATUS_RESTORING_BACKUP]);
        $backup = Backup::factory()->for($server)->create();
        $suspended = false;
        Event::listen('eloquent.retrieved: '.Server::class, function (Server $loaded) use ($server, &$suspended) {
            if (! $suspended && $loaded->is($server)) {
                $suspended = true;
                $server->update(['status' => Server::STATUS_SUSPENDED]);
            }
        });

        $this->sendRestoreStatus($server, $backup, $successful)->assertNoContent();

        $this->assertTrue($suspended);
        $this->assertTrue($server->refresh()->isSuspended());
    }

    #[DataProvider('restorationOutcomes')]
    public function test_restore_completion_retries_after_concurrent_sqlite_suspension(bool $successful): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('SQLite snapshot conflict regression.');
        }

        $server = $this->createServerModel(['status' => Server::STATUS_RESTORING_BACKUP]);
        $backup = Backup::factory()->for($server)->create();
        DB::statement('PRAGMA journal_mode=WAL');
        $writer = new \PDO('sqlite:'.DB::connection()->getDatabaseName());
        $suspended = false;
        Event::listen('eloquent.retrieved: '.Server::class, function (Server $loaded) use ($server, $writer, &$suspended) {
            if (! $suspended && $loaded->is($server) && DB::transactionLevel() > 0) {
                $suspended = true;
                $statement = $writer->prepare('UPDATE servers SET status = ? WHERE id = ?');
                $statement->execute([Server::STATUS_SUSPENDED, $server->id]);
            }
        });

        $this->sendRestoreStatus($server, $backup, $successful)->assertNoContent();

        $this->assertTrue($suspended);
        $this->assertTrue($server->refresh()->isSuspended());
        $this->assertActivityLogged($successful ? 'server:backup.restore-complete' : 'server:backup.restore-failed');
    }

    #[DataProvider('restorationOutcomes')]
    public function test_restore_completion_clears_restoring_status(bool $successful): void
    {
        $server = $this->createServerModel(['status' => Server::STATUS_RESTORING_BACKUP]);
        $backup = Backup::factory()->for($server)->create();
        Event::fake([ActivityLogged::class, ServerUpdated::class]);

        $this->sendRestoreStatus($server, $backup, $successful)->assertNoContent();

        $this->assertNull($server->refresh()->status);
        $this->assertActivityLogged($successful ? 'server:backup.restore-complete' : 'server:backup.restore-failed');
        Event::assertDispatched(ServerUpdated::class, function (ServerUpdated $event) use ($server) {
            return $event->server->is($server) && $event->server->status === null;
        });
    }

    private function sendRestoreStatus(Server $server, Backup $backup, bool $successful)
    {
        return $this
            ->withHeader('Authorization', "Bearer {$server->node->daemon_token_id}.{$server->node->getDecryptedKey()}")
            ->postJson("/api/remote/backups/{$backup->uuid}/restore", ['successful' => $successful]);
    }

    private function sendBackupStatus(Server $server, Backup $backup, bool $successful)
    {
        $data = ['successful' => $successful];
        if ($successful) {
            $data += [
                'checksum' => 'test',
                'checksum_type' => 'sha256',
                'size' => 123,
            ];
        }

        return $this
            ->withHeader('Authorization', "Bearer {$server->node->daemon_token_id}.{$server->node->getDecryptedKey()}")
            ->postJson("/api/remote/backups/{$backup->uuid}", $data);
    }
}
