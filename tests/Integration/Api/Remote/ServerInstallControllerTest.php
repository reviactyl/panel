<?php

namespace Tests\Integration\Api\Remote;

use App\Events\Server\Installed as ServerInstalled;
use App\Exceptions\Http\Server\ServerStateConflictException;
use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Servers\ReinstallServerService;
use App\Services\Servers\SuspensionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class ServerInstallControllerTest extends IntegrationTestCase
{
    public function test_failed_initial_install_records_a_failure_event_without_marking_server_installed(): void
    {
        Event::fake(ServerInstalled::class);
        $server = $this->createServerModel();

        $this->sendInstallStatus($server, false, false)->assertNoContent();

        $server->refresh();

        $this->assertSame(Server::STATUS_INSTALL_FAILED, $server->status);
        $this->assertNull($server->installed_at);
        Event::assertDispatched(ServerInstalled::class, fn ($event) => ! $event->successful);
    }

    public function test_successful_retry_after_failed_initial_install_uses_initial_install_notification(): void
    {
        Event::fake(ServerInstalled::class);
        config()->set('panel.email.send_install_notification', true);
        config()->set('panel.email.send_reinstall_notification', false);
        $server = $this->createServerModel();

        $this->sendInstallStatus($server, false, false)->assertNoContent();
        $this->sendInstallStatus($server, true, false)->assertNoContent();

        $server->refresh();

        $this->assertNull($server->status);
        $this->assertNotNull($server->installed_at);
        Event::assertDispatchedTimes(ServerInstalled::class, 2);
    }

    public function test_failed_reinstall_preserves_installed_at_and_records_a_failure_event(): void
    {
        Event::fake(ServerInstalled::class);
        $installedAt = CarbonImmutable::parse('2026-08-18 12:00:00');
        $server = $this->createServerModel(['installed_at' => $installedAt]);

        $this->sendInstallStatus($server, false, true)->assertNoContent();

        $server->refresh();

        $this->assertSame(Server::STATUS_REINSTALL_FAILED, $server->status);
        $this->assertTrue($installedAt->equalTo($server->installed_at));
        Event::assertDispatched(ServerInstalled::class, fn ($event) => ! $event->successful);
    }

    public function test_disabling_install_email_keeps_the_inbox_notification(): void
    {
        Event::fake(ServerInstalled::class);
        config()->set('panel.email.send_install_notification', false);
        $server = $this->createServerModel();
        $notification = new \App\Notifications\ServerInstalled();
        $notification->server = $server;
        $notification->sendEmail = false;
        $this->assertSame(['database'], $notification->via());

        $this->sendInstallStatus($server, true, false)->assertNoContent();
        Event::assertDispatched(ServerInstalled::class, fn ($event) => $event->sendEmail === false);
    }

    public static function reinstallStates(): array
    {
        return [
            'suspended success' => [Server::STATUS_SUSPENDED, true, Server::STATUS_SUSPENDED],
            'suspended failure' => [Server::STATUS_SUSPENDED, false, Server::STATUS_SUSPENDED],
            'ordinary success' => [null, true, null],
            'ordinary failure' => [null, false, Server::STATUS_REINSTALL_FAILED],
        ];
    }

    #[DataProvider('reinstallStates')]
    public function test_reinstall_through_completion_preserves_suspension(?string $initialStatus, bool $successful, ?string $finalStatus): void
    {
        Event::fake(ServerInstalled::class);
        $server = $this->createServerModel([
            'status' => $initialStatus,
            'skip_scripts' => false,
            'installed_at' => CarbonImmutable::parse('2026-08-18 12:00:00'),
        ]);
        $duringReinstall = $initialStatus === Server::STATUS_SUSPENDED ? $initialStatus : Server::STATUS_INSTALLING;
        $daemon = Mockery::mock(DaemonServerRepository::class);
        $daemon->shouldReceive('setServer')->once()->withArgs(function (Server $outgoing) use ($server, $duringReinstall) {
            $this->assertSame($server->id, $outgoing->id);
            $this->assertSame($duringReinstall, $outgoing->status);
            $this->assertSame($duringReinstall, $server->fresh()->status);

            return true;
        })->andReturnSelf();
        $daemon->shouldReceive('reinstall')->once();
        $this->app->instance(DaemonServerRepository::class, $daemon);

        $result = $this->app->make(ReinstallServerService::class)->handle($server);
        $this->assertSame($duringReinstall, $result->status);
        $this->assertTrue($result->is_reinstalling);
        $this->assertFalse($result->isInstalled());
        $this->assertTransferBlocked($result);

        $this->sendInstallStatus($server, $successful, true)->assertNoContent();
        $this->assertSame($finalStatus, $server->fresh()->status);
        $this->assertFalse($server->fresh()->is_reinstalling);
        $server->fresh()->validateTransferState();
    }

    public function test_unsuspending_during_reinstall_keeps_transfers_and_client_access_blocked_until_completion(): void
    {
        Event::fake(ServerInstalled::class);
        $server = $this->createServerModel(['status' => Server::STATUS_SUSPENDED, 'skip_scripts' => false]);
        $daemon = Mockery::mock(DaemonServerRepository::class);
        $daemon->shouldReceive('setServer')->twice()->with(Mockery::type(Server::class))->andReturnSelf();
        $daemon->shouldReceive('reinstall')->once();
        $daemon->shouldReceive('sync')->once();
        $this->app->instance(DaemonServerRepository::class, $daemon);

        $this->app->make(ReinstallServerService::class)->handle($server);
        $this->app->make(SuspensionService::class)->toggle($server, SuspensionService::ACTION_UNSUSPEND);
        $server->refresh();
        $this->assertNull($server->status);
        $this->assertSame(Server::STATUS_INSTALLING, $server->getResolvedStatus());
        $this->assertTransferBlocked($server);
        try {
            $server->validateCurrentState();
            $this->fail('Client access must remain blocked during reinstall.');
        } catch (ServerStateConflictException) {
            $this->assertTrue($server->is_reinstalling);
        }

        $this->sendInstallStatus($server, true, true)->assertNoContent();
        $server->refresh();
        $this->assertFalse($server->is_reinstalling);
        $server->validateTransferState();
        $server->validateCurrentState();
    }

    private function assertTransferBlocked(Server $server): void
    {
        try {
            $server->validateTransferState();
            $this->fail('Transfers must remain blocked during reinstall.');
        } catch (ServerStateConflictException) {
            $this->assertFalse($server->isInstalled());
        }
    }

    private function sendInstallStatus(Server $server, bool $successful, bool $reinstall)
    {
        return $this
            ->withHeader('Authorization', "Bearer {$server->node->daemon_token_id}.{$server->node->getDecryptedKey()}")
            ->postJson("/api/remote/servers/{$server->uuid}/install", [
                'successful' => $successful,
                'reinstall' => $reinstall,
            ]);
    }
}
