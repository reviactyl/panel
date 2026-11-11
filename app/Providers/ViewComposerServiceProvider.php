<?php

namespace App\Providers;

use App\Http\ViewComposers\AssetComposer;
use App\Http\ViewComposers\DesignifyComposer;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(DesignifyComposer::class);
    }

    public function boot(): void
    {
        $this->app->make('view')->composer('*', AssetComposer::class);
        $this->app->make('view')->composer('*', DesignifyComposer::class);
    }
}
