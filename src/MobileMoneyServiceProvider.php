<?php

namespace Jawiwy\MobileMoney;

use Illuminate\Support\ServiceProvider;
use Jawiwy\MobileMoney\src\Mpesa\Execute\RegisterUrlCommand;
class MobileMoneyServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'Jawiwy');
         $this->loadViewsFrom(__DIR__.'/Mpesa/resources/views', 'Jawiwy');
         $this->loadMigrationsFrom(__DIR__.'/Mpesa/database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {

            // Publishing the configuration file.
            $this->publishes([
                __DIR__.'/../config/mobilemoney.php' => config_path('mobilemoney.php'),
            ], 'mobilemoney.config');



            // Registering package commands.
            $this->commands([
                RegisterUrlCommand::class,
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mobilemoney.php', 'mobilemoney');

        // Register the service the package provides.
        $this->app->singleton('mobilemoney', function ($app) {
            return new MobileMoney;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['mobilemoney'];
    }
}