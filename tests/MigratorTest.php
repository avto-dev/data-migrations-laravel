<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use Carbon\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use AvtoDev\DataMigrationsLaravel\Migrator;
use AvtoDev\DataMigrationsLaravel\Contracts\RepositoryContract;

class MigratorTest extends AbstractTestCase
{
    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrator = new Migrator(
            $this->app->make(RepositoryContract::class),
            $this->app->make('files'),
            __DIR__ . '/stubs/data_migrations'
        );
    }

    /**
     * Test filesystem getter.
     *
     * @return void
     */
    public function testGetFilesystem()
    {
        $this->assertInstanceOf(Filesystem::class, $this->migrator->getFilesystem());
    }

    /**
     * Test migrations repository getter.
     *
     * @return void
     */
    public function testGetRepository()
    {
        $this->assertInstanceOf(RepositoryContract::class, $this->migrator->getRepository());
    }


}
