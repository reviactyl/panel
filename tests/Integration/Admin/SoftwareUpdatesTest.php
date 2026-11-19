<?php

namespace Tests\Integration\Admin;

use App\Filament\Pages\SoftwareUpdates;
use App\Jobs\Updates\UpdateAgentJob;
use App\Jobs\Updates\UpdatePanelJob;
use App\Models\Location;
use App\Models\Node;
use App\Models\User;
use App\Repositories\Agent\DaemonConfigurationRepository;
use App\Services\Helpers\SoftwareVersionService;
use App\Services\Updates\SoftwareUpdateStatusService;
use Illuminate\Bus\UniqueLock;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Mockery;
use Tests\Integration\IntegrationTestCase;

class SoftwareUpdatesTest extends IntegrationTestCase
{
    public function test_agent_progress_refreshes_and_failed_updates_can_be_retried(): void
    {
        $this->actingAs(User::factory()->create(['root_admin' => 1]));
        config()->set('panel.installation_type', 'native');
        config()->set('app.version', '26.09.0');
        Queue::fake();
        $node = Node::factory()->for(Location::factory())->create(['name' => 'Progress Agent']);
        $repository = Mockery::mock(DaemonConfigurationRepository::class);
        $repository->shouldReceive('setNode')->andReturnSelf();
        $repository->shouldReceive('getSystemInformation')->andReturn([
            'version' => '26.08.02',
            'installation_type' => 'native',
        ]);
        $this->app->instance(DaemonConfigurationRepository::class, $repository);
        $versions = Mockery::mock(SoftwareVersionService::class);
        $versions->shouldReceive('getPanel')->with('stable')->andReturn('26.09.0');
        $versions->shouldReceive('getDaemon')->with('stable')->andReturn('26.09.0');
        $versions->shouldReceive('isLatestPanel')->with('stable')->andReturnTrue();
        $versions->shouldReceive('isLatestDaemon')->with('26.08.02', 'stable')->andReturnFalse();
        $this->app->instance(SoftwareVersionService::class, $versions);
        $statuses = app(SoftwareUpdateStatusService::class);
        $page = Livewire::test(SoftwareUpdates::class)->call('updateAgent', $node->id);

        foreach (['queued', 'installing', 'restarting'] as $state) {
            $message = trans('admin/updates.status.'.($state === 'queued' ? $state : 'agent_'.$state));
            $statuses->set($statuses->agentKey($node->id), $state, $message, '26.09.0');
            $page->call('refreshUpdates')->assertSet('agents.0.status.state', $state);
            $document = new \DOMDocument();
            @$document->loadHTML($page->html());
            $button = (new \DOMXPath($document))->query('(//table)[2]//tbody/tr/td[6]//button')->item(0);
            $this->assertInstanceOf(\DOMElement::class, $button);
            $this->assertSame($message, preg_replace('/\s+/', ' ', trim($button->textContent)));
            $this->assertTrue($button->hasAttribute('disabled'));
            $page->call('updateAgent', $node->id)->call('updateAllAgents');
            Queue::assertPushed(UpdateAgentJob::class, 1);
        }

        $statuses->set($statuses->agentKey($node->id), 'failed', 'Download failed: '.str_repeat('long-path/', 30));
        (new UniqueLock(app(CacheRepository::class)))->release(Queue::pushed(UpdateAgentJob::class)->sole());
        $page->call('refreshUpdates')
            ->assertSee('Download failed:')
            ->assertSeeHtml('wire:click="updateAgent('.$node->id.')"')
            ->call('updateAgent', $node->id)
            ->assertSet('agents.0.status.state', 'queued');
        Queue::assertPushed(UpdateAgentJob::class, 2);
    }

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
