<?php

namespace Tests\Integration\Models;

use App\Models\EggVariable;
use Tests\Integration\IntegrationTestCase;

class EggVariableTest extends IntegrationTestCase
{
    public function test_egg_relationship(): void
    {
        $server = $this->createServerModel();
        $variable = EggVariable::query()->where('egg_id', $server->egg_id)->firstOrFail();

        $this->assertTrue($variable->egg->is($server->egg));
    }
}
