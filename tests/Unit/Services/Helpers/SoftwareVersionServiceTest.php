<?php

namespace Tests\Unit\Services\Helpers;

use App\Services\Helpers\SoftwareVersionService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Mockery;
use Tests\TestCase;

class SoftwareVersionServiceTest extends TestCase
{
    public function test_canary_panel_does_not_offer_a_stable_release_as_an_update(): void
    {
        config()->set('app.version', 'canary');
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')
            ->once()
            ->with('GET', config('panel.cdn.url'))
            ->andReturn(new Response(200, [], json_encode(['panel' => '26.09.0'], JSON_THROW_ON_ERROR)));

        $service = new SoftwareVersionService(new Repository(new ArrayStore()), $client);

        $this->assertSame('26.09.0', $service->getPanel());
        $this->assertTrue($service->isLatestPanel());
    }

    public function test_channels_filter_and_order_official_releases_independently(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->with('GET', config('panel.cdn.url'))->andReturn(new Response(200, [], '{}'));
        $releases = [
            ['tag_name' => 'v26.10.0-beta.2', 'prerelease' => false],
            ['tag_name' => 'v26.10.0-beta.11', 'prerelease' => true],
            ['tag_name' => 'v26.10.0-rc.1', 'prerelease' => false],
            ['tag_name' => 'v26.09.0', 'prerelease' => false],
            ['tag_name' => 'v26.11.0', 'prerelease' => true],
            ['tag_name' => 'v26.12.0-alpha.1', 'prerelease' => true],
            ['tag_name' => 'v27.0.0', 'draft' => true],
            ['tag_name' => 'v99.0.0/../../bad'],
        ];
        $client->shouldReceive('request')->with('GET', 'https://api.github.com/repos/reviactyl/panel/releases', Mockery::type('array'))
            ->twice()->andReturn(new Response(200, [], json_encode($releases, JSON_THROW_ON_ERROR)));
        $client->shouldReceive('request')->with('GET', 'https://api.github.com/repos/reviactyl/agent/releases', Mockery::type('array'))
            ->once()->andReturn(new Response(200, [], json_encode(array_slice($releases, 0, 4), JSON_THROW_ON_ERROR)));
        $service = new SoftwareVersionService(new Repository(new ArrayStore()), $client);
        $this->assertSame('26.09.0', $service->getPanel('stable'));
        $this->assertSame('26.11.0', $service->getPanel('beta'));
        $this->assertSame('26.10.0-rc.1', $service->getDaemon('beta'));
        $this->assertSame('26.09.0', $service->getPanel('stable'));
        config()->set('app.version', '26.10.0-rc.1');
        $this->assertTrue($service->isLatestPanel('stable'));
    }

    public function test_beta_channel_advances_to_final_and_reads_subsequent_pages(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->with('GET', config('panel.cdn.url'))->andReturn(new Response(200, [], '{}'));
        $client->shouldReceive('request')->with('GET', 'https://api.github.com/repos/reviactyl/panel/releases', Mockery::on(fn ($options) => $options['query']['page'] === 1))
            ->once()->andReturn(new Response(200, [], json_encode(array_fill(0, 100, ['tag_name' => 'v26.10.0-beta.1']), JSON_THROW_ON_ERROR)));
        $client->shouldReceive('request')->with('GET', 'https://api.github.com/repos/reviactyl/panel/releases', Mockery::on(fn ($options) => $options['query']['page'] === 2))
            ->once()->andReturn(new Response(200, [], '[{"tag_name":"v26.10.0"}]'));
        $service = new SoftwareVersionService(new Repository(new ArrayStore()), $client);
        config()->set('app.version', '26.10.0-rc.1');
        $this->assertSame('26.10.0', $service->getPanel('beta'));
        $this->assertFalse($service->isLatestPanel('beta'));
    }

    public function test_large_release_history_keeps_the_newest_resolved_version(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->with('GET', config('panel.cdn.url'))->andReturn(new Response(200, [], '{}'));
        $client->shouldReceive('request')->with('GET', 'https://api.github.com/repos/reviactyl/panel/releases', Mockery::type('array'))
            ->times(10)->andReturn(new Response(200, [], json_encode(array_fill(0, 100, ['tag_name' => 'v26.10.0-beta.1']), JSON_THROW_ON_ERROR)));
        $service = new SoftwareVersionService(new Repository(new ArrayStore()), $client);
        $this->assertSame('26.10.0-beta.1', $service->getPanel('beta'));
    }

    public function test_release_lookup_failure_does_not_fall_back_to_the_other_channel(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->with('GET', config('panel.cdn.url'))->andReturn(new Response(200, [], '{"panel":"26.09.0"}'));
        $client->shouldReceive('request')->with('GET', 'https://api.github.com/repos/reviactyl/panel/releases', Mockery::type('array'))
            ->once()->andReturn(new Response(503));
        $service = new SoftwareVersionService(new Repository(new ArrayStore()), $client);
        $this->assertSame('error', $service->getPanel('beta'));
        $this->expectException(\InvalidArgumentException::class);
        $service->getPanel('nightly');
    }
}
