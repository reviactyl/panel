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
            'designify:theme1:name' => 'Ocean',
            'designify:theme1:colorPrimary' => '#0ea5e9',
            'designify:errors:404:title' => 'Lost in space',
            'designify:alerts' => [['type' => 'info', 'message' => 'Hello']],
            'unrelated' => 'ignored',
        ];

        $this->assertSame([
            'colorPrimary' => '#2563eb',
            'theme1' => [
                'name' => 'Ocean',
                'colorPrimary' => '#0ea5e9',
            ],
            'errors' => [
                '404' => ['title' => 'Lost in space'],
            ],
            'alerts' => [['type' => 'info', 'message' => 'Hello']],
        ], $page->getPreviewSettings());
    }
}
