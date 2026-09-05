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
}
