<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\AdminAuthenticate;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AdminAuthenticateTest extends MiddlewareTestCase
{
    /**
     * Test that an admin is authenticated.
     */
    public function test_admins_are_authenticated()
    {
        $user = User::factory()->make(['root_admin' => 1]);

        $this->request->shouldReceive('user')->withNoArgs()->twice()->andReturn($user);

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that a missing user in the request triggers an error.
     */
    public function test_exception_is_thrown_if_user_does_not_exist()
    {
        $this->expectException(AuthenticationException::class);

        $this->request->shouldReceive('user')->withNoArgs()->once()->andReturnNull();

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Test that an exception is thrown if the user is not an admin.
     */
    public function test_exception_is_thrown_if_user_is_not_an_admin()
    {
        $this->expectException(AccessDeniedHttpException::class);

        $user = User::factory()->make(['root_admin' => 0]);

        $this->request->shouldReceive('user')->withNoArgs()->twice()->andReturn($user);

        $this->getMiddleware()->handle($this->request, $this->getClosureAssertions());
    }

    /**
     * Return an instance of the middleware using mocked dependencies.
     */
    private function getMiddleware(): AdminAuthenticate
    {
        return new AdminAuthenticate();
    }
}
