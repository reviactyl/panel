<?php

namespace Tests\Integration\Filament\Widgets;

use App\Models\User;
use App\Services\Helpers\DashboardStatisticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Integration\IntegrationTestCase;

class DashboardStatisticsTest extends IntegrationTestCase
{
    public function test_daily_counts_boundaries_cache_and_expiry(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(12));
        Cache::flush();
        $start = now()->subDays(6)->startOfDay();
        User::factory()->create(['created_at' => $start->copy()->subSecond()]);
        User::factory()->count(2)->create(['created_at' => $start]);
        User::factory()->count(3)->create(['created_at' => now()->startOfDay()]);
        User::factory()->create(['created_at' => now()->addDay()->startOfDay()]);
        $service = new DashboardStatisticsService();

        DB::enableQueryLog();
        DB::flushQueryLog();
        $counts = $service->counts(User::class);
        $this->assertSame(['total' => 7, 'today' => 3, 'daily' => [2, 0, 0, 0, 0, 0, 3]], $counts);
        $this->assertCount(2, DB::getQueryLog());
        DB::flushQueryLog();
        $this->assertSame($counts, $service->counts(User::class));
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();

        User::factory()->create();
        $this->assertSame($counts, $service->counts(User::class));
        $this->travel(16)->seconds();
        $this->assertSame(4, $service->counts(User::class)['today']);
        $this->assertSame(8, $service->counts(User::class)['total']);
        $this->travelTo(now()->addDay()->startOfDay());
        $this->assertSame(1, $service->counts(User::class)['today']);
    }
}
