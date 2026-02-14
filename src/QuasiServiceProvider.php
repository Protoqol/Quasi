<?php

namespace Protoqol\Quasi;

use Illuminate\Support\ServiceProvider;
use Protoqol\Quasi\Console\CreateQuasiResourceCommand;

class QuasiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateQuasiResourceCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/quasi.php' => config_path('quasi.php'),
        ], 'quasi-config');

        $this->publishes([
            __DIR__ . '/Stubs/QResource.stub' => base_path('stubs/qresource.stub'),
        ], 'quasi-stubs');
    }
}
