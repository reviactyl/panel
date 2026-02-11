<?php

namespace Tests\Integration\Admin;

use App\Tests\Integration\IntegrationTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SanityTest extends IntegrationTestCase
{
    // use RefreshDatabase;

    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }
}
