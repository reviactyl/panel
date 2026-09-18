<?php

namespace Tests\Integration\Admin;

use App\Filament\Pages\SoftwareUpdates;
use App\Jobs\Updates\UpdatePanelJob;
use App\Models\Location;
use App\Models\Node;
use App\Models\User;
use App\Repositories\Agent\DaemonConfigurationRepository;
use App\Services\Helpers\SoftwareVersionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Mockery;
use Tests\Integration\IntegrationTestCase;

class SoftwareUpdatesTest extends IntegrationTestCase
{
    public function test_channel_selection_refreshes_the_page_and_is_preserved_when_queued(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => 1]));
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        config()->set('queue.default', 'database');
        config()->set('queue.connections.database.retry_after', 3660);
        Queue::fake();
        Node::factory()->for(Location::factory())->create(['name' => 'Test Agent']);
        $repository = Mockery::mock(DaemonConfigurationRepository::class);
        $repository->shouldReceive('setNode')->andReturnSelf();
        $repository->shouldReceive('getSystemInformation')->andReturn([
            'version' => '26.08.02',
            'installation_type' => 'native',
        ]);
        $this->app->instance(DaemonConfigurationRepository::class, $repository);
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('getPanel')->with('stable')->andReturn('26.09.0');
        $versions->shouldReceive('getPanel')->with('beta')->andReturn('26.10.0-rc.1');
        $versions->shouldReceive('getDaemon')->with('stable')->andReturn('26.09.0');
        $versions->shouldReceive('getDaemon')->with('beta')->andReturn('26.10.0-beta.1');
        $versions->shouldReceive('isLatestPanel')->with('stable')->andReturnTrue();
        $versions->shouldReceive('isLatestPanel')->with('beta')->andReturnFalse();
        $versions->shouldReceive('isLatestDaemon')->with('26.08.02', 'stable')->andReturnFalse();
        $versions->shouldReceive('isLatestDaemon')->with('26.08.02', 'beta')->andReturnFalse();
        $this->app->instance(SoftwareVersionService::class, $versions);

        Livewire::test(SoftwareUpdates::class)
            ->assertSee('Release channel')
            ->assertSee('Component')
            ->assertSee(trans('admin/updates.actions'))
            ->assertSee('Up to date')
            ->assertSee('Test Agent')
            ->assertSee('panelUpdateRequested', false)
            ->assertSee('panelUpdatePollTimer', false)
            ->assertSee('panelUpdatePollController', false)
            ->assertSee('panelUpdatePollingActive', false)
            ->assertSee('panelWasUnavailable', false)
            ->assertSee('panelUpdateRequestRejected', false)
            ->assertSee('fetch(url', false)
            ->assertSee('new AbortController()', false)
            ->assertSee('signal: controller.signal', false)
            ->assertSee("status.state === 'idle'", false)
            ->assertSee('window.location.reload()', false)
            ->assertSee('wire:loading.flex', false)
            ->assertSee('Waiting for Update Worker')
            ->assertDontSee('Waiting for the update worker.')
            ->assertDontSee('software-update-spinner', false)
            ->assertSet('channel', 'stable')
            ->assertSet('panel.outdated', false)
            ->set('channel', 'beta')
            ->assertSet('panel.latest', '26.10.0-rc.1')
            ->assertSet('panel.outdated', true)
            ->assertDontSeeHtml('wire:confirm')
            ->call('updatePanel')
            ->assertHasNoErrors()
            ->assertSet('panelUpdateInProgress', true)
            ->assertSee(trans('admin/updates.status.queued'))
            ->assertDontSeeHtml('wire:poll.10s')
            ->set('channel', 'stable')
            ->assertSet('panel.latest', '26.09.0')
            ->set('channel', 'nightly')
            ->assertHasErrors(['channel'])
            ->call('refreshUpdates')
            ->assertSet('channel', 'stable');

        Queue::assertPushed(UpdatePanelJob::class, function (UpdatePanelJob $job): bool {
            $restored = unserialize(serialize($job));

            return $restored->version === '26.10.0-rc.1' && $restored->channel === 'beta';
        });

        $this->get(route('admin.software-updates.status'))
            ->assertOk()
            ->assertJson([
                'state' => 'queued',
                'version' => '26.10.0-rc.1',
            ]);
    }

    public function test_panel_update_admission_is_serialized(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => 1]));
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        Queue::fake();
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('getPanel')->with('stable')->andReturn('26.09.1');
        $versions->shouldReceive('getDaemon')->with('stable')->andReturn('error');
        $versions->shouldReceive('isLatestPanel')->with('stable')->andReturnFalse();
        $this->app->instance(SoftwareVersionService::class, $versions);

        $lock = Cache::lock('software-update:panel:admission', 120);
        $this->assertTrue($lock->get());

        try {
            Livewire::test(SoftwareUpdates::class)
                ->call('updatePanel')
                ->assertHasNoErrors();

            Queue::assertNothingPushed();
        } finally {
            $lock->release();
        }
    }
}
