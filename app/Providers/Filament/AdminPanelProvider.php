<?php

namespace App\Providers\Filament;

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\LanguageMiddleware;
use Filament\Http\Middleware\AuthenticateSession;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationGroup;
use Illuminate\Session\Middleware\StartSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('panel') // use 'admin' once task is completed
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
                VerifyCsrfToken::class,
                LanguageMiddleware::class,
            ])
            ->authMiddleware([
                AdminAuthenticate::class,
            ]);
    }
}
