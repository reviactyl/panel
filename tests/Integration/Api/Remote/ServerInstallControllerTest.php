<?php

namespace Tests\Integration\Api\Remote;

use App\Events\Server\Installed as ServerInstalled;
use App\Models\Server;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Tests\Integration\IntegrationTestCase;

class ServerInstallControllerTest extends IntegrationTestCase
{
    public function test_failed_initial_install_does_not_mark_server_installed_or_dispatch_event(): void
    {
        Event::fake(ServerInstalled::class);
        $server = $this->createServerModel();

        $this->sendInstallStatus($server, false, false)->assertNoContent();

        $server->refresh();

        $this->assertSame(Server::STATUS_INSTALL_FAILED, $server->status);
        $this->assertNull($server->installed_at);
        Event::assertNotDispatched(ServerInstalled::class);
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
        Event::assertDispatchedTimes(ServerInstalled::class, 1);
    }

    public function test_failed_reinstall_preserves_installed_at_and_does_not_dispatch_event(): void
    {
        Event::fake(ServerInstalled::class);
        $installedAt = CarbonImmutable::parse('2026-08-18 12:00:00');
        $server = $this->createServerModel(['installed_at' => $installedAt]);

        $this->sendInstallStatus($server, false, true)->assertNoContent();

        $server->refresh();

        $this->assertSame(Server::STATUS_REINSTALL_FAILED, $server->status);
        $this->assertTrue($installedAt->equalTo($server->installed_at));
        Event::assertNotDispatched(ServerInstalled::class);
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
