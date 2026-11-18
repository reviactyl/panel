<?php

namespace Tests\Integration\Api\Remote;

use App\Exceptions\Http\Server\ServerStateConflictException;
use App\Facades\Activity;
use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Servers\ReinstallServerService;
use Carbon\CarbonImmutable;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class ServerResetStateTest extends IntegrationTestCase
{
    public static function suspensionStates(): array
    {
        return [
            'active' => [null],
            'suspended' => [Server::STATUS_SUSPENDED],
        ];
    }

    #[DataProvider('suspensionStates')]
    public function test_agent_restart_recovers_interrupted_reinstall_without_unsuspending(?string $status): void
    {
        $installedAt = CarbonImmutable::parse('2026-08-18 12:00:00');
        $server = $this->createServerModel(['status' => $status, 'skip_scripts' => false, 'installed_at' => $installedAt]);
        $daemon = Mockery::mock(DaemonServerRepository::class);
        $daemon->shouldReceive('setServer')->once()->with(Mockery::type(Server::class))->andReturnSelf();
        $daemon->shouldReceive('reinstall')->once();
        $this->app->instance(DaemonServerRepository::class, $daemon);

        $this->app->make(ReinstallServerService::class)->handle($server);
        $this->assertFalse($server->refresh()->isInstalled());

        $this->resetNode($server)->assertNoContent();

        $server->refresh();
        $this->assertSame($status, $server->status);
        $this->assertFalse($server->is_reinstalling);
        $this->assertTrue($server->isInstalled());
        $this->assertTrue($installedAt->equalTo($server->installed_at));
        $server->validateTransferState();
        if ($status === Server::STATUS_SUSPENDED) {
            $this->expectException(ServerStateConflictException::class);
        }
        $server->validateCurrentState();
    }

    public static function resetStates(): array
    {
        return [
            'initial install' => [Server::STATUS_INSTALLING, false, null],
            'restore' => [Server::STATUS_RESTORING_BACKUP, false, null],
            'suspended reinstall' => [Server::STATUS_SUSPENDED, true, Server::STATUS_SUSPENDED],
            'suspended' => [Server::STATUS_SUSPENDED, false, Server::STATUS_SUSPENDED],
            'orphan reinstall' => [null, true, null],
            'normal' => [null, false, null],
            'failed install' => [Server::STATUS_INSTALL_FAILED, false, Server::STATUS_INSTALL_FAILED],
            'failed reinstall' => [Server::STATUS_REINSTALL_FAILED, false, Server::STATUS_REINSTALL_FAILED],
        ];
    }

    #[DataProvider('resetStates')]
    public function test_reset_is_repeatable_and_isolated_to_authenticated_node(?string $status, bool $reinstalling, ?string $expectedStatus): void
    {
        $attributes = ['status' => $status, 'is_reinstalling' => $reinstalling];
        $server = $this->createServerModel($attributes);
        $otherServer = $this->createServerModel($attributes);
        $otherUpdatedAt = $otherServer->updated_at;

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->resetNode($server)->assertNoContent();

            $this->assertSame($expectedStatus, $server->refresh()->status);
            $this->assertFalse($server->is_reinstalling);
            $this->assertSame($status, $otherServer->refresh()->status);
            $this->assertSame($reinstalling, $otherServer->is_reinstalling);
            $this->assertTrue($otherUpdatedAt->equalTo($otherServer->updated_at));
        }
    }

    public function test_restore_failure_audit_retains_backup_subject_and_is_not_duplicated(): void
    {
        $server = $this->createServerModel(['status' => Server::STATUS_RESTORING_BACKUP]);
        $backup = Backup::factory()->for($server)->create();
        Activity::event('server:backup.restore')->subject($server, $backup)->log();
        $other = $this->createServerModel(['status' => Server::STATUS_RESTORING_BACKUP]);
        $otherBackup = Backup::factory()->for($other)->create();
        Activity::event('server:backup.restore')->subject($other, $otherBackup)->log();

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->resetNode($server)->assertNoContent();

            $failure = ActivityLog::query()->where('event', 'server:backup.restore-failed')->sole();
            $this->assertSame($backup->name, $failure->properties['name']);
            $this->assertTrue($failure->subjects->contains(fn ($subject) => $subject->subject->is($server)));
            $this->assertTrue($failure->subjects->contains(fn ($subject) => $subject->subject->is($backup)));
            $this->assertNull($server->refresh()->status);
            $this->assertSame(Server::STATUS_RESTORING_BACKUP, $other->refresh()->status);
        }
    }

    public function test_reset_requires_valid_node_authentication(): void
    {
        $server = $this->createServerModel(['status' => Server::STATUS_INSTALLING, 'is_reinstalling' => true]);

        $this->postJson('/api/remote/servers/reset')->assertUnauthorized();
        $this->withHeader('Authorization', 'Bearer invalid.token')->postJson('/api/remote/servers/reset')->assertForbidden();

        $this->assertSame(Server::STATUS_INSTALLING, $server->refresh()->status);
        $this->assertTrue($server->is_reinstalling);
    }

    private function resetNode(Server $server)
    {
        return $this->withHeader('Authorization', "Bearer {$server->node->daemon_token_id}.{$server->node->getDecryptedKey()}")
            ->postJson('/api/remote/servers/reset');
    }
}
