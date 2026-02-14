<?php

namespace Protoqol\Quasi\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Protoqol\Quasi\QuasiServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            QuasiServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
    }
}
