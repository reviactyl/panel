<?php

namespace Tests\Integration\Api\Application\Servers;

use Tests\Integration\Api\Application\ApplicationApiIntegrationTestCase;

class ServerControllerTest extends ApplicationApiIntegrationTestCase
{
    /**
     * Test that the "skip scripts" state is returned for a server.
     */
    public function test_skip_scripts_state_is_returned()
    {
        $server = $this->createServerModel(['skip_scripts' => true]);

        $this->getJson('/api/application/servers/'.$server->id)
            ->assertOk()
            ->assertJsonPath('attributes.container.skip_scripts', true);

        $server->update(['skip_scripts' => false]);

        $this->getJson('/api/application/servers/'.$server->id)
            ->assertOk()
            ->assertJsonPath('attributes.container.skip_scripts', false);
    }
}
