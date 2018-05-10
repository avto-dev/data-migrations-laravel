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

    public function testNotMigrated()
    {
        $all       = array_flatten($this->migrator->source()->all());
        $exclude_1 = '2000_01_01_000001_simple_sql_data.sql';
        $exclude_2 = '2000_01_01_000010_simple_sql_data.sql';

        $filtered = array_filter($all, function ($migration_name) use (&$exclude_1, &$exclude_2) {
            return ! in_array($migration_name, [$exclude_1, $exclude_2], true);
        });

        foreach ($filtered as $migration_name) {
            $this->migrator->repository()->insert($migration_name);
        }

        $in_repository = $this->migrator->repository()->migrations();

        foreach ([$exclude_1, $exclude_2] as $exclide) {
            $this->assertNotContains($exclide, $in_repository);
        }

        $this->assertEquals([
            ''             => [$exclude_1],
            'connection_2' => [$exclude_2],
        ], $this->migrator->notMigrated());
    }
}
