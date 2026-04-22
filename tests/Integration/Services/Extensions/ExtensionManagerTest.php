<?php

namespace Tests\Integration\Services\Extensions;

use App\Models\Extension;
use App\Services\Extensions\Exceptions\ExtensionInstallException;
use App\Services\Extensions\ExtensionManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Integration\IntegrationTestCase;

class ExtensionManagerTest extends IntegrationTestCase
{
    private string $testRoot;

    private string $publicPath;

    private string $tempPath;

    private string $originalPublicPath;

    private string $originalTempPath;

    /** @var string[] */
    private array $extensionIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->testRoot = storage_path('framework/testing/extensions/'.Str::uuid());
        $this->publicPath = $this->testRoot.'/public';
        $this->tempPath = $this->testRoot.'/tmp';

        File::ensureDirectoryExists($this->publicPath);
        File::ensureDirectoryExists($this->tempPath);

        $this->originalPublicPath = (string) config('extensions.storage.public_path');
        $this->originalTempPath = (string) config('extensions.storage.temp_path');

        config()->set('extensions.storage.public_path', $this->publicPath);
        config()->set('extensions.storage.temp_path', $this->tempPath);
    }

    protected function tearDown(): void
    {
        foreach ($this->extensionIds as $id) {
            File::deleteDirectory(base_path('extensions/'.$id));
            File::deleteDirectory($this->publicPath.'/'.$id);
        }

        File::deleteDirectory($this->testRoot);

        config()->set('extensions.storage.public_path', $this->originalPublicPath);
        config()->set('extensions.storage.temp_path', $this->originalTempPath);

        parent::tearDown();
    }

    public function test_extension_package_can_be_installed_toggled_and_removed(): void
    {
        [$archivePath, $identifier] = $this->createExtensionPackage();

        $manager = $this->app->make(ExtensionManager::class);
        $installed = $manager->installFromArchive($archivePath);
        $installed = $installed->refresh();

        $this->assertSame($identifier, $installed->identifier);
        $this->assertFalse($installed->enabled);
        $this->assertDatabaseHas('extensions', ['identifier' => $identifier, 'enabled' => false]);

        $extensionPath = base_path('extensions/'.$identifier);
        $this->assertFileExists($extensionPath.'/extension.json');
        $this->assertDirectoryExists($extensionPath.'/backend');
        $this->assertDirectoryExists($extensionPath.'/frontend');
        $this->assertDirectoryExists($extensionPath.'/public');
        $this->assertDirectoryExists($extensionPath.'/data');
        $this->assertDirectoryExists($extensionPath.'/cache');
        $this->assertDirectoryExists($extensionPath.'/private');

        $this->assertFileExists($this->publicPath.'/'.$identifier.'/assets/banner.txt');
        $this->assertFileExists($this->publicPath.'/'.$identifier.'/frontend/dist/main.js');

        $enabled = $manager->enable($identifier);
        $this->assertTrue($enabled->enabled);
        $this->assertSame(ExtensionManager::API_VERSION, $enabled->api_version);

        $disabled = $manager->disable($identifier);
        $this->assertFalse($disabled->enabled);

        $manager->remove($identifier);

        $this->assertDatabaseMissing('extensions', ['identifier' => $identifier]);
        $this->assertDirectoryDoesNotExist(base_path('extensions/'.$identifier));
        $this->assertDirectoryDoesNotExist($this->publicPath.'/'.$identifier);
    }

    public function test_remote_install_respects_security_policy(): void
    {
        $manager = $this->app->make(ExtensionManager::class);

        config()->set('extensions.security.allow_remote_installs', false);

        $this->expectException(ExtensionInstallException::class);
        $this->expectExceptionMessage('Remote extension installs are disabled by policy.');

        $manager->installFromSource('https://example.com/example-extension.rext');
    }

    public function test_remote_install_can_download_archive_and_install(): void
    {
        [$archivePath, $identifier] = $this->createExtensionPackage();

        Http::fake([
            'https://example.com/extension.rext' => Http::response(file_get_contents($archivePath), 200),
        ]);

        $manager = $this->app->make(ExtensionManager::class);
        $installed = $manager->installFromSource('https://example.com/extension.rext');

        $this->assertSame($identifier, $installed->identifier);
        $this->assertDatabaseHas('extensions', ['identifier' => $identifier]);
    }

    public function test_frontend_registry_normalizes_and_versions_module_paths(): void
    {
        [$archivePath, $identifier] = $this->createExtensionPackage([
            'frontend' => [
                'entry_points' => ['frontend/src/main.tsx'],
                'routes' => [
                    'dashboardRouter' => [
                        ['path' => '/dashboard/extensions', 'module' => 'frontend/src/dashboard.tsx'],
                    ],
                    'serverRouter' => [
                        ['path' => '/server/extensions', 'module' => 'frontend/src/server.ts'],
                    ],
                ],
                'slots' => [
                    ['name' => 'dashboard.after-cards', 'module' => 'frontend/src/slot.tsx'],
                ],
            ],
        ], [
            'frontend/dist/main.js' => 'export default function Main() { return null; }',
            'frontend/dist/dashboard.js' => 'export default function Dashboard() { return null; }',
            'frontend/dist/server.js' => 'export default function ServerView() { return null; }',
            'frontend/dist/slot.js' => 'export default function Slot() { return null; }',
        ]);

        $manager = $this->app->make(ExtensionManager::class);
        $manager->installFromArchive($archivePath);
        $manager->enable($identifier);

        $registry = $manager->frontendRegistry();

        $this->assertCount(1, $registry);
        $record = $registry[0];

        $this->assertSame($identifier, $record['id']);
        $this->assertSame('frontend/dist/main.js', $record['frontend']['entry_points'][0]);
        $this->assertStringStartsWith('frontend/dist/slot.js?v=', $record['frontend']['slots'][0]['module']);
        $this->assertStringStartsWith('frontend/dist/dashboard.js?v=', $record['frontend']['routes']['dashboardRouter'][0]['module']);
        $this->assertStringStartsWith('frontend/dist/server.js?v=', $record['frontend']['routes']['serverRouter'][0]['module']);
    }

    public function test_archive_with_path_traversal_is_rejected(): void
    {
        $archivePath = $this->testRoot.'/malicious.rext';
        $zip = new \ZipArchive();

        $this->assertTrue($zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $zip->addFromString('extension.json', json_encode([
            'id' => 'malicious-ext',
            'name' => 'Malicious Extension',
            'version' => '1.0.0',
            'api_version' => ExtensionManager::API_VERSION,
        ], JSON_THROW_ON_ERROR));
        $zip->addFromString('../escape.txt', 'escape');
        $zip->close();

        $manager = $this->app->make(ExtensionManager::class);

        $this->expectException(ExtensionInstallException::class);
        $this->expectExceptionMessage('Archive contains invalid paths.');

        $manager->installFromArchive($archivePath);
    }

    public function test_apply_schedules_uses_manifest_schedule_definitions(): void
    {
        $identifier = 'schedule-ext-'.Str::lower(Str::random(6));
        $this->extensionIds[] = $identifier;

        Extension::query()->create([
            'identifier' => $identifier,
            'name' => 'Schedule Test',
            'version' => '1.0.0',
            'enabled' => true,
            'api_version' => ExtensionManager::API_VERSION,
            'manifest' => [
                'id' => $identifier,
                'name' => 'Schedule Test',
                'version' => '1.0.0',
                'api_version' => ExtensionManager::API_VERSION,
                'backend' => [
                    'schedules' => [
                        ['command' => 'extensions:test-cron', 'cron' => '*/5 * * * *'],
                        ['command' => 'extensions:test-hourly', 'frequency' => 'hourly'],
                        ['frequency' => 'daily'],
                    ],
                ],
            ],
        ]);

        $cronEvent = new class
        {
            public ?string $cronExpression = null;

            public function cron(string $expression): void
            {
                $this->cronExpression = $expression;
            }
        };

        $hourlyEvent = new class
        {
            public bool $hourlyCalled = false;

            public function hourly(): void
            {
                $this->hourlyCalled = true;
            }
        };

        $schedule = \Mockery::mock(Schedule::class);
        $schedule->shouldReceive('command')->once()->with('extensions:test-cron')->andReturn($cronEvent);
        $schedule->shouldReceive('command')->once()->with('extensions:test-hourly')->andReturn($hourlyEvent);

        $manager = $this->app->make(ExtensionManager::class);
        $manager->applySchedules($schedule);

        $this->assertSame('*/5 * * * *', $cronEvent->cronExpression);
        $this->assertTrue($hourlyEvent->hourlyCalled);
    }

    /**
     * @param  array<string, mixed>  $manifestOverrides
     * @param  array<string, string>  $files
     * @return array{0: string, 1: string}
     */
    private function createExtensionPackage(array $manifestOverrides = [], array $files = []): array
    {
        $identifier = (string) ($manifestOverrides['id'] ?? ('test-ext-'.Str::lower(Str::random(8))));
        $this->extensionIds[] = $identifier;

        $manifest = array_replace_recursive([
            'id' => $identifier,
            'name' => 'Test Extension',
            'version' => '1.0.0',
            'api_version' => ExtensionManager::API_VERSION,
            'permissions' => ['extensions.read'],
            'feature_flags' => ['test-flag'],
            'frontend' => [
                'entry_points' => ['frontend/dist/main.js'],
                'slots' => [
                    ['name' => 'dashboard.after-cards', 'module' => 'frontend/dist/main.js'],
                ],
            ],
        ], $manifestOverrides);

        $sourcePath = $this->testRoot.'/src-'.$identifier;
        File::ensureDirectoryExists($sourcePath);

        $defaultFiles = [
            'frontend/dist/main.js' => 'export default function Main() { return null; }',
            'public/assets/banner.txt' => 'banner',
            'backend/.keep' => '',
        ];

        foreach (array_merge($defaultFiles, $files) as $relativePath => $contents) {
            $absolutePath = $sourcePath.'/'.ltrim($relativePath, '/');
            File::ensureDirectoryExists(dirname($absolutePath));
            File::put($absolutePath, $contents);
        }

        File::put(
            $sourcePath.'/extension.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );

        $archivePath = $this->testRoot.'/'.$identifier.'.rext';
        $zip = new \ZipArchive();

        $this->assertTrue($zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $relativePath = ltrim(Str::after($file->getPathname(), $sourcePath), DIRECTORY_SEPARATOR);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);

                continue;
            }

            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();

        return [$archivePath, $identifier];
    }
}
