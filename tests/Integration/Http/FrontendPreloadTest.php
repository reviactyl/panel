<?php

namespace Tests\Integration\Http;

use App\Http\ViewComposers\FrontendPreloadComposer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\TestWith;

class FrontendPreloadTest extends HttpTestCase
{
    private string $publicPath;

    private string $originalPublicPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalPublicPath = public_path();
        $this->publicPath = sys_get_temp_dir().'/reviactyl-preload-'.uniqid();
        File::ensureDirectoryExists($this->publicPath.'/build');
        File::put($this->publicPath.'/build/manifest.json', json_encode([
            'resources/scripts/index.tsx' => ['file' => 'assets/index.js', 'isEntry' => true, 'imports' => ['_react.js']],
            '_react.js' => ['file' => 'assets/react.js'],
            '_shared.js' => ['file' => 'assets/shared.js', 'css' => ['assets/shared.css']],
            'resources/scripts/routers/AuthenticationRouter.tsx' => ['file' => 'assets/auth.js', 'imports' => ['_react.js']],
            'resources/scripts/routers/DashboardRouter.tsx' => ['file' => 'assets/dashboard.js', 'imports' => ['_shared.js']],
            'resources/scripts/routers/ServerRouter.tsx' => ['file' => 'assets/server.js', 'imports' => ['_shared.js']],
            'resources/scripts/components/server/console/ServerConsoleContainer.tsx' => ['file' => 'assets/console.js'],
            'resources/scripts/components/server/files/FileManagerContainer.tsx' => ['file' => 'assets/files.js'],
        ]));
        $this->app->usePublicPath($this->publicPath);
    }

    protected function tearDown(): void
    {
        $this->app->usePublicPath($this->originalPublicPath);
        File::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    public function test_login_page_preloads_the_authentication_router_without_prefetching(): void
    {
        $this->get('/auth/login')
            ->assertOk()
            ->assertSee('<link rel="modulepreload" href="'.asset('build/assets/auth.js').'" />', false)
            ->assertDontSee('build/assets/react.js', false)
            ->assertDontSee('build/assets/dashboard.js', false)
            ->assertDontSee('"/api/client', false)
            ->assertSee('boot-indicator', false);
    }

    public function test_server_page_preloads_router_screen_and_prefetches_api_requests(): void
    {
        [$user, $server] = $this->generateTestAccount();

        $this->actingAs($user)
            ->get('/server/'.$server->uuidShort.'/files')
            ->assertOk()
            ->assertSee('build/assets/server.js', false)
            ->assertSee('build/assets/shared.js', false)
            ->assertSee('<link rel="preload" as="style" href="'.asset('build/assets/shared.css').'" crossorigin />', false)
            ->assertSee('build/assets/files.js', false)
            ->assertDontSee('build/assets/console.js', false)
            ->assertSee('__REVIACTYL_PREFETCH__', false);
    }

    public function test_missing_manifest_emits_no_preloads(): void
    {
        File::delete($this->publicPath.'/build/manifest.json');

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertDontSee('rel="modulepreload"', false);
    }

    #[TestWith(['auth/login', false, ['routers/AuthenticationRouter.tsx']])]
    #[TestWith(['auth/login', true, ['routers/AuthenticationRouter.tsx']])]
    #[TestWith(['', false, []])]
    #[TestWith(['', true, ['routers/DashboardRouter.tsx']])]
    #[TestWith(['status/abc', true, []])]
    #[TestWith(['admin/users', true, []])]
    #[TestWith(['account', true, ['routers/DashboardRouter.tsx', 'components/dashboard/AccountOverviewContainer.tsx']])]
    #[TestWith(['account/api', true, ['routers/DashboardRouter.tsx', 'components/dashboard/AccountApiContainer.tsx']])]
    #[TestWith(['server/abc', true, ['routers/ServerRouter.tsx', 'components/server/console/ServerConsoleContainer.tsx']])]
    #[TestWith(['server/abc/files/edit/x.txt', true, ['routers/ServerRouter.tsx', 'components/server/files/FileEditContainer.tsx']])]
    #[TestWith(['server/abc/schedules/4', true, ['routers/ServerRouter.tsx', 'components/server/schedules/ScheduleEditContainer.tsx']])]
    #[TestWith(['server/abc/unknown', true, ['routers/ServerRouter.tsx']])]
    public function test_chunks_for_path(string $path, bool $authenticated, array $expected): void
    {
        $composer = new FrontendPreloadComposer(new Request());

        $this->assertSame($expected, $composer->chunksForPath($path, $authenticated));
    }
}
