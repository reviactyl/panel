<?php

namespace Tests\Integration\Services\Schedules;

use App\Exceptions\DisplayException;
use App\Jobs\Schedule\RunTaskJob;
use App\Models\Schedule;
use App\Models\Task;
use App\Repositories\Agent\DaemonCommandRepository;
use App\Repositories\Agent\DaemonServerRepository;
use App\Services\Schedules\ProcessScheduleService;
use Carbon\CarbonImmutable;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\IntegrationTestCase;

class ProcessScheduleServiceTest extends IntegrationTestCase
{
    /**
     * Test that a schedule with no tasks registered returns an error.
     */
    public function test_schedule_with_no_tasks_returns_exception()
    {
        $server = $this->createServerModel();
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);

        $this->expectException(DisplayException::class);
        $this->expectExceptionMessage('Cannot process schedule for task execution: no tasks are registered.');

        $this->getService()->handle($schedule);
    }

    /**
     * Test that an error during the schedule update is not persisted to the database.
     */
    public function test_error_during_schedule_data_update_does_not_persist_changes()
    {
        $server = $this->createServerModel();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'cron_minute' => 'hodor', // this will break the getNextRunDate() function.
        ]);

        /** @var Task $task */
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1]);

        $this->expectException(\InvalidArgumentException::class);

        $this->getService()->handle($schedule);

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id, 'is_processing' => true]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id, 'is_queued' => true]);
    }

    public function test_failed_dispatch_leaves_schedule_due_for_retry()
    {
        $this->swap(Dispatcher::class, $dispatcher = \Mockery::mock(Dispatcher::class));

        $server = $this->createServerModel();
        $dueAt = CarbonImmutable::now()->subMinute();
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'next_run_at' => $dueAt,
            'last_run_at' => null,
        ]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1]);
        $failure = new \RuntimeException('Queue unavailable');
        $dispatcher->expects('dispatch')->andThrow($failure);

        try {
            $this->getService()->handle($schedule);
            $this->fail('Dispatch should have failed.');
        } catch (\RuntimeException $exception) {
            $this->assertSame($failure, $exception);
        }

        $schedule->refresh();
        $this->assertFalse($schedule->is_processing);
        $this->assertFalse($task->refresh()->is_queued);
        $this->assertTrue($schedule->next_run_at->equalTo($dueAt));
        $this->assertNull($schedule->last_run_at);

        $dispatcher->expects('dispatch')->with(\Mockery::on(fn (RunTaskJob $job) => $job->task->id === $task->id && $job->afterCommit === false));
        $this->assertTrue($this->getService()->handle($schedule));
        $this->assertTrue($schedule->refresh()->is_processing);
        $this->assertTrue($task->refresh()->is_queued);
        $this->assertTrue($schedule->next_run_at->isFuture());
    }

    #[DataProvider('dispatchNowDataProvider')]
    public function test_completed_task_is_not_left_processing(bool $now)
    {
        config(['queue.default' => 'sync']);
        $server = $this->createServerModel();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'last_run_at' => null]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1, 'time_offset' => 0]);
        $this->mock(DaemonCommandRepository::class, function ($mock) use ($task) {
            $mock->expects('setServer')->andReturnSelf();
            $mock->expects('send')->with($task->payload)->andReturn(new Response());
        });

        $this->assertTrue($this->getService()->handle($schedule, $now));
        $this->assertFalse($schedule->refresh()->is_processing);
        $this->assertFalse($task->refresh()->is_queued);
        $this->assertNotNull($schedule->last_run_at);
    }

    #[DataProvider('unavailableServerDataProvider')]
    public function test_unavailable_server_schedule_is_skipped_without_dispatching(string $state)
    {
        Bus::fake();
        $server = $this->createServerModel();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'only_when_online' => true]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1]);
        $this->mock(DaemonServerRepository::class, function ($mock) use ($state) {
            $mock->expects('setServer')->andReturnSelf();
            $mock->expects('getDetails')->andReturnUsing(function () use ($state) {
                $this->assertSame(0, $this->app->make(ConnectionInterface::class)->transactionLevel());
                if ($state === 'exception') {
                    throw new \RuntimeException('Agent unavailable');
                }

                return ['state' => $state];
            });
        });

        $this->assertFalse($this->getService()->handle($schedule));
        Bus::assertNothingDispatched();
        $this->assertFalse($schedule->refresh()->is_processing);
        $this->assertFalse($task->refresh()->is_queued);
        $this->assertTrue($schedule->next_run_at->isFuture());
    }

    /**
     * Test that a job is dispatched as expected using the initial delay.
     */
    #[DataProvider('dispatchNowDataProvider')]
    public function test_job_can_be_dispatched_with_expected_initial_delay(bool $now)
    {
        Bus::fake();

        $server = $this->createServerModel();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);

        /** @var Task $task */
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'time_offset' => 10, 'sequence_id' => 1]);

        $this->getService()->handle($schedule, $now);

        Bus::assertDispatched(RunTaskJob::class, function ($job) use ($now, $task) {
            $this->assertInstanceOf(RunTaskJob::class, $job);
            $this->assertSame($task->id, $job->task->id);
            // Jobs using dispatchNow should not have a delay associated with them.
            $this->assertSame($now ? null : 10, $job->delay);

            return true;
        });

        $this->assertDatabaseHas('schedules', ['id' => $schedule->id, 'is_processing' => true]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'is_queued' => true]);
    }

    /**
     * Test that even if a schedule's task sequence gets messed up the first task based on
     * the ascending order of tasks is used.
     *
     * @see https://github.com/pterodactyl/panel/issues/2534
     */
    public function test_first_sequence_task_is_found()
    {
        Bus::fake();

        $server = $this->createServerModel();
        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);

        /** @var Task $task */
        $task2 = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 4]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 2]);
        $task3 = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 3]);

        $this->getService()->handle($schedule);

        Bus::assertDispatched(RunTaskJob::class, function (RunTaskJob $job) use ($task) {
            return $task->id === $job->task->id;
        });

        $this->assertDatabaseHas('schedules', ['id' => $schedule->id, 'is_processing' => true]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'is_queued' => true]);
        $this->assertDatabaseHas('tasks', ['id' => $task2->id, 'is_queued' => false]);
        $this->assertDatabaseHas('tasks', ['id' => $task3->id, 'is_queued' => false]);
    }

    /**
     * Tests that a task's processing state is reset correctly if using "dispatchNow" and there is
     * an exception encountered while running it.
     *
     * @see https://github.com/pterodactyl/panel/issues/2550
     */
    public function test_task_dispatched_now_is_reset_properly_if_error_is_encountered()
    {
        $this->swap(Dispatcher::class, $dispatcher = \Mockery::mock(Dispatcher::class));

        $server = $this->createServerModel();
        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'last_run_at' => null]);
        /** @var Task $task */
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1]);

        $dispatcher->expects('dispatchNow')->andThrows(new Exception('Test thrown exception'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Test thrown exception');

        $this->getService()->handle($schedule, true);

        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'is_processing' => false,
            'last_run_at' => CarbonImmutable::now()->toAtomString(),
        ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'is_queued' => false]);
    }

    /**
     * Test that a timezone change made after the schedule was loaded is used for the next run.
     */
    public function test_next_run_uses_current_server_timezone()
    {
        Bus::fake();

        $server = $this->createServerModel();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'cron_minute' => '0',
            'cron_hour' => '3',
        ]);
        Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1]);

        $schedule->load('server');
        $server->newQuery()->whereKey($server->id)->update(['timezone' => 'Asia/Tokyo']);

        $this->getService()->handle($schedule);

        $this->assertSame('03:00', $schedule->refresh()->next_run_at->setTimezone('Asia/Tokyo')->format('H:i'));
    }

    public static function unavailableServerDataProvider(): array
    {
        return [['offline'], ['stopping'], ['exception']];
    }

    public static function dispatchNowDataProvider(): array
    {
        return [[true], [false]];
    }

    private function getService(): ProcessScheduleService
    {
        return $this->app->make(ProcessScheduleService::class);
    }
}
