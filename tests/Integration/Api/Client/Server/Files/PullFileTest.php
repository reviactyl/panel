<?php

namespace Tests\Integration\Api\Client\Server\Files;

use App\Models\Permission;
use App\Repositories\Agent\DaemonFileRepository;
use GuzzleHttp\Psr7\Response;
use Mockery\MockInterface;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class PullFileTest extends ClientApiIntegrationTestCase
{
    public function test_pull_returns_agent_download_identifier(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_CREATE]);

        $this->mock(DaemonFileRepository::class, function (MockInterface $mock) {
            $mock->expects('setServer->pull')
                ->with('https://example.com/archive.tar.gz', '/', [])
                ->andReturn(new Response(202, [], json_encode(['identifier' => 'download-id'])));
        });

        $this->actingAs($user)
            ->postJson($this->link($server, '/files/pull'), [
                'url' => 'https://example.com/archive.tar.gz',
                'directory' => '/',
            ])
            ->assertAccepted()
            ->assertExactJson(['identifier' => 'download-id']);
    }

    public function test_active_downloads_are_returned_from_agent(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_CREATE]);

        $downloads = [
            ['identifier' => 'download-id', 'progress' => 0.5],
        ];

        $this->mock(DaemonFileRepository::class, function (MockInterface $mock) use ($downloads) {
            $mock->expects('setServer->getPulls')
                ->andReturn(new Response(200, [], json_encode(['downloads' => $downloads])));
        });

        $this->actingAs($user)
            ->getJson($this->link($server, '/files/pull'))
            ->assertOk()
            ->assertExactJson(['downloads' => $downloads]);
    }

    public function test_download_status_requires_file_create_permission(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_READ]);

        $this->actingAs($user)
            ->getJson($this->link($server, '/files/pull'))
            ->assertForbidden();
    }
}
