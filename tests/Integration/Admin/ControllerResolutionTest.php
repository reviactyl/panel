<?php

namespace Tests\Integration\Admin;

use App\Tests\Integration\IntegrationTestCase;
use App\Http\Controllers\Admin\ServersController;
use App\Services\Activity\ActivityLogService;

class ControllerResolutionTest extends IntegrationTestCase
{
    public function testActivityLogServiceResolves()
    {
        $service = $this->app->make(ActivityLogService::class);
        $this->assertNotNull($service);
    }

    public function testControllerResolves()
    {
        $controller = $this->app->make(ServersController::class);
        $this->assertNotNull($controller);
    }
}
