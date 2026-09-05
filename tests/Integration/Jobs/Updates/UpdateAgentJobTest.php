<?php

namespace Tests\Integration\Jobs\Updates;

use App\Jobs\Updates\UpdateAgentJob;
use App\Models\Location;
use App\Models\Node;
use App\Repositories\Agent\DaemonConfigurationRepository;
use App\Services\Helpers\SoftwareVersionService;
use App\Services\Updates\SoftwareUpdateStatusService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Mockery;
use RuntimeException;
use Tests\Integration\IntegrationTestCase;

class UpdateAgentJobTest extends IntegrationTestCase
{
    public function test_duplicate_dispatches_only_update_an_agent_once(): void
    {
        Cache::clear();
        $this->beforeApplicationDestroyed(fn () => Cache::clear());
        Queue::fake();
        $node = Node::factory()->for(Location::factory())->create();

        UpdateAgentJob::dispatch($node->id, '26.09.1');
        UpdateAgentJob::dispatch($node->id, '26.09.1');

        Queue::assertPushedTimes(UpdateAgentJob::class, 1);
        $job = Queue::pushed(UpdateAgentJob::class)->sole();
        $repository = Mockery::mock(DaemonConfigurationRepository::class);
        $repository->shouldReceive('setNode')->twice()->andReturnSelf();
        $repository->shouldReceive('getSystemInformation')->once()->andReturn([
            'version' => '26.09.0',
            'installation_type' => 'native',
        ]);
        $repository->shouldReceive('updateSystem')->once()->with('26.09.1')->andReturn([
            'version' => '26.09.1',
            'status' => 'restarting',
        ]);
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('getDaemon')->once()->andReturn('26.09.1');
        $versions->shouldReceive('isLatestDaemon')->once()->with('26.09.0')->andReturnFalse();

        $job->handle($repository, app(SoftwareUpdateStatusService::class), $versions);
    }

    public function test_native_agent_is_revalidated_and_updated_to_the_official_release(): void
    {
        $node = Node::factory()->for(Location::factory())->create();
        $repository = Mockery::mock(DaemonConfigurationRepository::class);
        $repository->shouldReceive('setNode')->twice()->with(Mockery::on(fn (Node $value): bool => $value->id === $node->id))->andReturnSelf();
        $repository->shouldReceive('getSystemInformation')->once()->andReturn([
            'version' => '26.09.0',
            'installation_type' => 'native',
        ]);
        $repository->shouldReceive('updateSystem')->once()->with('26.09.1')->andReturn([
            'version' => '26.09.1',
            'status' => 'restarting',
        ]);

        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('getDaemon')->once()->andReturn('26.09.1');
        $versions->shouldReceive('isLatestDaemon')->once()->with('26.09.0')->andReturnFalse();

        $statuses = app(SoftwareUpdateStatusService::class);
        (new UpdateAgentJob($node->id, '26.09.1'))->handle($repository, $statuses, $versions);

        $status = $statuses->get($statuses->agentKey($node->id));
        $this->assertSame('restarting', $status['state']);
        $this->assertSame('26.09.1', $status['version']);
    }

    public function test_docker_agent_is_never_sent_to_native_updater(): void
    {
        $node = Node::factory()->for(Location::factory())->create();
        $repository = Mockery::mock(DaemonConfigurationRepository::class);
        $repository->shouldReceive('setNode')->once()->with(Mockery::on(fn (Node $value): bool => $value->id === $node->id))->andReturnSelf();
        $repository->shouldReceive('getSystemInformation')->once()->andReturn([
            'version' => '26.09.0',
            'installation_type' => 'docker',
        ]);
        $repository->shouldNotReceive('updateSystem');

        $versions = Mockery::mock(SoftwareVersionService::class);
        $statuses = app(SoftwareUpdateStatusService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unavailable for this Agent installation');
        (new UpdateAgentJob($node->id, '26.09.1'))->handle($repository, $statuses, $versions);
    }
}
