<?php

namespace Tests\Unit\Services\Extensions;

use App\Models\Extension;
use App\Services\Extensions\Exceptions\ExtensionInstallException;
use App\Services\Extensions\ExtensionManager;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExtensionAssetTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/extension-assets-'.bin2hex(random_bytes(8));
        foreach (['source', 'source-other', 'destination', 'destination-other'] as $directory) {
            File::ensureDirectoryExists($this->root.'/'.$directory);
        }

        File::put($this->root.'/source/module.js', 'export default null;');
        File::put($this->root.'/source-other/module.js', 'isolated fixture');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);

        parent::tearDown();
    }

    #[DataProvider('invalidPaths')]
    public function test_invalid_asset_paths_are_rejected_before_any_copy(string $path): void
    {
        File::partialMock()->shouldNotReceive('copy');
        File::shouldNotReceive('ensureDirectoryExists');

        $this->expectException(ExtensionInstallException::class);
        $this->publish($path, $path);
    }

    /** @return array<string, array{string}> */
    public static function invalidPaths(): array
    {
        return [
            'parent' => ['../source-other/module.js'],
            'nested parent' => ['nested/../../source-other/module.js'],
            'backslash parent' => ['..\\source-other\\module.js'],
            'absolute' => ['/module.js'],
            'drive' => ['C:\\module.js'],
            'stream' => ['file://module.js'],
            'null byte' => ["module\0.js"],
        ];
    }

    public function test_source_links_must_resolve_inside_the_source_root(): void
    {
        symlink($this->root.'/source-other', $this->root.'/source/public');
        File::partialMock()->shouldNotReceive('copy');
        File::shouldNotReceive('ensureDirectoryExists');

        $this->expectException(ExtensionInstallException::class);
        $this->publish('public/module.js', 'module.js');
    }

    #[DataProvider('destinationLinks')]
    public function test_destination_links_are_rejected_before_creating_parents(string $link, string $target): void
    {
        symlink($this->root.'/destination-other', $this->root.'/destination/'.$link);
        File::partialMock()->shouldNotReceive('copy');
        File::shouldNotReceive('ensureDirectoryExists');

        $this->expectException(ExtensionInstallException::class);
        $this->publish('module.js', $target);
    }

    /** @return array<string, array{string, string}> */
    public static function destinationLinks(): array
    {
        return [
            'existing parent' => ['linked', 'linked/module.js'],
            'missing nested parent' => ['linked', 'linked/nested/module.js'],
        ];
    }

    public function test_dangling_destination_link_is_rejected(): void
    {
        symlink($this->root.'/destination-other/missing', $this->root.'/destination/module.js');
        File::partialMock()->shouldNotReceive('copy');

        $this->expectException(ExtensionInstallException::class);
        $this->publish('module.js', 'module.js');
    }

    public function test_linked_public_directory_is_rejected_before_deleting_assets(): void
    {
        config()->set('extensions.storage.public_path', $this->root.'/destination');
        symlink($this->root.'/destination-other', $this->root.'/destination/example');
        File::partialMock()->shouldNotReceive('deleteDirectory')->with($this->root.'/destination/example');

        $this->expectException(ExtensionInstallException::class);
        $method = new \ReflectionMethod(ExtensionManager::class, 'publishAssets');
        $method->invoke($this->app->make(ExtensionManager::class), 'example', []);
    }

    public function test_valid_assets_publish_to_new_nested_directories(): void
    {
        $this->publish('module.js', 'nested/assets/module.js');

        $this->assertSame('export default null;', File::get($this->root.'/destination/nested/assets/module.js'));
    }

    public function test_contained_links_remain_supported(): void
    {
        symlink($this->root.'/source/module.js', $this->root.'/source/alias.js');
        symlink($this->root.'/destination', $this->root.'/destination/linked');

        $this->publish('alias.js', 'linked/module.js');

        $this->assertSame('export default null;', File::get($this->root.'/destination/module.js'));
    }

    public function test_versioning_uses_both_supported_locations_and_updated_mtimes(): void
    {
        File::ensureDirectoryExists($this->root.'/source/public');
        File::put($this->root.'/source/public/public-module.js', 'export default null;');
        $extension = \Mockery::mock(Extension::class)->makePartial();
        $extension->shouldReceive('getInstallPathAttribute')->andReturn($this->root.'/source');
        $method = new \ReflectionMethod(ExtensionManager::class, 'versionedFrontendModulePath');
        $manager = $this->app->make(ExtensionManager::class);

        foreach (['module.js', 'public-module.js'] as $module) {
            $file = $this->root.'/source/'.($module === 'module.js' ? '' : 'public/').$module;
            touch($file, 1700000000);
            clearstatcache();
            $this->assertSame($module.'?v=1700000000', $method->invoke($manager, $module.'?old=1', $extension));
            touch($file, 1700000001);
            clearstatcache();
            $this->assertSame($module.'?v=1700000001', $method->invoke($manager, $module, $extension));
        }

        $this->assertSame('missing.js?old=1', $method->invoke($manager, 'missing.js?old=1', $extension));
    }

    public function test_versioning_ignores_modules_outside_the_install_root(): void
    {
        symlink($this->root.'/source-other', $this->root.'/source/public');
        $extension = \Mockery::mock(Extension::class)->makePartial();
        $extension->shouldReceive('getInstallPathAttribute')->andReturn($this->root.'/source');
        $method = new \ReflectionMethod(ExtensionManager::class, 'versionedFrontendModulePath');
        $manager = $this->app->make(ExtensionManager::class);

        $this->assertSame('other.js', $method->invoke($manager, 'other.js', $extension));
        $this->assertSame('../source-other/module.js', $method->invoke($manager, '../source-other/module.js', $extension));
        File::delete($this->root.'/source/module.js');
        $this->assertSame('module.js', $method->invoke($manager, 'module.js', $extension));
    }

    private function publish(string $source, string $target): void
    {
        $method = new \ReflectionMethod(ExtensionManager::class, 'publishAsset');
        $method->invoke($this->app->make(ExtensionManager::class), $this->root.'/source', $this->root.'/destination', $source, $target);
    }
}
