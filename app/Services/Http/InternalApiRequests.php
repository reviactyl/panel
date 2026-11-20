<?php

namespace App\Services\Http;

use App\Http\Middleware\EnsureStatefulRequests;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;

class InternalApiRequests
{
    public function __construct(private Container $app, private Router $router) {}

    public function get(Request $page, string $uri): ?string
    {
        $request = Request::create($uri, 'GET', [], $page->cookies->all(), [], [
            ...$page->server->all(),
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_REFERER' => $page->fullUrl(),
        ]);
        if ($page->hasSession()) {
            $request->setLaravelSession($page->session());
        }
        $request->setUserResolver($page->getUserResolver());
        $request->attributes->set('sanctum', true);

        $original = $this->app->make('request');
        // The API's auth middleware switches the default guard to Sanctum's request guard, which
        // would otherwise stay in place for the rest of the page request.
        $auth = $this->app->make('auth');
        $guard = $auth->getDefaultDriver();
        $this->use($request);

        try {
            $route = $this->router->getRoutes()->match($request);
            $request->setRouteResolver(fn () => $route);
            $middleware = array_values(array_filter(
                $this->router->gatherRouteMiddleware($route),
                fn ($middleware) => ! is_string($middleware) || explode(':', $middleware)[0] !== EnsureStatefulRequests::class,
            ));

            $response = (new Pipeline($this->app))
                ->send($request)
                ->through($middleware)
                ->then(fn (Request $request) => $this->router->prepareResponse($request, $route->run()));
        } catch (\Throwable) {
            return null;
        } finally {
            $this->use($original);
            $auth->shouldUse($guard);
        }

        if ($response->getStatusCode() !== 200 || ! str_contains((string) $response->headers->get('Content-Type'), 'json')) {
            return null;
        }

        return $response->getContent() ?: null;
    }

    private function use(Request $request): void
    {
        $this->app->instance('request', $request);
        Facade::clearResolvedInstance('request');
    }
}
