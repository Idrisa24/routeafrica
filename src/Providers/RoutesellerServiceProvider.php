<?php

namespace Saidtech\Routereseller\Providers;

use Illuminate\Support\ServiceProvider;

class RoutesellerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/routereseller.php', 'routereseller');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php', 'routereseller');
        $this->publishesMigrations([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ]);
    }
}
