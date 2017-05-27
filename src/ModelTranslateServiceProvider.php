<?php

namespace Vortgo\Translate;

use Illuminate\Support\ServiceProvider;

class ModelTranslateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/translate.php' => config_path('translate.php'),
        ]);

        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('/migrations')
        ], 'migrations');

        $this->mergeConfigFrom(
            __DIR__ . '/config/translate.php', 'translate'
        );
    }

    public function register()
    {

    }
}
