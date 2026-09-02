<?php

namespace Tests\Integration\Api\Remote;

use App\Jobs\Backups\RotateBackupsJob;
use App\Models\Backup;
use App\Models\Server;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;
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
