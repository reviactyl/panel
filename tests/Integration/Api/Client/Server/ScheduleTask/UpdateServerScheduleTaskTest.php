<?php

namespace Tests\Integration\Api\Client\Server\ScheduleTask;

use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Schedule;
use App\Models\Task;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class UpdateServerScheduleTaskTest extends ClientApiIntegrationTestCase
{
    public function test_task_can_be_updated()
    {
        [$user, $server] = $this->generateTestAccount([
            Permission::ACTION_SCHEDULE_UPDATE,
            Permission::ACTION_CONTROL_CONSOLE,
        ]);

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        /** @var Task $task */
        $task = Task::factory()->create([
            'schedule_id' => $schedule->id,
            'action' => 'power',
            'payload' => 'start',
        ]);

        $response = $this->actingAs($user)->postJson($this->link($task), [
            'action' => 'command',
            'payload' => 'say Test',
            'time_offset' => 10,
        ]);

        $response->assertOk();
        $task->refresh();

        $this->assertSame('command', $task->action);
        $this->assertSame('say Test', $task->payload);
        $this->assertSame(10, $task->time_offset);

        $activity = ActivityLog::query()->where('event', 'server:task.update')->sole();
        $this->assertSame('command', $activity->properties['action']);
        $this->assertSame('say Test', $activity->properties['payload']);
    }

    public function test_task_sequence_is_clamped_to_the_schedule_task_count(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        $task = Task::factory()->create([
            'schedule_id' => $schedule->id,
            'sequence_id' => 1,
            'action' => 'power',
            'payload' => 'start',
        ]);
        $other = Task::factory()->create(['schedule_id' => $schedule->id, 'sequence_id' => 2]);
        $transactionQueries = [];
        DB::listen(function (QueryExecuted $query) use (&$transactionQueries) {
            if ($query->connection->transactionLevel() > 0) {
                $transactionQueries[] = strtolower($query->sql);
            }
        });

        $this->actingAs($user)->postJson($this->link($task), [
            'sequence_id' => 99,
            'action' => 'command',
            'payload' => 'say Updated',
            'time_offset' => 10,
            'continue_on_failure' => true,
        ])->assertOk()->assertJsonPath('attributes.sequence_id', 2)
            ->assertJsonPath('attributes.action', 'command')
            ->assertJsonPath('attributes.payload', 'say Updated');

        $this->assertStringStartsWith('select', $transactionQueries[0]);
        $this->assertStringContainsString('schedules', $transactionQueries[0]);
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb', 'pgsql'])) {
            $this->assertStringContainsString('for update', $transactionQueries[0]);
        }

        $this->assertSame([$other->id, $task->id], $schedule->tasks()->orderBy('sequence_id')->pluck('id')->all());
        $this->assertSame([1, 2], $schedule->tasks()->orderBy('sequence_id')->pluck('sequence_id')->all());
        $this->assertSame(10, $task->refresh()->time_offset);
        $this->assertTrue($task->continue_on_failure);

        $activity = ActivityLog::query()->where('event', 'server:task.update')->sole();
        $this->assertSame('command', $activity->properties['action']);
        $this->assertSame('say Updated', $activity->properties['payload']);
        $this->assertActivityFor('server:task.update', $user, $schedule, $task);
    }

    public function test_task_can_be_moved_to_the_first_position(): void
    {
        [$user, $server] = $this->generateTestAccount();
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        $tasks = collect([1, 2, 3])->map(fn ($sequence) => Task::factory()->create([
            'schedule_id' => $schedule->id, 'sequence_id' => $sequence,
        ]));

        $this->actingAs($user)->postJson($this->link($tasks[2]), [
            'sequence_id' => 1,
            'action' => 'command',
            'payload' => 'say First',
            'time_offset' => 0,
        ])->assertOk()->assertJsonPath('attributes.sequence_id', 1);

        $this->assertSame([$tasks[2]->id, $tasks[0]->id, $tasks[1]->id], $schedule->tasks()->orderBy('sequence_id')->pluck('id')->all());
        $this->assertSame([1, 2, 3], $schedule->tasks()->orderBy('sequence_id')->pluck('sequence_id')->all());
    }

    #[DataProvider('legacySequenceProvider')]
    public function test_legacy_sequence_gaps_are_repaired_without_unintended_moves(?int $sequenceId, array $expectedOrder): void
    {
        [$user, $server] = $this->generateTestAccount();
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        $tasks = collect([1, 2, 99, 100])->map(fn ($sequence) => Task::factory()->create([
            'schedule_id' => $schedule->id, 'sequence_id' => $sequence,
        ]));
        $data = ['action' => 'command', 'payload' => 'say Updated', 'time_offset' => 0];
        if ($sequenceId !== null) {
            $data['sequence_id'] = $sequenceId;
        }

        $this->actingAs($user)->postJson($this->link($tasks[3]), $data)->assertOk();

        $ordered = $schedule->tasks()->orderBy('sequence_id')->get();
        $this->assertSame([1, 2, 3, 4], $ordered->pluck('sequence_id')->all());
        $this->assertSame(array_map(fn ($index) => $tasks[$index]->id, $expectedOrder), $ordered->pluck('id')->all());
    }

    public static function legacySequenceProvider(): array
    {
        return [
            'details only' => [null, [0, 1, 2, 3]],
            'past the end' => [99, [0, 1, 2, 3]],
            'explicit move' => [2, [0, 3, 1, 2]],
        ];
    }

    public function test_task_cannot_be_updated_without_action_permission()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SCHEDULE_UPDATE]);

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        /** @var Task $task */
        $task = Task::factory()->create([
            'schedule_id' => $schedule->id,
            'action' => 'power',
            'payload' => 'start',
        ]);

        $this->actingAs($user)->postJson($this->link($task), [
            'action' => 'command',
            'payload' => 'say Test',
            'time_offset' => 10,
        ])->assertForbidden();

        $task->refresh();

        $this->assertSame('power', $task->action);
        $this->assertSame('start', $task->payload);
    }

    public function test_power_task_requires_valid_payload()
    {
        [$user, $server] = $this->generateTestAccount([
            Permission::ACTION_SCHEDULE_UPDATE,
            Permission::ACTION_CONTROL_START,
        ]);

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create(['server_id' => $server->id]);
        /** @var Task $task */
        $task = Task::factory()->create(['schedule_id' => $schedule->id]);

        $this->actingAs($user)->postJson($this->link($task), [
            'action' => 'power',
            'payload' => 'invalid',
            'time_offset' => 0,
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.meta.rule', 'in')
            ->assertJsonPath('errors.0.meta.source_field', 'payload');
    }
}
