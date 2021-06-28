<?php

namespace Dnsoftware\Decta;

use Illuminate\Support\ServiceProvider;

class DectaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/../config/dectamerchant.php' => config_path('dectamerchant.php'),
        ]);

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/decta.php');
    }
}
