<?php

namespace Tests\Integration\Api\Remote;

use App\Models\Backup;
use App\Models\Server;
use App\Repositories\Agent\DaemonBackupRepository;
use Carbon\CarbonImmutable;
use GuzzleHttp\Psr7\Response;
use Tests\Integration\IntegrationTestCase;

class BackupStatusControllerTest extends IntegrationTestCase
{
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
    }

    public function test_successful_replacement_deletes_oldest_backup(): void
    {
        $server = $this->createServerModel(['backup_limit' => 1]);
        $existing = Backup::factory()->for($server)->create(['created_at' => CarbonImmutable::now()->subHour()]);
        $replacement = Backup::factory()->for($server)->create([
            'is_successful' => false,
            'completed_at' => null,
        ]);

        $this->expectBackupDeletion($existing);

        $this->sendBackupStatus($server, $replacement, true)->assertNoContent();

        $this->assertSoftDeleted($existing);
        $this->assertTrue($replacement->refresh()->is_successful);
    }

    public function test_backup_locked_before_replacement_completes_is_preserved(): void
    {
        $server = $this->createServerModel(['backup_limit' => 1]);
        $existing = Backup::factory()->for($server)->create([
            'created_at' => CarbonImmutable::now()->subHour(),
            'is_locked' => true,
        ]);
        $replacement = Backup::factory()->for($server)->create([
            'is_successful' => false,
            'completed_at' => null,
        ]);

        $this->expectBackupDeletion($replacement);

        $this->sendBackupStatus($server, $replacement, true)->assertNoContent();

        $this->assertNotSoftDeleted($existing);
        $this->assertSoftDeleted($replacement);
    }

    private function expectBackupDeletion(Backup $expected): void
    {
        $repository = $this->mock(DaemonBackupRepository::class);
        $repository->expects('setServer')->andReturnSelf();
        $repository->expects('delete')->with(\Mockery::on(function ($backup) use ($expected) {
            return $backup instanceof Backup && $backup->id === $expected->id;
        }))->andReturn(new Response());
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
