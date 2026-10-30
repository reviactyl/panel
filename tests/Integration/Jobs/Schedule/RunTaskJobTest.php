<?php

namespace Tests\Integration\Jobs\Schedule;

use App\Jobs\Schedule\RunTaskJob;
use App\Models\Schedule;
use App\Models\Server;
use App\Models\Task;
use App\Repositories\Agent\DaemonPowerRepository;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class RunTaskJobTest extends IntegrationTestCase
{
    /**
     * An inactive job should not be run by the system.
     */
    public function test_inactive_job_is_not_run()
    {
        $server = $this->createServerModel();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'is_processing' => true,
            'last_run_at' => null,
            'is_active' => false,
        ]);
        /** @var Task $task */
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'is_queued' => true]);

        $job = new RunTaskJob($task);

        Bus::dispatchSync($job);

        $task->refresh();
        $schedule->refresh();

        $this->assertFalse($task->is_queued);
        $this->assertFalse($schedule->is_processing);
        $this->assertFalse($schedule->is_active);
        $this->assertTrue(CarbonImmutable::now()->isSameAs(\DateTimeInterface::ATOM, $schedule->last_run_at));
    }

    public function test_job_with_invalid_action_throws_exception()
    {
        $server = $this->createServerModel();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        /** @var Task $task */
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'action' => 'foobar']);

        $job = new RunTaskJob($task);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid task action provided: foobar');
        Bus::dispatchSync($job);
    }

    #[DataProvider('isManualRunDataProvider')]
    public function test_later_task_keeps_its_offset(bool $isManualRun)
    {
        Bus::fake();
        $server = $this->createServerModel();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'is_active' => true, 'is_processing' => true]);
        $first = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1, 'action' => Task::ACTION_POWER, 'payload' => 'start', 'is_queued' => true]);
        $second = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 2, 'time_offset' => 120]);
        $this->mock(DaemonPowerRepository::class, function ($mock) {
            $mock->expects('setServer')->andReturnSelf();
            $mock->expects('send')->with('start')->andReturn(new Response());
        });

        $this->app->call([new RunTaskJob($first, $isManualRun), 'handle']);

        Bus::assertDispatched(RunTaskJob::class, function (RunTaskJob $job) use ($second, $isManualRun) {
            return $job->taskId === $second->id && $job->delay === 120 && $job->manualRun === $isManualRun;
        });
        $this->assertFalse($first->refresh()->is_queued);
        $this->assertTrue($second->refresh()->is_queued);
        $this->assertTrue($schedule->refresh()->is_processing);
    }

    #[DataProvider('isManualRunDataProvider')]
    public function test_job_is_executed(bool $isManualRun)
    {
        $server = $this->createServerModel();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'is_active' => ! $isManualRun,
            'is_processing' => true,
            'last_run_at' => null,
        ]);
        /** @var Task $task */
        $task = Task::factory()->create([
            'schedule_id' => $schedule->id,
            'action' => Task::ACTION_POWER,
            'payload' => 'start',
            'is_queued' => true,
            'continue_on_failure' => false,
        ]);

        $mock = \Mockery::mock(DaemonPowerRepository::class);
        $this->instance(DaemonPowerRepository::class, $mock);

        $mock->expects('setServer')->with(\Mockery::on(function ($value) use ($server) {
            return $value instanceof Server && $value->id === $server->id;
        }))->andReturnSelf();
        $mock->expects('send')->with('start')->andReturn(new Response());

        Bus::dispatchSync(new RunTaskJob($task, $isManualRun));

        $task->refresh();
        $schedule->refresh();

        $this->assertFalse($task->is_queued);
        $this->assertFalse($schedule->is_processing);
        $this->assertTrue(CarbonImmutable::now()->isSameAs(\DateTimeInterface::ATOM, $schedule->last_run_at));
    }

    #[DataProvider('isManualRunDataProvider')]
    public function test_exception_during_run_is_handled_correctly(bool $continueOnFailure)
    {
        $server = $this->createServerModel();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        /** @var Task $task */
        $task = Task::factory()->create([
            'schedule_id' => $schedule->id,
            'action' => Task::ACTION_POWER,
            'payload' => 'start',
            'continue_on_failure' => $continueOnFailure,
        ]);

        $mock = \Mockery::mock(DaemonPowerRepository::class);
        $this->instance(DaemonPowerRepository::class, $mock);

        $mock->expects('setServer->send')->andThrow(new \RuntimeException('Task failed.'));

        if (! $continueOnFailure) {
            $this->expectException(\RuntimeException::class);
        }

        Bus::dispatchSync(new RunTaskJob($task));

        if ($continueOnFailure) {
            $task->refresh();
            $schedule->refresh();

            $this->assertFalse($task->is_queued);
            $this->assertFalse($schedule->is_processing);
            $this->assertTrue(CarbonImmutable::now()->isSameAs(\DateTimeInterface::ATOM, $schedule->last_run_at));
        }
    }

    /**
     * Test that a schedule is not executed if the server is suspended.
     *
     * @see https://github.com/pterodactyl/panel/issues/4008
     */
    public function test_task_is_not_run_if_server_is_suspended()
    {
        $server = $this->createServerModel([
            'status' => Server::STATUS_SUSPENDED,
        ]);

        $schedule = Schedule::factory()->for($server)->create([
            'last_run_at' => Carbon::now()->subHour(),
        ]);

        $task = Task::factory()->for($schedule)->create([
            'action' => Task::ACTION_POWER,
            'payload' => 'start',
        ]);

        Bus::dispatchSync(new RunTaskJob($task));

        $task->refresh();
        $schedule->refresh();

        $this->assertFalse($task->is_queued);
        $this->assertFalse($schedule->is_processing);
        $this->assertTrue(Carbon::now()->isSameAs(\DateTimeInterface::ATOM, $schedule->last_run_at));
    }

    public function test_deleting_delayed_task_completes_the_schedule()
    {
        Bus::fake();
        $server = $this->createServerModel();
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'is_active' => true,
            'is_processing' => true,
            'processing_token' => 'original-run',
        ]);
        $first = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1, 'action' => Task::ACTION_POWER, 'payload' => 'start', 'is_queued' => true]);
        $second = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 2, 'time_offset' => 120]);
        $this->mock(DaemonPowerRepository::class, function ($mock) {
            $mock->expects('setServer')->andReturnSelf();
            $mock->expects('send')->with('start')->andReturn(new Response());
        });

        $this->app->call([new RunTaskJob($first, false, 'original-run'), 'handle']);
        $job = Bus::dispatched(RunTaskJob::class)->sole();
        $this->assertSame(120, $job->delay);
        $payload = serialize($job);
        $second->delete();

        $this->app->call([unserialize($payload), 'handle']);

        $this->assertFalse($schedule->refresh()->is_processing);
        $this->assertNotNull($schedule->last_run_at);
        $this->assertFalse($first->refresh()->is_queued);
    }

    public function test_deleted_task_does_not_complete_a_newer_schedule_run()
    {
        $server = $this->createServerModel();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'is_processing' => true, 'processing_token' => 'original-run']);
        $task = Task::factory()->create(['schedule_id' => $schedule->id]);
        $payload = serialize(new RunTaskJob($task, false, 'original-run'));
        $task->delete();
        $schedule->forceFill(['processing_token' => 'new-run'])->save();

        $this->app->call([unserialize($payload), 'handle']);

        $this->assertTrue($schedule->refresh()->is_processing);
    }

    public function test_legacy_queued_job_still_executes()
    {
        $server = $this->createServerModel();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'is_processing' => true, 'processing_token' => 'legacy-run']);
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'action' => Task::ACTION_POWER, 'payload' => 'start', 'is_queued' => true]);
        $this->mock(DaemonPowerRepository::class, function ($mock) {
            $mock->expects('setServer')->andReturnSelf();
            $mock->expects('send')->with('start')->andReturn(new Response());
        });
        $values = ['task' => new ModelIdentifier(Task::class, $task->id, [], $task->getConnectionName()), 'manualRun' => false];
        $serializedValues = serialize($values);
        $payload = 'O:'.strlen(RunTaskJob::class).':"'.RunTaskJob::class.'":'.substr($serializedValues, 2);

        $this->app->call([unserialize($payload), 'handle']);

        $this->assertFalse($schedule->refresh()->is_processing);
        $this->assertFalse($task->refresh()->is_queued);
    }

    public static function isManualRunDataProvider(): array
    {
        return [[true], [false]];
    }
}
