<?php

namespace Tests\Integration\Api\Client\Server\Schedule;

use App\Jobs\Schedule\RunTaskJob;
use App\Models\Permission;
use App\Models\Schedule;
use App\Models\Task;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class ExecuteScheduleTest extends ClientApiIntegrationTestCase
{
    /**
     * Test that a schedule can be executed and is updated in the database correctly.
     */
    #[DataProvider('permissionsDataProvider')]
    public function test_schedule_is_executed_right_away(array $permissions)
    {
        [$user, $server] = $this->generateTestAccount($permissions);

        Bus::fake();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
        ]);

        $response = $this->actingAs($user)->postJson($this->link($schedule, '/execute'));
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonPath('errors.0.code', 'DisplayException');
        $response->assertJsonPath('errors.0.detail', 'Cannot process schedule for task execution: no tasks are registered.');

        /** @var Task $task */
        $task = Task::factory()->create([
            'schedule_id' => $schedule->id,
            'sequence_id' => 1,
            'time_offset' => 2,
        ]);

        $this->actingAs($user)->postJson($this->link($schedule, '/execute'))->assertStatus(Response::HTTP_ACCEPTED);

        Bus::assertDispatched(function (RunTaskJob $job) use ($task) {
            // A task executed right now should not have any job delay associated with it.
            $this->assertNull($job->delay);
            $this->assertSame($task->id, $job->task->id);

            return true;
        });
    }

    /**
     * Test that a user without the schedule update permission cannot execute it.
     */
    public function test_user_without_schedule_update_permission_cannot_execute()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SCHEDULE_CREATE]);

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);

        $this->actingAs($user)->postJson($this->link($schedule, '/execute'))->assertForbidden();
    }

    /**
     * Test that a subuser can execute a schedule containing tasks they could not create.
     */
    #[DataProvider('taskActionDataProvider')]
    public function test_subuser_cannot_execute_schedule_without_task_action_permission(string $action, string $payload)
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SCHEDULE_UPDATE]);

        Bus::fake();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);

        /** @var Task $task */
        $task = Task::factory()->create([
            'schedule_id' => $schedule->id,
            'sequence_id' => 1,
            'action' => $action,
            'payload' => $payload,
        ]);
        $this->actingAs($user)->postJson($this->link($schedule, '/execute'))->assertStatus(Response::HTTP_ACCEPTED);

        Bus::assertDispatched(fn (RunTaskJob $job) => $job->task->id === $task->id);
    }

    /**
     * Test that a task payload predating the current validation rules does not lock the owner
     * out of their own schedule.
     */
    public function test_owner_can_execute_schedule_with_unmappable_task_payload()
    {
        [$user, $server] = $this->generateTestAccount();

        Bus::fake();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);

        /** @var Task $task */
        $task = Task::factory()->create([
            'schedule_id' => $schedule->id,
            'sequence_id' => 1,
            'action' => 'power',
            'payload' => 'reboot',
        ]);

        $this->actingAs($user)->postJson($this->link($schedule, '/execute'))->assertStatus(Response::HTTP_ACCEPTED);

        Bus::assertDispatched(fn (RunTaskJob $job) => $job->task->id === $task->id);
    }

    public static function permissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SCHEDULE_UPDATE]]];
    }

    public static function taskActionDataProvider(): array
    {
        return [
            ['command', 'say Test'],
            ['power', 'start'],
            ['power', 'stop'],
            ['power', 'restart'],
            ['power', 'kill'],
            ['backup', ''],
        ];
    }
}
