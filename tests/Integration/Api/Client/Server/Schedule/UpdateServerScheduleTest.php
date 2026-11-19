<?php

namespace Tests\Integration\Api\Client\Server\Schedule;

use App\Helpers\Utilities;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Schedule;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class UpdateServerScheduleTest extends ClientApiIntegrationTestCase
{
    /**
     * The data to use when updating a schedule.
     */
    private array $updateData = [
        'name' => 'Updated Schedule Name',
        'minute' => '5',
        'hour' => '*',
        'day_of_week' => '*',
        'month' => '*',
        'day_of_month' => '*',
        'is_active' => false,
    ];

    /**
     * Test that a schedule can be updated.
     */
    #[DataProvider('permissionsDataProvider')]
    public function test_schedule_can_be_updated(array $permissions, bool $active)
    {
        [$user, $server] = $this->generateTestAccount($permissions);

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'name' => 'Original Schedule Name',
            'is_active' => ! $active,
        ]);
        $expected = Utilities::getScheduleNextRunDate('5', '*', '*', '*', '*');

        $response = $this->actingAs($user)
            ->postJson("/api/client/servers/{$server->uuid}/schedules/{$schedule->id}", array_replace($this->updateData, ['is_active' => $active]));

        $schedule = $schedule->refresh();

        $response->assertOk();
        $this->assertSame('Updated Schedule Name', $schedule->name);
        $this->assertSame($active, $schedule->is_active);
        $this->assertJsonTransformedWith($response->json('attributes'), $schedule);

        $this->assertActivityFor('server:schedule.update', $user, [$schedule, $server]);
        $activity = ActivityLog::query()->where('event', 'server:schedule.update')->sole();
        $this->assertSame('Updated Schedule Name', $activity->properties->get('name'));
        $this->assertSame($active, $activity->properties->get('active'));

        $this->assertSame($expected->toAtomString(), $schedule->next_run_at->toAtomString());
    }

    /**
     * Test that an error is returned if the schedule exists but does not belong to this
     * specific server instance.
     */
    public function test_error_is_returned_if_schedule_does_not_belong_to_server()
    {
        [$user, $server] = $this->generateTestAccount();
        $server2 = $this->createServerModel(['owner_id' => $user->id]);

        $schedule = Schedule::factory()->create(['server_id' => $server2->id]);

        $this->actingAs($user)
            ->postJson("/api/client/servers/{$server->uuid}/schedules/{$schedule->id}")
            ->assertNotFound();
    }

    /**
     * Test that an error is returned if the subuser does not have permission to modify a
     * server schedule.
     */
    public function test_error_is_returned_if_subuser_does_not_have_permission_to_modify_schedule()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SCHEDULE_CREATE]);

        $schedule = Schedule::factory()->create(['server_id' => $server->id]);

        $this->actingAs($user)
            ->postJson("/api/client/servers/{$server->uuid}/schedules/{$schedule->id}")
            ->assertForbidden();
    }

    /**
     * Test that the "is_processing" field gets reset to false when the schedule is enabled
     * or disabled so that an invalid state can be more easily fixed.
     *
     * @see https://github.com/pterodactyl/panel/issues/2425
     */
    public function test_schedule_is_processing_is_set_to_false_when_active_state_changes()
    {
        [$user, $server] = $this->generateTestAccount();

        /** @var Schedule $schedule */
        $schedule = Schedule::factory()->create([
            'server_id' => $server->id,
            'is_active' => true,
            'is_processing' => true,
        ]);

        $this->assertTrue($schedule->is_active);
        $this->assertTrue($schedule->is_processing);

        $response = $this->actingAs($user)
            ->postJson("/api/client/servers/{$server->uuid}/schedules/{$schedule->id}", $this->updateData);

        $schedule = $schedule->refresh();

        $response->assertOk();
        $this->assertFalse($schedule->is_active);
        $this->assertFalse($schedule->is_processing);
    }

    public static function permissionsDataProvider(): array
    {
        return [
            [[], false],
            [[], true],
            [[Permission::ACTION_SCHEDULE_UPDATE], false],
            [[Permission::ACTION_SCHEDULE_UPDATE], true],
        ];
    }
}
