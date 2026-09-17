<?php

namespace Tests\Integration\Services\Servers;

use App\Models\Server;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Servers\SuspensionService;
use Mockery\MockInterface;
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

    private function getService(): SuspensionService
    {
        return $this->app->make(SuspensionService::class);
    }
}
