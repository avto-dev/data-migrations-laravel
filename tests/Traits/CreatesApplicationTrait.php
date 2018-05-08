<?php

namespace AvtoDev\DataMigrationsLaravel\Tests\Traits;

use Illuminate\Contracts\Console\Kernel;
use AvtoDev\DataMigrationsLaravel\DataMigrationsServiceProvider;

trait CreatesApplicationTrait
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        /** @var \Illuminate\Foundation\Application $app */
        $app = require __DIR__ . '/../../vendor/laravel/laravel/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $app->register(DataMigrationsServiceProvider::class);

        return $app;
    }
}
