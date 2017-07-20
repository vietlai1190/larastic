<?php

namespace Larastic;

use Larastic\Client\Factory;
use Larastic\Client\Manage;
use Illuminate\Support\ServiceProvider;

/**
 * Class LarasticServiceProvider
 * @package Larastic
 */
class LarasticServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $config_path = realpath(__DIR__ . '/../config/elastic.php');
        $this->publishes([
            $config_path => base_path('config/elastic.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('larastic', function($app) {
            $factory = new Factory();
            return new Manage($app, $factory);
        });
    }
}
