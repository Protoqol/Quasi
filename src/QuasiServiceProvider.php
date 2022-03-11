<?php

namespace Protoqol\Quasi;

use Illuminate\Support\ServiceProvider;
use Protoqol\Quasi\Commands\CreateQuasiResourceCommand;

class QuasiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/quasi.php' => config_path('quasi.php'),
        ], 'config');


        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateQuasiResourceCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/quasi.php', 'quasi');

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateQuasiResourceCommand::class,
            ]);
        }
    }
}
