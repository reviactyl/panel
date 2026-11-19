<?php

namespace Tests\Unit\Filament\Pages;

use App\Filament\Pages\Designify;
use App\Http\ViewComposers\DesignifyComposer;
use App\Services\Designify\SiteImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DesignifyTest extends TestCase
{
    public function test_background_scope_defaults_to_page_and_exports_all_surfaces(): void
    {
        foreach ([null => 'page', 'all' => 'all', 'invalid' => 'page'] as $configured => $expected) {
            config()->set('designify.backgroundScope', $configured ?: null);
            $view = Mockery::mock(View::class);
            $view->shouldReceive('with')->once()->withArgs(fn ($key, $settings) => $key === 'panelConfiguration'
                && $settings['backgroundScope'] === $expected);
            (new DesignifyComposer())->compose($view);
        }
    }

    public function test_background_overlay_defaults_and_saved_values_are_exported(): void
    {
        foreach ([[true, 85, '', true, 85, ''], [false, 30, '#123456', false, 30, '#123456'], [true, 150, 'invalid', true, 100, ''], [true, -10, '', true, 0, '']] as [$enabled, $opacity, $color, $expectedEnabled, $expectedOpacity, $expectedColor]) {
            config(['designify.backgroundOverlay' => $enabled, 'designify.backgroundOverlayOpacity' => $opacity, 'designify.backgroundOverlayColor' => $color]);
            $view = Mockery::mock(View::class);
            $view->shouldReceive('with')->once()->withArgs(fn ($key, $settings) => $key === 'panelConfiguration'
                && $settings['backgroundOverlay'] === $expectedEnabled
                && $settings['backgroundOverlayOpacity'] === $expectedOpacity
                && $settings['backgroundOverlayColor'] === $expectedColor);
            (new DesignifyComposer())->compose($view);
        }
    }

    public function test_card_icon_defaults_and_saved_options_are_exported(): void
    {
        $this->assertSame('classic', config('designify.cardIconStyle'));
        $this->assertSame('standard', config('designify.cardIconSize'));

        foreach ([[null, null, 'classic', 'standard'], ['background', 'enlarged', 'background', 'enlarged']] as [$style, $size, $expectedStyle, $expectedSize]) {
            config(['designify.cardIconStyle' => $style, 'designify.cardIconSize' => $size]);
            $view = Mockery::mock(View::class);
            $view->shouldReceive('with')->once()->withArgs(fn ($key, $settings) => $key === 'panelConfiguration'
                && $settings['cardIconStyle'] === $expectedStyle
                && $settings['cardIconSize'] === $expectedSize);
            (new DesignifyComposer())->compose($view);
        }
    }

    public function test_legacy_public_image_paths_are_normalized_without_changing_external_urls(): void
    {
        $images = app(SiteImageService::class);
        $this->assertSame('/reviactyl/logo.png', $images->normalizeUrl('/public/reviactyl/logo.png'));
        $this->assertSame('https://example.com/public/logo.png', $images->normalizeUrl('https://example.com/public/logo.png'));
        $this->assertSame('/storage/designify/logo.png', $images->normalizeUrl('/storage/designify/logo.png'));
    }

    public function test_uploads_are_stored_as_public_images_with_generated_names(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->createWithContent('logo.php.png', file_get_contents(public_path('reviactyl/logo.png')));
        $url = app(SiteImageService::class)->store($file, 'data.site_image_upload');
        $this->assertStringStartsWith('/storage/designify/', $url);
        $this->assertStringEndsWith('.png', $url);
        $this->assertStringNotContainsString('logo.php', $url);
        Storage::disk('public')->assertExists(substr($url, strlen('/storage/')));
    }

    public function test_images_up_to_twenty_megabytes_can_be_uploaded(): void
    {
        Storage::fake('public');
        $contents = file_get_contents(public_path('reviactyl/icon.png'));
        $contents .= str_repeat("\0", 20 * 1024 * 1024 - strlen($contents));
        $file = UploadedFile::fake()->createWithContent('image.png', $contents);
        $url = app(SiteImageService::class)->store($file);
        Storage::disk('public')->assertExists(substr($url, strlen('/storage/')));
    }

    public function test_non_images_and_oversized_images_are_rejected(): void
    {
        Storage::fake('public');
        $svg = tmpfile();
        fwrite($svg, '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>');
        foreach ([
            new UploadedFile(stream_get_meta_data($svg)['uri'], 'image.png', null, null, true),
            UploadedFile::fake()->create('image.png', 20481, 'image/png'),
        ] as $file) {
            try {
                app(SiteImageService::class)->store($file);
                $this->fail('The invalid upload was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image', $exception->errors());
            }
        }
        fclose($svg);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_composer_normalizes_legacy_site_image_paths(): void
    {
        config()->set('designify.site_image', '/public/reviactyl/logo.png');
        config()->set('designify.site_favicon', '/public/reviactyl/icon.png');
        $view = Mockery::mock(View::class);
        $view->shouldReceive('with')->once()->withArgs(fn ($key, $settings) => $key === 'panelConfiguration'
            && $settings['site_image'] === '/reviactyl/logo.png'
            && $settings['site_favicon'] === '/reviactyl/icon.png');
        (new DesignifyComposer())->compose($view);
    }

    public static function imageUrls(): array
    {
        return [
            'legacy public path' => ['/public/reviactyl/logo.png', '/reviactyl/logo.png'],
            'whitespace' => [' /public/reviactyl/logo.png ', '/reviactyl/logo.png'],
            'external URL' => ['https://example.com/public/image.png', 'https://example.com/public/image.png'],
            'protocol relative URL' => ['//example.com/public/image.png', '//example.com/public/image.png'],
            'uploaded image' => ['/storage/designify/image.png', '/storage/designify/image.png'],
            'root relative path' => ['/reviactyl/logo.png', '/reviactyl/logo.png'],
            'nested public segment' => ['/images/public/logo.png', '/images/public/logo.png'],
            'empty' => ['', ''],
            'none' => ['none', 'none'],
            'null' => [null, ''],
        ];
    }

    #[DataProvider('imageUrls')]
    public function test_preview_normalizes_background_and_error_image_urls(?string $input, string $expected): void
    {
        $page = app(Designify::class);
        $page->data = ['designify:background' => $input];
        foreach ([403, 404, 500] as $status) {
            $page->data['designify:errors:'.$status.':image'] = $input;
        }

        $preview = $page->getPreviewSettings();
        $this->assertSame($expected, $preview['background']);
        foreach ([403, 404, 500] as $status) {
            $this->assertSame($expected, $preview['errors'][$status]['image']);
        }
    }

    #[DataProvider('imageUrls')]
    public function test_composer_normalizes_background_and_error_image_urls(?string $input, string $expected): void
    {
        config(['designify.background' => $input]);
        foreach ([403, 404, 500] as $status) {
            config(['designify.errors.'.$status.'.image' => $input]);
        }

        $view = view('errors.404');
        (new DesignifyComposer())->compose($view);
        $settings = $view->getData()['panelConfiguration'];
        $this->assertSame($input === null ? 'none' : $expected, $settings['background']);
        foreach ([403, 404, 500] as $status) {
            $this->assertSame($expected, $settings['errors'][$status]['image']);
        }
    }

    #[DataProvider('imageUrls')]
    public function test_error_pages_render_normalized_image_urls(?string $input, string $expected): void
    {
        foreach ([403, 404, 500] as $status) {
            config(['designify.errors.'.$status.'.image' => $input]);
        }

        foreach ([403, 404, 500] as $status) {
            $html = view('errors.'.$status)->render();
            $this->assertSame(1, preg_match('/<img data-designify-error-image[^>]*>/', $html, $matches));
            $image = $matches[0];
            if ($expected === '') {
                $this->assertStringNotContainsString('src=', $image);
                $this->assertStringContainsString('object-contain mb-8 hidden', $image);
            } else {
                $this->assertStringContainsString('src="'.e($expected).'"', $image);
                $this->assertStringNotContainsString('object-contain mb-8 hidden', $image);
            }
        }
    }

    public function test_file_layout_defaults_to_list_and_exports_grid_configuration(): void
    {
        $this->assertSame('list', config('designify.fileLayout'));

        foreach ([null => 'list', 'list' => 'list', 'grid' => 'grid'] as $configured => $expected) {
            config()->set('designify.fileLayout', $configured ?: null);
            $view = Mockery::mock(View::class);
            $view->shouldReceive('with')->once()->withArgs(fn ($key, $settings) => $key === 'panelConfiguration' && $settings['fileLayout'] === $expected);
            (new DesignifyComposer())->compose($view);
        }
    }

    public function test_preview_settings_are_nested_without_unrelated_state(): void
    {
        $page = app(Designify::class);
        $page->data = [
            'designify:headerStyle' => 'modern',
            'designify:fileLayout' => 'grid',
            'designify:cardIconStyle' => 'background',
            'designify:cardIconSize' => 'enlarged',
            'designify:colorPrimary' => '#2563eb',
            'designify:color50' => '#09090b',
            'designify:color50L' => '#fafafa',
            'designify:errors:404:title' => 'Lost in space',
            'designify:sidebarButtons' => [['label' => 'Docs', 'url' => '/docs', 'newTab' => false]],
            'unrelated' => 'ignored',
        ];

        $this->assertSame([
            'headerStyle' => 'modern',
            'fileLayout' => 'grid',
            'cardIconStyle' => 'background',
            'cardIconSize' => 'enlarged',
            'colorPrimary' => '#2563eb',
            'color50' => '#09090b',
            'color50L' => '#fafafa',
            'errors' => [
                '404' => ['title' => 'Lost in space'],
            ],
            'sidebarButtons' => [['label' => 'Docs', 'url' => '/docs', 'newTab' => false]],
        ], $page->getPreviewSettings());
    }
}
