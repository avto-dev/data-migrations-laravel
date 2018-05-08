<?php

namespace AvtoDev\DataMigrationsLaravel;

use Illuminate\Contracts\Foundation\Application;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class Repository implements RepositoryContract
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * Repository constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}
