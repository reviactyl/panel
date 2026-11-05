<?php

namespace Tests\Integration\Api\Client\Server\Files;

use App\Exceptions\Http\Connection\DaemonConnectionException;
use App\Models\Permission;
use App\Repositories\Agent\DaemonFileRepository;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery\MockInterface;
use Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class CreateFileTest extends ClientApiIntegrationTestCase
{
    public function test_create_only_permission_does_not_require_read_or_update(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_CREATE]);
        $this->mock(DaemonFileRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('setServer')->once()->andReturnSelf();
            $mock->shouldReceive('createContent')->once()->with('/new.txt', 'draft')->andReturn(new Response(204));
            $mock->shouldNotReceive('getDirectory', 'putContent');
        });
        $this->actingAs($user)->call('POST', $this->link($server, '/files/create').'?file=/new.txt', [], [], [], ['CONTENT_TYPE' => 'text/plain'], 'draft')->assertNoContent();
    }

    public function test_creation_requires_create_permission(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_READ, Permission::ACTION_FILE_UPDATE]);
        $this->mock(DaemonFileRepository::class, fn (MockInterface $mock) => $mock->shouldNotReceive('createContent'));
        $this->actingAs($user)->postJson($this->link($server, '/files/create').'?file=/new.txt')->assertForbidden();
    }

    public function test_agent_collision_is_returned_without_overwrite_fallback(): void
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_FILE_CREATE]);
        $this->mock(DaemonFileRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('setServer')->once()->andReturnSelf();
            $mock->shouldReceive('createContent')->once()->andThrow(new DaemonConnectionException(new ClientException(
                'Conflict', new Request('POST', '/files/create'), new Response(409, [], '{"error":"Already exists"}')
            )));
            $mock->shouldNotReceive('putContent');
        });
        $this->actingAs($user)->postJson($this->link($server, '/files/create').'?file=/existing.txt')->assertConflict();
    }
}
