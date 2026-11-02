<?php

namespace Tests\Unit\Filament\Pages;

use App\Filament\Pages\Designify;
use App\Http\ViewComposers\DesignifyComposer;
use Illuminate\View\View;
use Mockery;
use Tests\TestCase;

class DesignifyTest extends TestCase
{
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
