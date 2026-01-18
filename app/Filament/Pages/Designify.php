<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Designify extends Page
{

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-palette';
    protected static string|\BackedEnum|null $activeNavigationIcon = 'tabler-palette-filled';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return 'Designify';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/navigation.administration.title');
    }

    public function mount(): void
    {
        redirect()->route('admin.designify');
    }
}
