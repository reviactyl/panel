<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActivityLogWidget;
use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\FeedbackWidget;
use App\Filament\Widgets\SponsorWidget;
use App\Filament\Widgets\UpdateWidget;
use App\Filament\Widgets\UserActivityWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\BackedEnum|null $activeNavigationIcon = 'heroicon-s-squares-2x2';

    public function getWidgets(): array
    {
        return [
            DashboardStatsOverview::class,
            UpdateWidget::class,
            FeedbackWidget::class,
            SponsorWidget::class,
            UserActivityWidget::class,
            ActivityLogWidget::class,
        ];
    }

    public function getHeading(): string
    {
        return trans('admin/index.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/navigation.administration.dashboard');
    }

    public function getTitle(): string
    {
        return trans('admin/index.title');
    }
}
