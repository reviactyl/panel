<?php

namespace Tests\Integration\Admin;

use App\Http\ViewComposers\DesignifyComposer;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Integration\IntegrationTestCase;

class DashboardPerformanceTest extends IntegrationTestCase
{
    public function test_local_widgets_render_in_the_first_response_without_loading_unused_activity_data(): void
    {
        $admin = User::factory()->create(['root_admin' => true, 'name_first' => 'Dashboard', 'name_last' => 'Admin']);
        $this->actingAs($admin);
        foreach (range(1, 6) as $number) {
            $log = new ActivityLog();
            $log->event = 'dashboard:'.$number;
            $log->timestamp = now();
            $log->ip = '127.0.0.1';
            $log->properties = collect(['large_unused_payload' => str_repeat('x', 10000)]);
            $log->actor()->associate($admin);
            $log->save();
        }
        $resolved = 0;
        $this->app->resolving(DesignifyComposer::class, function () use (&$resolved): void {
            $resolved++;
        });
        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->get('/admin')->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Any Feedback?')
            ->assertSee('Active User Locations')
            ->assertSee('Dashboard Admin')
            ->assertSee('dashboard:6')
            ->assertDontSee('dashboard:1');

        $queries = collect(DB::getQueryLog())->pluck('query');
        DB::disableQueryLog();
        $this->assertSame(1, $resolved);
        $this->assertFalse($queries->contains(fn (string $query): bool => str_contains($query, 'activity_log_subjects')));
        $activity = $queries->first(fn (string $query): bool => str_contains($query, 'from "activity_logs"') || str_contains($query, 'from `activity_logs`'));
        $this->assertNotNull($activity);
        $this->assertStringNotContainsString('select *', $activity);
        $this->assertStringNotContainsString('properties', $activity);
    }

    public function test_theme_composer_is_rebuilt_for_a_new_request_scope(): void
    {
        config()->set('designify.site_title', 'First request');
        $first = app(DesignifyComposer::class);
        $this->assertSame($first, app(DesignifyComposer::class));
        $this->app->forgetScopedInstances();
        config()->set('designify.site_title', 'Next request');
        $second = app(DesignifyComposer::class);
        $this->assertNotSame($first, $second);
        $view = view('templates.auth.core');
        $second->compose($view);
        $this->assertSame('Next request', $view->getData()['panelConfiguration']['site_title']);
    }
}
