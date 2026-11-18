<?php

namespace Tests\Integration\Scramble;

use App\Http\Middleware\ApiDocAccess;
use Illuminate\Auth\Middleware\Authenticate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DocumentationBootTest extends TestCase
{
    public function test_named_apis_are_available_before_providers_boot(): void
    {
        $process = $this->process(['-r', <<<'CODE'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->booting(function () {
    foreach (['client', 'application'] as $api) {
        $config = \Dedoc\Scramble\Scramble::configure($api);
        if ($config->get('api_path') !== "api/$api" || $config->get('export_path') !== "$api.json") {
            throw new \RuntimeException("Incorrect $api documentation configuration.");
        }
    }
});
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app->boot();
echo 'booted';
CODE]);

        $this->assertTrue($process->isSuccessful(), $process->getOutput().$process->getErrorOutput());
        $this->assertSame('booted', $process->getOutput());
    }

    public function test_documentation_routes_survive_caching_and_repeated_application_boots(): void
    {
        $cachePath = sys_get_temp_dir().'/scramble-routes-'.bin2hex(random_bytes(8)).'.php';
        $environment = ['APP_ROUTES_CACHE' => $cachePath];

        try {
            $uncached = $this->documentationRoutes($environment);
            $cache = $this->process(['artisan', 'route:cache'], $environment);
            $this->assertTrue($cache->isSuccessful(), $cache->getOutput().$cache->getErrorOutput());
            $this->assertFileExists($cachePath);

            $this->assertSame($uncached, $this->documentationRoutes($environment));
            $this->assertSame($uncached, $this->documentationRoutes($environment));
        } finally {
            if (is_file($cachePath)) {
                unlink($cachePath);
            }
        }
    }

    private function documentationRoutes(array $environment): array
    {
        $process = $this->process(['artisan', 'route:list', '--path=docs', '--json'], $environment);
        $this->assertTrue($process->isSuccessful(), $process->getOutput().$process->getErrorOutput());
        $routes = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame([
            'docs/api',
            'docs/api/application',
            'docs/api/application.json',
            'docs/api/client',
            'docs/api/client.json',
        ], array_column($routes, 'uri'));

        foreach (array_slice($routes, 1) as $route) {
            $this->assertContains(Authenticate::class.':sanctum', $route['middleware']);
            $this->assertContains(ApiDocAccess::class, $route['middleware']);
        }

        return array_map(fn (array $route) => [
            'uri' => $route['uri'],
            'method' => $route['method'],
            'action' => $route['action'],
            'middleware' => $route['middleware'],
        ], $routes);
    }

    private function process(array $arguments, array $environment = []): Process
    {
        $process = new Process([PHP_BINARY, ...$arguments], dirname(__DIR__, 3), array_merge([
            'APP_KEY' => 'SomeRandomString3232RandomString',
            'CACHE_STORE' => 'array',
        ], $environment));
        $process->run();

        return $process;
    }
}
