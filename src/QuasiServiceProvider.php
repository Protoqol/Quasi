<?php

namespace Protoqol\Quasi;

use Illuminate\Support\ServiceProvider;
use Protoqol\Quasi\Console\CreateQuasiResourceCommand;
use Protoqol\Quasi\Console\QuasiResource;

class QuasiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                QuasiResource::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/quasi.php' => config_path('quasi.php'),
        ], 'quasi-config');
    }
}
