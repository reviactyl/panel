<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class PreviewWidget extends Widget
{
    protected int|string|array $columnSpan = 2;

    protected string $view = 'filament.widgets.preview-widget';
}
