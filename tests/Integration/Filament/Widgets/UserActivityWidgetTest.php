<?php

namespace Tests\Integration\Filament\Widgets;

use App\Filament\Widgets\UserActivityWidget;
use App\Jobs\Administration\RefreshUserActivityLocationsJob;
use App\Models\User;
use App\Services\Helpers\GeoIPService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\Integration\IntegrationTestCase;

class UserActivityWidgetTest extends IntegrationTestCase
{
    public function test_country_percentages_total_one_hundred(): void
    {
        Cache::put(RefreshUserActivityLocationsJob::CACHE_KEY, [
            ['country' => 'United States', 'code' => 'US', 'count' => 4],
            ['country' => 'Canada', 'code' => 'CA', 'count' => 1],
            ['country' => 'United Kingdom', 'code' => 'GB', 'count' => 1],
        ]);

        $data = app(TestableUserActivityWidget::class)->getChartData();
        $percentages = $data['datasets'][0]['data'];

        $this->assertSame('Active users', $data['datasets'][0]['label']);
        $this->assertSame(['United States', 'Canada', 'United Kingdom'], $data['labels']);
        $this->assertSame([66, 17, 17], $percentages);
        $this->assertSame(100, array_sum($percentages));
    }

    public function test_countries_outside_the_top_three_are_included_in_the_total(): void
    {
        Cache::put(RefreshUserActivityLocationsJob::CACHE_KEY, [
            ['country' => 'United States', 'code' => 'US', 'count' => 4],
            ['country' => 'Canada', 'code' => 'CA', 'count' => 1],
            ['country' => 'United Kingdom', 'code' => 'GB', 'count' => 1],
            ['country' => 'Australia', 'code' => 'AU', 'count' => 1],
        ]);

        $data = app(TestableUserActivityWidget::class)->getChartData();
        $percentages = $data['datasets'][0]['data'];

        $this->assertSame(['United States', 'Canada', 'United Kingdom', 'Other'], $data['labels']);
        $this->assertSame([58, 14, 14, 14], $percentages);
        $this->assertSame(100, array_sum($percentages));
    }

    public function test_each_active_user_is_counted_once_using_their_latest_activity_location(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $inactiveUser = User::factory()->create();

        $this->createUserActivity($firstUser, 'server:file.write', '192.0.2.2', now()->subDay());
        $this->createUserActivity($firstUser, 'server:power.start', '192.0.2.1', now()->subDays(10));
        $this->createUserActivity($secondUser, 'api-key:create', '192.0.2.3', now()->subDays(2));
        $this->createUserActivity($inactiveUser, 'server:power.stop', '192.0.2.4', now()->subDays(31));

        $this->mock(GeoIPService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getCountryInfo')->with('192.0.2.1')->never();
            $mock->shouldReceive('getCountryInfo')->with('192.0.2.2')->once()->andReturn([
                'country' => 'Canada',
                'code' => 'CA',
            ]);
            $mock->shouldReceive('getCountryInfo')->with('192.0.2.3')->once()->andReturnNull();
            $mock->shouldReceive('getCountryInfo')->with('192.0.2.4')->never();
        });
        Cache::forget(RefreshUserActivityLocationsJob::CACHE_KEY);
        app(RefreshUserActivityLocationsJob::class)->handle(app(GeoIPService::class));

        $data = app(TestableUserActivityWidget::class)->getChartData();

        $this->assertSame(['Canada', 'Unknown'], $data['labels']);
        $this->assertSame([50, 50], $data['datasets'][0]['data']);
    }

    public function test_geoip_lookups_are_bounded_while_unresolved_users_remain_in_the_distribution(): void
    {
        $users = User::factory()->count(201)->create();

        foreach ($users as $index => $user) {
            $this->createUserActivity($user, 'auth:success', '8.8.8.'.($index + 1), now()->subDay());
        }

        $this->mock(GeoIPService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getCountryInfo')->times(200)->andReturn([
                'country' => 'United States',
                'code' => 'US',
            ]);
        });
        Cache::forget(RefreshUserActivityLocationsJob::CACHE_KEY);
        app(RefreshUserActivityLocationsJob::class)->handle(app(GeoIPService::class));

        $data = app(TestableUserActivityWidget::class)->getChartData();

        $this->assertSame(['United States', 'Unknown'], $data['labels']);
        $this->assertSame([100, 0], $data['datasets'][0]['data']);
    }

    private function createUserActivity(User $user, string $event, string $ip, CarbonInterface $timestamp): void
    {
        DB::table('activity_logs')->insert([
            'event' => $event,
            'ip' => $ip,
            'actor_type' => $user->getMorphClass(),
            'actor_id' => $user->id,
            'properties' => '[]',
            'timestamp' => $timestamp,
        ]);
    }
}

class TestableUserActivityWidget extends UserActivityWidget
{
    public function getChartData(): array
    {
        return $this->getData();
    }
}
