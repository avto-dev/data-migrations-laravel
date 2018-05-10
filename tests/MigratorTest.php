<?php

namespace AvtoDev\DataMigrationsLaravel\Tests;

use AvtoDev\DataMigrationsLaravel\Migrator;
use AvtoDev\DataMigrationsLaravel\Sources\Files;
use AvtoDev\DataMigrationsLaravel\Contracts\SourceContract;
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
            new Files($this->app->make('files'), __DIR__ . '/stubs/data_migrations')
        );
    }

    /**
     * Test source getter.
     *
     * @return void
     */
    public function testGetSource()
    {
        $this->assertInstanceOf(SourceContract::class, $this->migrator->source());
    }

    /**
     * Test migrations repository getter.
     *
     * @return void
     */
    public function testGetRepository()
    {
        $this->assertInstanceOf(RepositoryContract::class, $this->migrator->repository());
    }

    public function testMigrate()
    {
        $this->migrator->repository()->insert('foo');
        $this->migrator->repository()->insert('bar');
        $this->assertNotFalse($this->migrator->migrate());
    }
}
