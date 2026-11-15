<?php

namespace App\Filament\Components;

use App\Services\Designify\SiteImageService;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class SiteImageInput extends TextInput
{
    protected string $view = 'filament.components.site-image-input';

    protected function setUp(): void
    {
        parent::setUp();

        $this->live(debounce: 300)
            ->placeholder(trans('admin/settings.designify.image-url-placeholder'))
            ->formatStateUsing(fn ($state) => SiteImageService::normalizeUrl($state))
            ->dehydrateStateUsing(fn ($state) => SiteImageService::normalizeUrl($state));
    }

    public function getUploadPath(): string
    {
        return 'siteImageFiles.'.Str::after($this->getName(), 'designify:');
    }

    public function isFavicon(): bool
    {
        return $this->getName() === 'designify:site_favicon';
    }

    public function isSquarePreview(): bool
    {
        return $this->isFavicon();
    }
}
