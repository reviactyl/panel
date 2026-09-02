<?php

namespace Tests\Unit\Http\ViewComposers;

use App\Http\ViewComposers\DesignifyComposer;
use Tests\TestCase;

class DesignifyComposerTest extends TestCase
{
    public function test_it_selects_accessible_muted_text_colors_for_each_palette(): void
    {
        $view = view('templates.wrapper');
        app(DesignifyComposer::class)->compose($view);

        $configuration = $view->getData()['panelConfiguration'];

        $this->assertSame('#9f9fa9', $configuration['colorMutedText']);
        $this->assertSame('#9aa5b1', $configuration['theme1']['colorMutedText']);
        $this->assertSame('#A2739B', $configuration['theme2']['colorMutedText']);
        $this->assertSame('#8F7A9E', $configuration['theme3']['colorMutedText']);
        $this->assertSame('#9E766F', $configuration['theme4']['colorMutedText']);
        $this->assertSame('#AD9693', $configuration['theme5']['colorMutedText']);
        $this->assertSame('#94a3b8', $configuration['theme6']['colorMutedText']);
        $this->assertSame('#a3a3a3', $configuration['theme7']['colorMutedText']);

        $palettes = [
            'default' => $configuration,
            'theme1' => $configuration['theme1'],
            'theme2' => $configuration['theme2'],
            'theme3' => $configuration['theme3'],
            'theme4' => $configuration['theme4'],
            'theme5' => $configuration['theme5'],
            'theme6' => $configuration['theme6'],
            'theme7' => $configuration['theme7'],
        ];

        foreach ($palettes as $name => $palette) {
            foreach (['color800', 'color900', 'color950'] as $background) {
                $this->assertGreaterThanOrEqual(
                    4.5,
                    $this->contrastRatio($palette['colorMutedText'], $palette[$background]),
                    "{$name} muted text does not meet WCAG AA against {$background}.",
                );
            }
        }
    }

    private function contrastRatio(string $foreground, string $background): float
    {
        $foregroundLuminance = $this->relativeLuminance($foreground);
        $backgroundLuminance = $this->relativeLuminance($background);

        return (max($foregroundLuminance, $backgroundLuminance) + 0.05)
            / (min($foregroundLuminance, $backgroundLuminance) + 0.05);
    }

    private function relativeLuminance(string $hex): float
    {
        $channels = str_split(ltrim($hex, '#'), 2);

        $channels = array_map(function (string $channel): float {
            $value = hexdec($channel) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $channels);

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
