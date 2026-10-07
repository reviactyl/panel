<?php

namespace Tests\Integration\Api\Client\Server\Schedule;

use App\Models\Permission;
use App\Models\Schedule;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class CreateServerScheduleTest extends ClientApiIntegrationTestCase
{
    /**
     * Test that a schedule can be created for the server.
     */
    #[DataProvider('permissionsDataProvider')]
    public function test_schedule_can_be_created_for_server(array $permissions)
    {
        [$user, $server] = $this->generateTestAccount($permissions);

        $response = $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/schedules", [
            'name' => 'Test Schedule',
            'is_active' => false,
            'minute' => '0',
            'hour' => '*/2',
            'day_of_week' => '2',
            'month' => '1',
            'day_of_month' => '*',
        ]);

        $response->assertOk();

        $this->assertNotNull($id = $response->json('attributes.id'));

        /** @var Schedule $schedule */
        $schedule = Schedule::query()->findOrFail($id);
        $this->assertFalse($schedule->is_active);
        $this->assertFalse($schedule->is_processing);
        $this->assertSame('0', $schedule->cron_minute);
        $this->assertSame('*/2', $schedule->cron_hour);
        $this->assertSame('2', $schedule->cron_day_of_week);
        $this->assertSame('1', $schedule->cron_month);
        $this->assertSame('*', $schedule->cron_day_of_month);
        $this->assertSame('Test Schedule', $schedule->name);

        $this->assertJsonTransformedWith($response->json('attributes'), $schedule);
        $response->assertJsonCount(0, 'attributes.relationships.tasks.data');
    }

    public function test_schedule_uses_server_timezone()
    {
        [$user, $server] = $this->generateTestAccount();
        $server->update(['timezone' => 'Asia/Tokyo']);

        $response = $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/schedules", [
            'name' => 'Test Schedule',
            'minute' => '0',
            'hour' => '3',
            'day_of_week' => '*',
            'month' => '*',
            'day_of_month' => '*',
        ]);

        $response->assertOk();

        $schedule = Schedule::query()->findOrFail($response->json('attributes.id'));
        $this->assertSame('03:00', $schedule->next_run_at->clone()->setTimezone('Asia/Tokyo')->format('H:i'));
        $this->assertSame('03:00', $schedule->getNextRunDate()->setTimezone('Asia/Tokyo')->format('H:i'));
    }

    /**
     * Test that the validation rules for scheduling work as expected.
     */
    public function test_schedule_validation_rules()
    {
        [$user, $server] = $this->generateTestAccount();

        $response = $this->actingAs($user)->postJson("/api/client/servers/$server->uuid/schedules", []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        foreach (['name', 'minute', 'hour', 'day_of_month', 'day_of_week'] as $i => $field) {
            $response->assertJsonPath("errors.$i.code", 'ValidationException');
            $response->assertJsonPath("errors.$i.meta.rule", 'required');
            $response->assertJsonPath("errors.$i.meta.source_field", $field);
        }

        $this->actingAs($user)
            ->postJson("/api/client/servers/$server->uuid/schedules", [
                'name' => 'Testing',
                'is_active' => 'no',
                'minute' => '*',
                'hour' => '*',
                'day_of_month' => '*',
                'month' => '*',
                'day_of_week' => '*',
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.0.meta.rule', 'boolean');
    }

    /**
     * Test that a subuser without required permissions cannot create a schedule.
     */
    public function test_subuser_cannot_create_schedule_without_permissions()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_SCHEDULE_UPDATE]);

        $this->actingAs($user)
            ->postJson("/api/client/servers/$server->uuid/schedules", [])
            ->assertForbidden();
    }

    public static function permissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SCHEDULE_CREATE]]];
    }
}
