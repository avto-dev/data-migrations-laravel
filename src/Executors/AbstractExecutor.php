<?php

namespace AvtoDev\DataMigrationsLaravel\Executors;

use Illuminate\Contracts\Foundation\Application;
use AvtoDev\DataMigrationsLaravel\Contracts\ExecutorContract;

abstract class AbstractExecutor implements ExecutorContract
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * ExecutorContract constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}
