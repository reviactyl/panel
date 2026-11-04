<?php

namespace Tests\Integration\Services\Servers;

use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Servers\SuspensionService;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class SuspensionServiceTest extends IntegrationTestCase
{
    private MockInterface $repository;

    /**
     * Setup test instance.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = \Mockery::mock(DaemonServerRepository::class);
        $this->app->instance(DaemonServerRepository::class, $this->repository);
    }

    public function test_server_is_suspended_and_unsuspended()
    {
        $server = $this->createServerModel();

        $this->repository->expects('setServer->sync')->twice()->andReturnSelf();

        $this->getService()->toggle($server);

        $this->assertTrue($server->refresh()->isSuspended());

        $this->getService()->toggle($server, SuspensionService::ACTION_UNSUSPEND);

        $this->assertFalse($server->refresh()->isSuspended());
    }

    public function test_no_action_is_taken_if_suspension_status_is_unchanged()
    {
        $server = $this->createServerModel();

        $this->getService()->toggle($server, SuspensionService::ACTION_UNSUSPEND);

        $server->refresh();
        $this->assertFalse($server->isSuspended());

        $server->update(['status' => Server::STATUS_SUSPENDED]);
        $this->getService()->toggle($server);

        $server->refresh();
        $this->assertTrue($server->isSuspended());
    }

    public function test_exception_is_thrown_if_invalid_actions_are_passed()
    {
        $server = $this->createServerModel();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected one of: "suspend", "unsuspend". Got: "foo"');

        $this->getService()->toggle($server, 'foo');
    }

    public static function failedSyncStatuses(): array
    {
        return [
            'installed' => [null, SuspensionService::ACTION_SUSPEND],
            'install failed' => [Server::STATUS_INSTALL_FAILED, SuspensionService::ACTION_SUSPEND],
            'reinstall failed' => [Server::STATUS_REINSTALL_FAILED, SuspensionService::ACTION_SUSPEND],
            'installing' => [Server::STATUS_INSTALLING, SuspensionService::ACTION_SUSPEND],
            'suspended' => [Server::STATUS_SUSPENDED, SuspensionService::ACTION_UNSUSPEND],
        ];
    }

    #[DataProvider('failedSyncStatuses')]
    public function test_failed_sync_restores_previous_status(?string $status, string $action): void
    {
        $server = $this->createServerModel();
        $server->update(['status' => $status]);
        $exception = new \RuntimeException('Agent unavailable');

        $this->repository->expects('setServer')->with($server)->andReturnSelf();
        $this->repository->expects('sync')->andReturnUsing(function () use ($server, $action, $exception) {
            $this->assertSame(
                $action === SuspensionService::ACTION_SUSPEND ? Server::STATUS_SUSPENDED : null,
                $server->fresh()->status,
            );

            throw $exception;
        });

        try {
            $this->getService()->toggle($server, $action);
            $this->fail('Expected Agent sync to fail.');
        } catch (\RuntimeException $caught) {
            $this->assertSame($exception, $caught);
        }

        $this->assertSame($status, $server->status);
        $this->assertSame($status, $server->fresh()->status);
    }

    private function getService(): SuspensionService
    {
        return $this->app->make(SuspensionService::class);
    }
}
