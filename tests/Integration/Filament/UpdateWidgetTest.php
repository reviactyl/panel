<?php

namespace Tests\Integration\Filament;

use App\Filament\Pages\SoftwareUpdates;
use App\Filament\Widgets\UpdateWidget;
use App\Models\Role;
use App\Models\User;
use App\Services\Helpers\SoftwareVersionService;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Mockery;
use Tests\Integration\IntegrationTestCase;

class UpdateWidgetTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['root_admin' => true]));
        config()->set('app.version', '26.09.0');
    }

    public function test_outdated_widget_keeps_its_version_and_update_link_after_hydration(): void
    {
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('isLatestPanel')->withNoArgs()->andReturnFalse();
        $versions->shouldReceive('getPanel')->withNoArgs()->andReturn('26.10.0');
        $this->app->instance(SoftwareVersionService::class, $versions);

        foreach (['native', 'docker', 'unknown'] as $installation) {
            config()->set('panel.installation_type', $installation);
            $url = $installation === 'native'
                ? SoftwareUpdates::getUrl()
                : 'https://docs.reviactyl.app/project/panel/updating-the-panel';

            $widget = Livewire::test(UpdateWidget::class)
                ->assertSuccessful()
                ->assertSee(trans('admin/index.notuptodate-header'))
                ->assertSee('26.10.0')
                ->assertSeeHtml('href="'.$url.'"');

            for ($request = 0; $request < 2; $request++) {
                $widget->refresh()
                    ->assertSuccessful()
                    ->assertSee(trans('admin/index.notuptodate-header'))
                    ->assertSee('26.10.0')
                    ->assertSeeHtml('href="'.$url.'"');

                $action = $widget->instance()->form->getComponents()[0]->getHeaderActions()[0];
                $this->assertSame($installation !== 'native', $action->shouldOpenUrlInNewTab());
            }
        }
    }

    public function test_current_widget_remains_hidden_after_hydration(): void
    {
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('isLatestPanel')->withNoArgs()->andReturnTrue();
        $versions->shouldReceive('getPanel')->withNoArgs()->andReturn('26.09.0');
        $this->app->instance(SoftwareVersionService::class, $versions);

        Livewire::test(UpdateWidget::class)
            ->assertSuccessful()
            ->assertDontSee(trans('admin/index.notuptodate-header'))
            ->refresh()
            ->assertSuccessful()
            ->assertDontSee(trans('admin/index.notuptodate-header'));
    }

    public function test_widget_requires_update_read_permission(): void
    {
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('isLatestPanel')->withNoArgs()->andReturnTrue();
        $versions->shouldReceive('getPanel')->withNoArgs()->andReturn('26.09.0');
        $this->app->instance(SoftwareVersionService::class, $versions);

        $this->assertTrue(UpdateWidget::canView());

        foreach ([[], ['updates.update'], ['updates.read']] as $permissions) {
            $user = User::factory()->create();
            $user->roles()->attach(Role::factory()->create(['permissions' => $permissions]));
            $this->actingAs($user);

            $this->assertSame(in_array('updates.read', $permissions, true), UpdateWidget::canView());

            if (in_array('updates.read', $permissions, true)) {
                Livewire::test(UpdateWidget::class)->assertSuccessful()->refresh()->assertSuccessful();
            } else {
                Livewire::test(UpdateWidget::class)->assertForbidden();
            }
        }

        auth()->logout();
        $this->assertFalse(UpdateWidget::canView());
    }

    public function test_lazy_widget_mounts_and_refreshes_with_its_dependency(): void
    {
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('isLatestPanel')->withNoArgs()->andReturnFalse();
        $versions->shouldReceive('getPanel')->withNoArgs()->andReturn('26.10.0');
        $this->app->instance(SoftwareVersionService::class, $versions);

        $this->assertTrue(UpdateWidget::isLazy());
        $widget = Livewire::test(UpdateWidget::class, ['lazy' => true])
            ->assertSuccessful()
            ->assertDontSee('26.10.0');

        preg_match("/__lazyLoad\('([^']+)'\)/", html_entity_decode($widget->html()), $matches);
        $this->assertArrayHasKey(1, $matches);

        $widget->call('__lazyLoad', $matches[1])
            ->assertSuccessful()
            ->assertSee('26.10.0')
            ->refresh()
            ->assertSuccessful()
            ->assertSee('26.10.0');
    }
}
