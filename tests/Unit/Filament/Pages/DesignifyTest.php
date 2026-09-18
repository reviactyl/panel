<?php

namespace Tests\Unit\Filament\Pages;

use App\Filament\Pages\Designify;
use Tests\TestCase;

class DesignifyTest extends TestCase
{
    public function test_preview_settings_are_nested_without_unrelated_state(): void
    {
        $page = app(Designify::class);
        $page->data = [
            'designify:colorPrimary' => '#2563eb',
            'designify:color50' => '#09090b',
            'designify:color50L' => '#fafafa',
            'designify:errors:404:title' => 'Lost in space',
            'designify:alerts' => [['type' => 'info', 'message' => 'Hello']],
            'unrelated' => 'ignored',
        ];

        $this->assertSame([
            'colorPrimary' => '#2563eb',
            'color50' => '#09090b',
            'color50L' => '#fafafa',
            'errors' => [
                '404' => ['title' => 'Lost in space'],
            ],
            'alerts' => [['type' => 'info', 'message' => 'Hello']],
        ], $page->getPreviewSettings());
    }
}
