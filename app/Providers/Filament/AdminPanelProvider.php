<?php

namespace App\Providers\Filament;

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Middleware\PreventRequestForgery;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->homeUrl('/')
            ->favicon(config('app.favicon', '/favicons/favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
            ])
            ->breadcrumbs(false)
            ->navigationGroups([
                NavigationGroup::make(fn () => trans('admin/navigation.administration.title'))
                    ->collapsible(false),
                NavigationGroup::make(fn () => trans('admin/navigation.management.title'))
                    ->collapsible(false),
                NavigationGroup::make(fn () => trans('admin/navigation.service.title')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                StartSession::class,
                AuthenticateSession::class,
                PreventRequestForgery::class,
                AddQueuedCookiesToResponse::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                LanguageMiddleware::class,
            ])
            ->authMiddleware([
                AdminAuthenticate::class,
            ]);
    }
}
