<?php

use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\BladeServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\BackupsServiceProvider;
use App\Providers\HashidsServiceProvider;
use Prologue\Alerts\AlertsServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\ActivityLogServiceProvider;
use App\Providers\RouteConfigServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\ViewComposerServiceProvider;

return [
    ActivityLogServiceProvider::class,
    AppServiceProvider::class,
    AuthServiceProvider::class,
    BackupsServiceProvider::class,
    BladeServiceProvider::class,
    EventServiceProvider::class,
    HashidsServiceProvider::class,
    AdminPanelProvider::class,
    RouteConfigServiceProvider::class,
    RepositoryServiceProvider::class,
    ViewComposerServiceProvider::class,

    AlertsServiceProvider::class,
];
