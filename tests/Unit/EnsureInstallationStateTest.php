<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureInstallationState;
use App\Support\InstallationState;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Mockery;
use Tests\TestCase;

class EnsureInstallationStateTest extends TestCase
{
    public function test_it_redirects_to_the_installer_when_the_application_has_not_been_installed(): void
    {
        $installationState = Mockery::mock(InstallationState::class);
        $installationState->shouldReceive('isInstalled')->andReturnFalse();

        $middleware = new EnsureInstallationState($installationState);
        $request = Request::create('/');
        $request->setRouteResolver(static fn () => new class
        {
            public function getName(): string
            {
                return 'index';
            }
        });

        $response = $middleware->handle($request, static fn () => response('next'));

        $this->assertSame(route('installer'), $response->getTargetUrl());
    }

    public function test_it_allows_the_installer_route_when_the_application_has_not_been_installed(): void
    {
        $installationState = Mockery::mock(InstallationState::class);
        $installationState->shouldReceive('isInstalled')->andReturnFalse();

        $middleware = new EnsureInstallationState($installationState);
        $request = Request::create('/install');
        $request->setRouteResolver(static fn () => new class
        {
            public function getName(): string
            {
                return 'installer';
            }
        });

        $response = $middleware->handle($request, static fn () => response('next'));

        $this->assertSame('next', $response->getContent());
    }

    public function test_it_redirects_away_from_the_installer_after_installation(): void
    {
        $installationState = Mockery::mock(InstallationState::class);
        $installationState->shouldReceive('isInstalled')->andReturnTrue();

        $middleware = new EnsureInstallationState($installationState);
        $request = Request::create('/install');
        $request->setRouteResolver(static fn () => new class
        {
            public function getName(): string
            {
                return 'installer';
            }
        });

        $response = $middleware->handle($request, static fn () => response('next'));

        $this->assertSame(route('index'), $response->getTargetUrl());
    }

    public function test_it_allows_the_installer_route_when_installation_is_in_progress(): void
    {
        $installationState = Mockery::mock(InstallationState::class);
        $installationState->shouldReceive('isInstalled')->andReturnTrue();

        $middleware = new EnsureInstallationState($installationState);
        $request = Request::create('/install');

        $session = app(Store::class);
        $session->put('installer.in_progress', true);
        $request->setLaravelSession($session);

        $request->setRouteResolver(static fn () => new class
        {
            public function getName(): string
            {
                return 'installer';
            }
        });

        $response = $middleware->handle($request, static fn () => response('next'));

        $this->assertSame('next', $response->getContent());
    }
}
