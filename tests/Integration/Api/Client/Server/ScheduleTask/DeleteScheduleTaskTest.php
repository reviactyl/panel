<?php

namespace Tests\Integration\Api\Client\Server\ScheduleTask;

use App\Jobs\Schedule\RunTaskJob;
use App\Models\Permission;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Agent\DaemonPowerRepository;
use GuzzleHttp\Psr7\Response as AgentResponse;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Http\Response;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class DeleteScheduleTaskTest extends ClientApiIntegrationTestCase
{
    public function test_deleting_a_running_task_does_not_release_the_schedule_early()
    {
        [$user, $server] = $this->generateTestAccount();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'is_active' => true, 'is_processing' => true]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'action' => Task::ACTION_POWER, 'payload' => 'start', 'is_queued' => true]);
        $this->mock(DaemonPowerRepository::class, function ($mock) use ($user, $schedule, $task) {
            $mock->expects('setServer')->andReturnSelf();
            $mock->expects('send')->with('start')->andReturnUsing(function () use ($user, $schedule, $task) {
                $this->actingAs($user)->deleteJson($this->link($task))->assertNoContent();
                $this->assertTrue($schedule->refresh()->is_processing);

                return new AgentResponse();
            });
        });

        $this->app->call([new RunTaskJob($task), 'handle']);

        $this->assertFalse($schedule->refresh()->is_processing);
    }

    public function test_deleting_a_legacy_queued_task_releases_the_schedule()
    {
        [$user, $server] = $this->generateTestAccount();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'is_processing' => true, 'processing_token' => '00000000-0000-4000-8000-000000000003']);
        Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1, 'is_queued' => false]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 2, 'time_offset' => 120, 'is_queued' => true]);
        $values = serialize(['task' => new ModelIdentifier(Task::class, $task->id, [], $task->getConnectionName()), 'manualRun' => false]);
        $payload = 'O:'.strlen(RunTaskJob::class).':"'.RunTaskJob::class.'":'.substr($values, 2);

        $this->actingAs($user)->deleteJson($this->link($task))->assertNoContent();
        $this->assertFalse($schedule->refresh()->is_processing);
        $this->assertNotNull($schedule->last_run_at);

        $schedule->forceFill(['is_processing' => true, 'processing_token' => '00000000-0000-4000-8000-000000000002'])->save();
        $this->app->call([unserialize($payload), 'handle']);

        $this->assertTrue($schedule->refresh()->is_processing);
    }

    public function test_deleting_an_unqueued_task_does_not_release_the_schedule()
    {
        [$user, $server] = $this->generateTestAccount();
        $schedule = Schedule::factory()->create(['server_id' => $server->id, 'is_processing' => true]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id, 'is_queued' => false]);

        $this->actingAs($user)->deleteJson($this->link($task))->assertNoContent();

        $this->assertTrue($schedule->refresh()->is_processing);
    }

    /**
     * Test that an error is returned if the schedule does not belong to the server.
     */
    public function test_schedule_not_belonging_to_server_returns_error()
    {
        $server2 = $this->createServerModel();
        [$user] = $this->generateTestAccount();

        $schedule = Schedule::factory()->create(['server_id' => $server2->id]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id]);

        $this->actingAs($user)->deleteJson($this->link($task))->assertNotFound();
    }

    /**
     * Test that an error is returned if the task and schedule in the URL do not line up
     * with each other.
     */
    public function test_task_belonging_to_different_schedule_returns_error()
    {
        [$user, $server] = $this->generateTestAccount();

        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        $schedule2 = Schedule::factory()->create(['server_id' => $server->id]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id]);

        $this->actingAs($user)->deleteJson("/api/client/servers/$server->uuid/schedules/$schedule2->id/tasks/$task->id")->assertNotFound();
    }

    /**
     * Test that a user without the required permissions returns an error.
     */
    public function test_user_without_permission_returns_error()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SCHEDULE_CREATE]);

        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        $task = Task::factory()->create(['schedule_id' => $schedule->id]);

        $this->actingAs($user)->deleteJson($this->link($task))->assertForbidden();

        $user2 = User::factory()->create();

        $this->actingAs($user2)->deleteJson($this->link($task))->assertNotFound();
    }

    /**
     * Test that a schedule task is deleted and items with a higher sequence ID are decremented
     * properly in the database.
     */
    public function test_schedule_task_is_deleted_and_subsequent_tasks_are_updated()
    {
        [$user, $server] = $this->generateTestAccount();

        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        $tasks = [
            Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 1]),
            Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 2]),
            Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 3]),
            Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 4]),
        ];

        $response = $this->actingAs($user)->deleteJson($this->link($tasks[1]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('tasks', ['id' => $tasks[0]->id, 'sequence_id' => 1]);
        $this->assertDatabaseHas('tasks', ['id' => $tasks[2]->id, 'sequence_id' => 2]);
        $this->assertDatabaseHas('tasks', ['id' => $tasks[3]->id, 'sequence_id' => 3]);
        $this->assertDatabaseMissing('tasks', ['id' => $tasks[1]->id]);
    }
}
